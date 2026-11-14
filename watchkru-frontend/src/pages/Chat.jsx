import { useEffect, useMemo, useRef, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { MessagesSquare, Plus, Send, Trash2, Users, ArrowRight } from "lucide-react";
import { chatService } from "../services/chatService";
import { useAuth } from "../hooks/useAuth";
import { EmptyState, ErrorState, LoadingSkeleton } from "../components/common/states";

const STATUS_LABEL = {
  pending: "در انتظار تأیید",
  approved: "تأییدشده",
  rejected: "ردشده",
  deletion_requested: "درخواست حذف",
};

function initials(name = "") {
  return name.trim().slice(0, 1) || "؟";
}

function timeOf(iso) {
  try {
    return new Date(iso).toLocaleTimeString("fa-IR", { hour: "2-digit", minute: "2-digit" });
  } catch {
    return "";
  }
}

function CreateGroupForm({ onClose }) {
  const qc = useQueryClient();
  const [name, setName] = useState("");
  const [description, setDescription] = useState("");
  const [error, setError] = useState("");

  const mutation = useMutation({
    mutationFn: () => chatService.createGroup({ name, description }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["chat", "groups"] });
      onClose();
    },
    onError: (err) => {
      setError(err.response?.data?.message || "ساخت گروه ناموفق بود.");
    },
  });

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        mutation.mutate();
      }}
      className="rounded-wk-md border border-border bg-surface p-4"
    >
      <p className="text-sm font-medium text-ink">گروه گفت‌وگوی تازه</p>
      <p className="mt-1 text-xs leading-6 text-ink-dim">
        گروه پس از تأیید مدیران در فهرست گفت‌وگوها نمایش داده می‌شود.
      </p>
      <input
        value={name}
        onChange={(e) => setName(e.target.value)}
        placeholder="نام گروه"
        className="mt-3 w-full rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm text-ink placeholder:text-ink-dim"
      />
      <textarea
        value={description}
        onChange={(e) => setDescription(e.target.value)}
        rows={3}
        placeholder="این گروه درباره چیست؟ (اختیاری)"
        className="mt-2 w-full rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm text-ink placeholder:text-ink-dim"
      />
      {error && <p className="mt-2 text-xs text-danger">{error}</p>}
      <div className="mt-3 flex gap-2">
        <button
          type="submit"
          disabled={mutation.isPending || name.trim().length < 3}
          className="rounded-wk-sm bg-gold px-4 py-2 text-sm font-medium text-bg disabled:opacity-40"
        >
          {mutation.isPending ? "در حال ارسال…" : "درخواست ساخت"}
        </button>
        <button type="button" onClick={onClose} className="rounded-wk-sm border border-border px-4 py-2 text-sm text-ink-dim">
          انصراف
        </button>
      </div>
    </form>
  );
}

export default function Chat() {
  const { user } = useAuth();
  const qc = useQueryClient();
  const [params, setParams] = useSearchParams();
  const [creating, setCreating] = useState(false);
  const [draft, setDraft] = useState("");
  const bottomRef = useRef(null);

  const activeId = Number(params.get("group") || 0);

  const groupsQuery = useQuery({
    queryKey: ["chat", "groups"],
    queryFn: chatService.groups,
    refetchInterval: 15000,
  });

  const groups = useMemo(() => groupsQuery.data || [], [groupsQuery.data]);
  const active = groups.find((g) => g.id === activeId) || groups[0] || null;

  const messagesQuery = useQuery({
    queryKey: ["chat", active?.id, "messages"],
    queryFn: () => chatService.messages(active.id),
    enabled: !!active && active.status === "approved",
    refetchInterval: 3000,
  });

  const messages = messagesQuery.data || [];

  useEffect(() => {
    if (active && active.status === "approved") {
      chatService.markRead(active.id).then(() => {
        qc.setQueryData(["chat", "groups"], (old) =>
          (old || []).map((g) => (g.id === active.id ? { ...g, unread_count: 0 } : g))
        );
      });
    }
  }, [active?.id, messages.length]); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: "smooth", block: "end" });
  }, [messages.length, active?.id]);

  const sendMutation = useMutation({
    mutationFn: (body) => chatService.send(active.id, body),
    onSuccess: () => {
      setDraft("");
      qc.invalidateQueries({ queryKey: ["chat", active.id, "messages"] });
      qc.invalidateQueries({ queryKey: ["chat", "groups"] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: () => chatService.requestDeletion(active.id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["chat", "groups"] }),
  });

  function select(group) {
    setParams({ group: String(group.id) });
  }

  return (
    <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="font-display text-2xl text-ink">گفت‌وگو</h1>
          <p className="mt-1 text-sm text-ink-dim">
            در گروه‌های تأییدشده با بقیه درباره فیلم و سریال‌ها حرف بزنید.
          </p>
        </div>
        <button
          onClick={() => setCreating((v) => !v)}
          className="inline-flex items-center gap-2 rounded-wk-sm bg-gold px-4 py-2 text-sm font-medium text-bg hover:bg-gold/90"
        >
          <Plus className="h-4 w-4" strokeWidth={2} />
          گروه جدید
        </button>
      </div>

      {creating && (
        <div className="mt-4">
          <CreateGroupForm onClose={() => setCreating(false)} />
        </div>
      )}

      {groupsQuery.isLoading && <LoadingSkeleton rows={3} className="mt-6" />}
      {groupsQuery.isError && (
        <ErrorState message="فهرست گروه‌ها بارگذاری نشد." onRetry={groupsQuery.refetch} />
      )}

      {!groupsQuery.isLoading && !groups.length && !creating && (
        <div className="mt-6">
          <EmptyState
            title="هنوز گروهی ندارید."
            hint="اولین گروه را بسازید؛ پس از تأیید مدیران، همه می‌توانند در آن گفت‌وگو کنند."
            action={
              <button onClick={() => setCreating(true)} className="rounded-wk-sm bg-gold px-4 py-2 text-sm text-bg">
                ساخت گروه
              </button>
            }
          />
        </div>
      )}

      {!!groups.length && (
        <div className="mt-6 grid gap-4 md:grid-cols-[290px_1fr]">
          <aside className={`space-y-2 ${active ? "hidden md:block" : ""}`}>
            {groups.map((g) => {
              const isActive = active?.id === g.id;
              return (
                <button
                  key={g.id}
                  onClick={() => select(g)}
                  className={`w-full rounded-wk-md border p-3 text-start transition-colors ${
                    isActive ? "border-gold/60 bg-gold/5" : "border-border bg-surface hover:border-gold/40"
                  }`}
                >
                  <div className="flex items-center justify-between gap-2">
                    <span className="truncate text-sm font-medium text-ink">{g.name}</span>
                    {g.unread_count > 0 && g.status === "approved" && (
                      <span className="lat shrink-0 rounded-full bg-gold px-1.5 py-0.5 text-[10px] font-bold text-bg">
                        {g.unread_count}
                      </span>
                    )}
                  </div>
                  {g.status !== "approved" && (
                    <p className="mt-1 text-[11px] text-gold">{STATUS_LABEL[g.status]}</p>
                  )}
                  <p className="mt-1 truncate text-xs text-ink-dim">
                    {g.last_message ? `${g.last_message.user}: ${g.last_message.body}` : "بدون پیام"}
                  </p>
                  <p className="mt-1 flex items-center gap-1 text-[11px] text-ink-dim">
                    <Users className="h-3 w-3" strokeWidth={1.75} />
                    {g.members_count} عضو
                  </p>
                </button>
              );
            })}
          </aside>

          {active && (
            <section className="flex min-h-[60vh] flex-col rounded-wk-md border border-border bg-surface">
              <header className="flex flex-wrap items-center gap-2 border-b border-border px-4 py-3">
                <button
                  onClick={() => setParams({})}
                  className="md:hidden inline-flex h-8 w-8 items-center justify-center rounded-wk-sm border border-border text-ink-dim"
                  aria-label="بازگشت به فهرست"
                >
                  <ArrowRight className="h-4 w-4" />
                </button>
                <div className="min-w-0 flex-1">
                  <p className="truncate text-sm font-semibold text-ink">{active.name}</p>
                  <p className="truncate text-xs text-ink-dim">
                    {active.members_count} عضو
                    {active.status !== "approved" && ` · ${STATUS_LABEL[active.status]}`}
                  </p>
                </div>
                {active.is_owner && active.status === "approved" && (
                  <button
                    onClick={() => {
                      if (window.confirm("درخواست حذف این گروه ثبت شود؟ حذف پس از تأیید مدیران انجام می‌شود.")) {
                        deleteMutation.mutate();
                      }
                    }}
                    disabled={deleteMutation.isPending}
                    className="inline-flex items-center gap-1.5 rounded-wk-sm border border-danger/40 px-3 py-1.5 text-xs text-danger hover:bg-danger/10 disabled:opacity-40"
                  >
                    <Trash2 className="h-3.5 w-3.5" strokeWidth={1.75} />
                    درخواست حذف
                  </button>
                )}
              </header>

              {active.status !== "approved" ? (
                <div className="flex flex-1 items-center justify-center p-8 text-center">
                  <div className="max-w-sm">
                    <MessagesSquare className="mx-auto h-8 w-8 text-gold" strokeWidth={1.5} />
                    <p className="mt-3 text-sm text-ink">
                      {active.status === "pending"
                        ? "این گروه در انتظار تأیید مدیران است."
                        : active.status === "deletion_requested"
                        ? "درخواست حذف این گروه ثبت شده و پس از تأیید مدیران اعمال می‌شود."
                        : "این گروه رد شده است."}
                    </p>
                    {active.admin_note && (
                      <p className="mt-2 text-xs text-ink-dim">یادداشت مدیر: {active.admin_note}</p>
                    )}
                  </div>
                </div>
              ) : (
                <>
                  <div className="flex-1 space-y-3 overflow-y-auto px-4 py-4" style={{ maxHeight: "52vh" }}>
                    {messagesQuery.isLoading && <LoadingSkeleton rows={2} />}
                    {!messagesQuery.isLoading && !messages.length && (
                      <p className="py-8 text-center text-sm text-ink-dim">اولین پیام را بنویسید.</p>
                    )}
                    {messages.map((m) => (
                      <div key={m.id} className={`flex gap-2 ${m.mine ? "flex-row-reverse" : ""}`}>
                        <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-border bg-surface-2 text-xs text-ink">
                          {initials(m.user?.name)}
                        </div>
                        <div className={`max-w-[78%] ${m.mine ? "text-start" : ""}`}>
                          <div className="flex items-center gap-2 text-[11px] text-ink-dim">
                            <span>{m.mine ? "شما" : m.user?.name}</span>
                            <span className="lat">{timeOf(m.created_at)}</span>
                          </div>
                          <p
                            className={`mt-1 whitespace-pre-wrap rounded-wk-md border px-3 py-2 text-sm leading-6 ${
                              m.mine
                                ? "border-gold/40 bg-gold/10 text-ink"
                                : "border-border bg-bg text-ink"
                            }`}
                          >
                            {m.body}
                          </p>
                        </div>
                      </div>
                    ))}
                    <div ref={bottomRef} />
                  </div>

                  <form
                    onSubmit={(e) => {
                      e.preventDefault();
                      if (draft.trim()) sendMutation.mutate(draft.trim());
                    }}
                    className="flex items-end gap-2 border-t border-border p-3"
                  >
                    <textarea
                      value={draft}
                      onChange={(e) => setDraft(e.target.value)}
                      onKeyDown={(e) => {
                        if (e.key === "Enter" && !e.shiftKey) {
                          e.preventDefault();
                          if (draft.trim()) sendMutation.mutate(draft.trim());
                        }
                      }}
                      rows={1}
                      placeholder={`پیام به ${active.name}…`}
                      className="max-h-32 min-h-[42px] flex-1 resize-y rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm text-ink placeholder:text-ink-dim"
                    />
                    <button
                      type="submit"
                      disabled={sendMutation.isPending || !draft.trim()}
                      className="inline-flex h-[42px] items-center gap-2 rounded-wk-sm bg-gold px-4 text-sm font-medium text-bg disabled:opacity-40"
                    >
                      <Send className="h-4 w-4" strokeWidth={1.75} />
                      ارسال
                    </button>
                  </form>
                  {sendMutation.isError && (
                    <p className="px-3 pb-2 text-xs text-danger">
                      {sendMutation.error?.response?.data?.message || "ارسال پیام ناموفق بود."}
                    </p>
                  )}
                </>
              )}
            </section>
          )}
        </div>
      )}
      {user === null && <p className="mt-4 text-sm text-ink-dim">برای گفت‌وگو وارد شوید.</p>}
    </div>
  );
}
