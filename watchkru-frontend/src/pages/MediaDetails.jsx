import { useState } from "react";
import { useParams } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import { mediaService, communityService } from "../services/authService";
import Breadcrumbs from "../components/common/Breadcrumbs";
import CommunityCard from "../components/common/CommunityCard";
import { GenreBadge } from "../components/common/badges";
import { LoadingSkeleton, EmptyState, ErrorState } from "../components/common/states";

const TYPE_LABEL = { movie: "فیلم", series: "سریال" };
const STATUS_LABEL = { ongoing: "در حال پخش", ended: "پایان‌یافته", upcoming: "به‌زودی" };

export default function MediaDetails() {
  const { slug } = useParams();
  const [platformFilter, setPlatformFilter] = useState("");

  const { data: media, isLoading, isError } = useQuery({
    queryKey: ["media", slug],
    queryFn: () => mediaService.get(slug),
  });

  const { data: communities } = useQuery({
    queryKey: ["communities", "for-media", slug, platformFilter],
    queryFn: () => communityService.list({ media: slug, platform: platformFilter || undefined, per_page: 30 }),
    enabled: !!media,
  });

  if (isLoading) {
    return (
      <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6">
        <LoadingSkeleton rows={1} className="h-80" />
      </div>
    );
  }

  if (isError || !media) {
    return (
      <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6">
        <ErrorState message="این عنوان پیدا نشد." />
      </div>
    );
  }

  return (
    <div>
      <div className="relative h-56 overflow-hidden border-b border-border sm:h-72">
        {media.backdrop_path ? (
          <img
            src={media.backdrop_path}
            alt=""
            loading="lazy"
            decoding="async"
            className="h-full w-full object-cover opacity-40"
          />
        ) : media.poster_path ? (
          // No wide backdrop: reuse the poster scaled up and blurred so the
          // hero still shows the title's own art instead of a flat block.
          <img
            src={media.poster_path}
            alt=""
            aria-hidden="true"
            loading="lazy"
            decoding="async"
            className="h-full w-full scale-110 object-cover opacity-35 blur-sm"
          />
        ) : (
          <div className="h-full w-full bg-surface-2" />
        )}
        <div className="absolute inset-0 bg-gradient-to-t from-bg via-bg/40 to-transparent" />
      </div>

      <div className="mx-auto -mt-20 max-w-5xl px-4 sm:px-6">
        <Breadcrumbs
          items={[
            { label: "کاوش", to: "/explore" },
            { label: TYPE_LABEL[media.media_type], to: `/explore?type=${media.media_type}` },
            { label: media.title },
          ]}
        />

        <div className="flex flex-col gap-6 sm:flex-row">
          <div className="w-36 shrink-0 overflow-hidden rounded-wk-md border border-border bg-surface-2 shadow-[0_8px_24px_rgba(0,0,0,0.4)] sm:w-48">
            {media.poster_path ? (
              <img
                src={media.poster_path}
                alt={media.title}
                loading="lazy"
                decoding="async"
                width={192}
                height={288}
                className="w-full"
              />
            ) : (
              <div className="flex aspect-[2/3] items-center justify-center text-ink-dim">بدون پوستر</div>
            )}
          </div>

          <div className="flex-1 pb-6 pt-2">
            <h1 className="font-display text-3xl text-ink">{media.title}</h1>
            {media.original_title && media.original_title !== media.title && (
              <p className="lat mt-1 text-ink-dim">{media.original_title}</p>
            )}

            <div className="mt-3 flex flex-wrap items-center gap-2 text-sm text-ink-dim">
              <span className="lat font-display text-base text-ink">{media.release_year}</span>
              {media.runtime && <span>· {media.runtime} دقیقه</span>}
              {media.number_of_seasons && <span>· {media.number_of_seasons} فصل</span>}
              {media.status && <span>· {STATUS_LABEL[media.status] || media.status}</span>}
              {typeof media.communities_count === "number" && (
                <span>· {media.communities_count} انجمن</span>
              )}
            </div>

            {!!media.genres?.length && (
              <div className="mt-3 flex flex-wrap gap-1.5">
                {media.genres.map((g) => (
                  <GenreBadge key={g.id}>{g.name}</GenreBadge>
                ))}
              </div>
            )}

            {media.overview && <p className="mt-4 max-w-2xl leading-7 text-ink-dim">{media.overview}</p>}

            {!!media.cast?.length && (
              <p className="mt-4 text-sm text-ink-dim">
                <span className="text-ink">بازیگران: </span>
                {media.cast.join("، ")}
              </p>
            )}
          </div>
        </div>

        <div className="sprocket-rule my-8" />

        <section className="pb-16">
          <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 className="font-display text-xl text-ink">
              انجمن‌های مرتبط با «{media.title}»
            </h2>
            <select
              value={platformFilter}
              onChange={(e) => setPlatformFilter(e.target.value)}
              className="rounded-wk-sm border border-border bg-surface px-3 py-1.5 text-sm text-ink"
            >
              <option value="">همه پلتفرم‌ها</option>
              <option value="telegram_group">گروه تلگرام</option>
              <option value="telegram_channel">کانال تلگرام</option>
              <option value="discord_server">سرور دیسکورد</option>
              <option value="instagram_page">پیج اینستاگرام</option>
              <option value="reddit_community">انجمن ردیت</option>
              <option value="whatsapp_group">گروه واتس‌اپ</option>
              <option value="website_forum">وب‌سایت / فروم</option>
            </select>
          </div>

          {!communities?.data?.length ? (
            <EmptyState
              title="هنوز انجمنی برای این عنوان ثبت نشده."
              hint="اگر انجمنی می‌شناسید، اولین نفری باشید که آن را معرفی می‌کند."
            />
          ) : (
            <div className="grid gap-4 sm:grid-cols-2">
              {communities.data.map((c) => (
                <CommunityCard key={c.id} community={c} />
              ))}
            </div>
          )}
        </section>
      </div>
    </div>
  );
}
