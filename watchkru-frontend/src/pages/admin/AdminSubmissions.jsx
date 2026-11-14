import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import api from "../../services/api";
import { EmptyState } from "../../components/common/states";
import { PlatformBadge } from "../../components/common/badges";

export default function AdminSubmissions() {
  const qc = useQueryClient();
  const { data } = useQuery({
    queryKey: ["admin", "submissions"],
    queryFn: () => api.get("/admin/communities", { params: { status: "pending" } }).then((r) => r.data.data),
  });

  const decide = useMutation({
    mutationFn: ({ id, action }) => api.post(`/admin/communities/${id}/${action}`),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["admin", "submissions"] }),
  });

  if (!data?.length) return <EmptyState title="موردی در انتظار بررسی نیست." />;

  return (
    <div className="space-y-3">
      {data.map((c) => (
        <div key={c.id} className="flex flex-wrap items-center justify-between gap-3 rounded-wk-md border border-border bg-surface p-4">
          <div>
            <p className="text-sm text-ink">{c.name}</p>
            <p className="mt-0.5 text-xs text-ink-dim">
              {c.media?.title} · <span className="lat">{c.external_url}</span>
            </p>
          </div>
          <div className="flex items-center gap-2">
            <PlatformBadge platform={c.platform} />
            <button
              onClick={() => decide.mutate({ id: c.id, action: "approve" })}
              className="rounded-wk-sm border border-teal/50 px-3 py-1.5 text-xs text-teal hover:bg-teal/10"
            >
              تأیید
            </button>
            <button
              onClick={() => decide.mutate({ id: c.id, action: "reject" })}
              className="rounded-wk-sm border border-danger/50 px-3 py-1.5 text-xs text-danger hover:bg-danger/10"
            >
              رد
            </button>
          </div>
        </div>
      ))}
    </div>
  );
}
