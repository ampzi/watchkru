import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import api from "../../services/api";
import { EmptyState } from "../../components/common/states";

export default function AdminReports() {
  const qc = useQueryClient();
  const { data } = useQuery({
    queryKey: ["admin", "reports"],
    queryFn: () => api.get("/admin/reports", { params: { status: "open" } }).then((r) => r.data.data),
  });

  const resolve = useMutation({
    mutationFn: (id) => api.put(`/admin/reports/${id}`, { status: "resolved" }),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["admin", "reports"] }),
  });

  if (!data?.length) return <EmptyState title="گزارش بازی وجود ندارد." />;

  return (
    <div className="space-y-3">
      {data.map((r) => (
        <div key={r.id} className="flex flex-wrap items-center justify-between gap-3 rounded-wk-md border border-border bg-surface p-4">
          <div>
            <p className="text-sm text-ink">{r.community?.name}</p>
            <p className="mt-0.5 text-xs text-ink-dim">{r.report_type} — {r.description || "بدون توضیح"}</p>
          </div>
          <button
            onClick={() => resolve.mutate(r.id)}
            className="rounded-wk-sm border border-teal/50 px-3 py-1.5 text-xs text-teal hover:bg-teal/10"
          >
            حل‌شده علامت بزن
          </button>
        </div>
      ))}
    </div>
  );
}
