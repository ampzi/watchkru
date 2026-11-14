import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { Link } from "react-router-dom";
import api from "../services/api";
import { useAuth } from "../hooks/useAuth";
import { VerificationBadge } from "../components/common/badges";
import { EmptyState } from "../components/common/states";

const TABS = [
  { key: "submissions", label: "انجمن‌های ارسالی من" },
  { key: "reports", label: "گزارش‌های من" },
  { key: "favorites", label: "علاقه‌مندی‌ها" },
];

export default function Dashboard() {
  const { user } = useAuth();
  const [tab, setTab] = useState("submissions");

  const { data } = useQuery({
    queryKey: ["user", tab],
    queryFn: () => api.get(`/user/${tab}`).then((r) => r.data.data),
  });

  return (
    <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6">
      <h1 className="font-display text-2xl text-ink">
        سلام {user?.display_name || user?.username}
      </h1>
      <p className="mt-1 text-sm text-ink-dim">فعالیت‌های شما در واچ‌کرو</p>

      <div className="mt-6 flex gap-1 border-b border-border">
        {TABS.map((t) => (
          <button
            key={t.key}
            onClick={() => setTab(t.key)}
            className={`-mb-px border-b-2 px-4 py-2.5 text-sm ${
              tab === t.key ? "border-gold text-ink" : "border-transparent text-ink-dim hover:text-ink"
            }`}
          >
            {t.label}
          </button>
        ))}
        <Link to="/submit-community" className="ms-auto self-center text-sm text-gold hover:text-gold/80">
          + معرفی انجمن جدید
        </Link>
      </div>

      <div className="mt-6 space-y-3">
        {!data?.length && <EmptyState title="هنوز موردی ثبت نشده است." />}
        {data?.map((item) => (
          <div key={item.id} className="flex items-center justify-between rounded-wk-md border border-border bg-surface p-4">
            <div>
              <p className="text-sm text-ink">{item.name || item.title || item.description}</p>
              {item.created_at && <p className="mt-0.5 text-xs text-ink-dim">{item.created_at}</p>}
            </div>
            {item.status && <VerificationBadge status={item.status} />}
          </div>
        ))}
      </div>
    </div>
  );
}
