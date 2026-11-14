import { useState } from "react";
import { Link } from "react-router-dom";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { Bell } from "lucide-react";
import { notificationService } from "../../services/chatService";
import { useAuth } from "../../hooks/useAuth";

export default function NotificationBell() {
  const { user } = useAuth();
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);

  const { data } = useQuery({
    queryKey: ["notifications"],
    queryFn: notificationService.list,
    enabled: !!user,
    refetchInterval: 20000,
  });

  const markRead = useMutation({
    mutationFn: notificationService.markAllRead,
    onSuccess: () => qc.invalidateQueries({ queryKey: ["notifications"] }),
  });

  if (!user) return null;

  const items = data?.items || [];
  const unread = data?.unread_count || 0;

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => {
          setOpen((v) => !v);
          if (!open && unread > 0) markRead.mutate();
        }}
        aria-label={`اعلان‌ها${unread ? `، ${unread} خوانده‌نشده` : ""}`}
        className="relative inline-flex h-9 w-9 items-center justify-center rounded-wk-sm border border-border text-ink-dim transition-colors hover:border-gold/60 hover:text-ink"
      >
        <Bell className="h-4 w-4" strokeWidth={1.75} />
        {unread > 0 && (
          <span className="lat absolute -top-1.5 -end-1.5 min-w-4 rounded-full bg-gold px-1 text-[10px] font-bold leading-4 text-bg">
            {unread > 9 ? "9+" : unread}
          </span>
        )}
      </button>

      {open && (
        <>
          <button
            className="fixed inset-0 z-30 cursor-default"
            aria-label="بستن اعلان‌ها"
            onClick={() => setOpen(false)}
          />
          <div className="absolute end-0 z-40 mt-2 w-80 overflow-hidden rounded-wk-md border border-border bg-surface shadow-lg">
            <div className="border-b border-border px-3 py-2 text-xs text-ink-dim">اعلان‌ها</div>
            <div className="max-h-80 overflow-y-auto">
              {!items.length && <p className="px-3 py-6 text-center text-sm text-ink-dim">اعلانی ندارید.</p>}
              {items.map((n) => {
                const content = (
                  <div className={`border-b border-border px-3 py-2.5 last:border-0 hover:bg-surface-2 ${n.read ? "" : "bg-gold/5"}`}>
                    <p className="text-sm text-ink">{n.title}</p>
                    <p className="mt-0.5 line-clamp-2 text-xs leading-6 text-ink-dim">{n.body}</p>
                  </div>
                );

                return n.url ? (
                  <Link key={n.id} to={n.url} onClick={() => setOpen(false)} className="block">
                    {content}
                  </Link>
                ) : (
                  <div key={n.id}>{content}</div>
                );
              })}
            </div>
          </div>
        </>
      )}
    </div>
  );
}
