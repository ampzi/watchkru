export function LoadingSkeleton({ rows = 1, className = "" }) {
  return (
    <div className={`animate-pulse space-y-3 ${className}`}>
      {Array.from({ length: rows }).map((_, i) => (
        <div key={i} className="h-24 rounded-wk-md border border-border bg-surface-2/60" />
      ))}
    </div>
  );
}

export function PosterGridSkeleton({ count = 6 }) {
  return (
    <div className="flex gap-4 overflow-hidden">
      {Array.from({ length: count }).map((_, i) => (
        <div key={i} className="w-[168px] shrink-0 animate-pulse sm:w-[192px]">
          <div className="aspect-[2/3] rounded-wk-md border border-border bg-surface-2/60" />
          <div className="mt-2 h-3 w-3/4 rounded bg-surface-2/60" />
        </div>
      ))}
    </div>
  );
}

export function EmptyState({ title, hint, action }) {
  return (
    <div className="rounded-wk-md border border-dashed border-border px-6 py-12 text-center">
      <p className="text-ink">{title}</p>
      {hint && <p className="mt-1 text-sm text-ink-dim">{hint}</p>}
      {action && <div className="mt-4">{action}</div>}
    </div>
  );
}

export function ErrorState({ message = "مشکلی پیش آمد.", onRetry }) {
  return (
    <div className="rounded-wk-md border border-danger/40 bg-danger/5 px-6 py-8 text-center">
      <p className="text-ink">{message}</p>
      {onRetry && (
        <button
          onClick={onRetry}
          className="mt-3 rounded-wk-sm border border-border px-4 py-1.5 text-sm text-ink hover:border-gold/60"
        >
          تلاش دوباره
        </button>
      )}
    </div>
  );
}
