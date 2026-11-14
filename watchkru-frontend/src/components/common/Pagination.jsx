export default function Pagination({ meta, onPageChange }) {
  if (!meta || meta.last_page <= 1) return null;
  const { current_page, last_page } = meta;

  const pages = [];
  const start = Math.max(1, current_page - 2);
  const end = Math.min(last_page, current_page + 2);
  for (let p = start; p <= end; p++) pages.push(p);

  return (
    <nav className="mt-8 flex items-center justify-center gap-1.5" aria-label="صفحه‌بندی">
      <button
        disabled={current_page <= 1}
        onClick={() => onPageChange(current_page - 1)}
        className="rounded-wk-sm border border-border px-3 py-1.5 text-sm text-ink-dim disabled:opacity-30 hover:not-disabled:border-gold/50"
      >
        قبلی
      </button>
      {start > 1 && <span className="px-1 text-ink-dim">…</span>}
      {pages.map((p) => (
        <button
          key={p}
          onClick={() => onPageChange(p)}
          aria-current={p === current_page ? "page" : undefined}
          className={`lat min-w-9 rounded-wk-sm border px-3 py-1.5 text-sm ${
            p === current_page
              ? "border-gold bg-gold/10 text-gold"
              : "border-border text-ink-dim hover:border-gold/50"
          }`}
        >
          {p}
        </button>
      ))}
      {end < last_page && <span className="px-1 text-ink-dim">…</span>}
      <button
        disabled={current_page >= last_page}
        onClick={() => onPageChange(current_page + 1)}
        className="rounded-wk-sm border border-border px-3 py-1.5 text-sm text-ink-dim disabled:opacity-30 hover:not-disabled:border-gold/50"
      >
        بعدی
      </button>
    </nav>
  );
}
