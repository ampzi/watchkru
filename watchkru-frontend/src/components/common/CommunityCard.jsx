import { Link } from "react-router-dom";
import { useQueryClient } from "@tanstack/react-query";
import { PlatformBadge, VerificationBadge, ActivityBadge } from "./badges";
import { communityService } from "../../services/authService";

export default function CommunityCard({ community }) {
  const { slug, name, description, platform, verification_status, activity_status, media } = community;
  const qc = useQueryClient();

  function prefetch() {
    qc.prefetchQuery({
      queryKey: ["community", slug],
      queryFn: () => communityService.get(slug),
      staleTime: 5 * 60 * 1000,
    });
  }

  return (
    <Link
      to={`/communities/${slug}`}
      onMouseEnter={prefetch}
      onFocus={prefetch}
      className="block rounded-wk-md border border-border bg-surface p-4 transition-colors hover:border-gold/50"
    >
      <div className="flex items-start justify-between gap-3">
        <h3 className="text-sm font-semibold text-ink">{name}</h3>
        <PlatformBadge platform={platform} />
      </div>
      {media?.title && (
        <p className="mt-1 text-xs text-ink-dim">مرتبط با «{media.title}»</p>
      )}
      {description && (
        <p className="mt-2 line-clamp-2 text-sm text-ink-dim">{description}</p>
      )}
      <div className="mt-3 flex flex-wrap items-center gap-2">
        <VerificationBadge status={verification_status} />
        <ActivityBadge status={activity_status} />
      </div>
    </Link>
  );
}
