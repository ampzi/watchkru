import { Link } from "react-router-dom";
import { useQueryClient } from "@tanstack/react-query";
import { mediaService } from "../../services/authService";

const TYPE_LABEL = { movie: "فیلم", series: "سریال" };

export default function MediaCard({ media }) {
  const {
    slug,
    title,
    original_title,
    poster_path,
    release_year,
    media_type,
    communities_count,
  } = media;
  const qc = useQueryClient();

  // Warm the detail query while the pointer is still travelling to the card,
  // so the destination page renders from cache.
  function prefetch() {
    qc.prefetchQuery({
      queryKey: ["media", slug],
      queryFn: () => mediaService.get(slug),
      staleTime: 5 * 60 * 1000,
    });
  }

  return (
    <Link
      to={`/media/${slug}`}
      onMouseEnter={prefetch}
      onFocus={prefetch}
      className="group block"
    >

      <div className="relative aspect-[2/3] overflow-hidden rounded-wk-md border border-border bg-surface-2">
        {poster_path ? (
          <img
            src={poster_path}
            alt={title}
            loading="lazy"
            decoding="async"
            className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.04]"
          />
        ) : (
          <div className="flex h-full w-full items-center justify-center text-ink-dim">
            بدون پوستر
          </div>
        )}
        <span className="absolute top-2 inset-inline-start-2 rounded-wk-sm bg-bg/80 px-2 py-0.5 text-[11px] text-gold backdrop-blur-sm">
          {TYPE_LABEL[media_type] || media_type}
        </span>
      </div>
      <div className="mt-2 space-y-0.5">
        <h3 className="truncate text-sm font-medium text-ink">{title}</h3>
        {original_title && original_title !== title && (
          <p className="lat truncate text-xs text-ink-dim">{original_title}</p>
        )}
        <div className="flex items-center justify-between text-xs text-ink-dim">
          <span className="lat font-display">{release_year}</span>
          {typeof communities_count === "number" && (
            <span>{communities_count} انجمن</span>
          )}
        </div>
      </div>
    </Link>
  );
}
