import { Routes, Route, NavLink } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import api from "../../services/api";
import AdminSubmissions from "./AdminSubmissions";
import AdminReports from "./AdminReports";

const tabs = [
  { to: "/admin", label: "نمای کلی", end: true },
  { to: "/admin/submissions", label: "انجمن‌های ارسالی" },
  { to: "/admin/reports", label: "گزارش‌ها" },
];

function Overview() {
  const { data } = useQuery({
    queryKey: ["admin", "dashboard"],
    queryFn: () => api.get("/admin/dashboard").then((r) => r.data.data),
  });

  const stats = [
    { label: "کل فیلم‌ها", value: data?.total_movies },
    { label: "کل سریال‌ها", value: data?.total_series },
    { label: "کل انجمن‌ها", value: data?.total_communities },
    { label: "در انتظار بررسی", value: data?.pending_submissions },
    { label: "نیاز به بازبینی", value: data?.needs_review },
    { label: "کل کاربران", value: data?.total_users },
    { label: "گزارش‌های باز", value: data?.pending_reports },
  ];

  return (
    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
      {stats.map((s) => (
        <div key={s.label} className="rounded-wk-md border border-border bg-surface p-4">
          <p className="lat font-display text-2xl text-ink">{s.value ?? "—"}</p>
          <p className="mt-1 text-xs text-ink-dim">{s.label}</p>
        </div>
      ))}
    </div>
  );
}

export default function AdminDashboard() {
  return (
    <div className="mx-auto max-w-6xl px-4 py-10 sm:px-6">
      <h1 className="font-display text-2xl text-ink">پنل مدیریت</h1>

      <div className="mt-5 flex gap-1 border-b border-border">
        {tabs.map((t) => (
          <NavLink
            key={t.to}
            to={t.to}
            end={t.end}
            className={({ isActive }) =>
              `-mb-px border-b-2 px-4 py-2.5 text-sm ${
                isActive ? "border-gold text-ink" : "border-transparent text-ink-dim hover:text-ink"
              }`
            }
          >
            {t.label}
          </NavLink>
        ))}
      </div>

      <div className="mt-6">
        <Routes>
          <Route index element={<Overview />} />
          <Route path="submissions" element={<AdminSubmissions />} />
          <Route path="reports" element={<AdminReports />} />
        </Routes>
      </div>
    </div>
  );
}
