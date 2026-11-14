import { useState } from "react";
import { Link, useParams } from "react-router-dom";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { ExternalLink, Flag, Star } from "lucide-react";
import { communityService } from "../services/authService";
import { useAuth } from "../hooks/useAuth";
import Breadcrumbs from "../components/common/Breadcrumbs";
import { PlatformBadge, VerificationBadge, ActivityBadge, GenreBadge } from "../components/common/badges";
import { LoadingSkeleton, ErrorState } from "../components/common/states";

const REPORT_TYPES = [
  { value: "broken_link", label: "لینک خراب است" },
  { value: "inactive", label: "انجمن غیرفعال است" },
  { value: "wrong_description", label: "توضیحات نادرست است" },
  { value: "wrong_media", label: "فیلم/سریال اشتباه است" },
  { value: "wrong_platform", label: "پلتفرم اشتباه است" },
  { value: "spam", label: "هرزنامه" },
  { value: "inappropriate", label: "محتوای نامناسب" },
  { value: "duplicate", label: "ثبت تکراری" },
  { value: "other", label: "سایر" },
];

export default function CommunityDetails() {
  const { slug } = useParams();
  const { user } = useAuth();
  const qc = useQueryClient();
  const [reportOpen, setReportOpen] = useState(false);
  const [reportType, setReportType] = useState(REPORT_TYPES[0].value);
  const [reportNote, setReportNote] = useState("");
  const [rating, setRating] = useState(5);
  const [reviewText, setReviewText] = useState("");

  const { data: community, isLoading, isError } = useQuery({
    queryKey: ["community", slug],
    queryFn: () => communityService.get(slug),
  });

  const reportMutation = useMutation({
    mutationFn: (payload) => communityService.report(community.id, payload),
    onSuccess: () => {
      setReportOpen(false);
      setReportNote("");
    },
  });

  const reviewMutation = useMutation({
    mutationFn: (payload) => communityService.review(community.id, payload),
    onSuccess: () => {
      setReviewText("");
      qc.invalidateQueries({ queryKey: ["community", slug] });
    },
  });

  if (isLoading) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <LoadingSkeleton rows={2} />
      </div>
    );
  }

  if (isError || !community) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <ErrorState message="این انجمن پیدا نشد." />
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6">
      <Breadcrumbs
        items={[
          { label: "کاوش", to: "/explore" },
          ...(community.media ? [{ label: community.media.title, to: `/media/${community.media.slug}` }] : []),
          { label: community.name },
        ]}
      />

      <div className="rounded-wk-lg border border-border bg-surface p-6">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h1 className="font-display text-2xl text-ink">{community.name}</h1>
            {community.media && (
              <p className="mt-1 text-sm text-ink-dim">
                مرتبط با{" "}
                <Link to={`/media/${community.media.slug}`} className="text-teal hover:underline">
                  «{community.media.title}»
                </Link>
              </p>
            )}
          </div>
          <PlatformBadge platform={community.platform} />
        </div>

        <div className="mt-4 flex flex-wrap gap-2">
          <VerificationBadge status={community.verification_status} />
          <ActivityBadge status={community.activity_status} />
          {community.community_category && <GenreBadge>{community.community_category.name}</GenreBadge>}
          <GenreBadge>{community.language === "fa" ? "فارسی" : community.language}</GenreBadge>
        </div>

        {community.description && <p className="mt-5 leading-7 text-ink-dim">{community.description}</p>}

        <dl className="mt-6 grid grid-cols-2 gap-4 border-t border-border pt-5 text-sm sm:grid-cols-3">
          <div>
            <dt className="text-ink-dim">اعضا</dt>
            <dd className="lat mt-0.5 text-ink">
              {community.verification_status === "verified" && community.member_count
                ? community.member_count.toLocaleString("fa-IR")
                : "نامشخص"}
            </dd>
          </div>
          <div>
            <dt className="text-ink-dim">آخرین بررسی</dt>
            <dd className="lat mt-0.5 text-ink">{community.last_checked_at || "—"}</dd>
          </div>
          <div>
            <dt className="text-ink-dim">امتیاز</dt>
            <dd className="mt-0.5 text-ink">
              {community.reviews_avg_rating ? (
                <span className="lat">{Number(community.reviews_avg_rating).toFixed(1)} / 5</span>
              ) : (
                "بدون امتیاز"
              )}{" "}
              <span className="text-ink-dim">({community.reviews_count || 0} نظر)</span>
            </dd>
          </div>
        </dl>

        <div className="mt-6 flex flex-wrap items-center gap-3">
          <a
            href={community.external_url}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 rounded-wk-sm bg-gold px-5 py-2.5 text-sm font-medium text-bg hover:bg-gold/90"
          >
            ورود به انجمن
            <ExternalLink className="h-4 w-4" />
          </a>
          <button
            onClick={() => setReportOpen((v) => !v)}
            className="inline-flex items-center gap-2 rounded-wk-sm border border-border px-4 py-2.5 text-sm text-ink-dim hover:border-danger/50 hover:text-danger"
          >
            <Flag className="h-4 w-4" />
            گزارش مشکل
          </button>
        </div>

        {reportOpen && (
          <div className="mt-4 rounded-wk-md border border-border bg-surface-2 p-4">
            {!user ? (
              <p className="text-sm text-ink-dim">
                برای ثبت گزارش ابتدا{" "}
                <Link to="/login" className="text-teal hover:underline">وارد شوید</Link>.
              </p>
            ) : (
              <div className="space-y-3">
                <select
                  value={reportType}
                  onChange={(e) => setReportType(e.target.value)}
                  className="w-full rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm text-ink"
                >
                  {REPORT_TYPES.map((t) => (
                    <option key={t.value} value={t.value}>{t.label}</option>
                  ))}
                </select>
                <textarea
                  value={reportNote}
                  onChange={(e) => setReportNote(e.target.value)}
                  placeholder="توضیح کوتاه (اختیاری)"
                  rows={3}
                  className="w-full rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm text-ink placeholder:text-ink-dim"
                />
                <button
                  onClick={() => reportMutation.mutate({ report_type: reportType, description: reportNote })}
                  disabled={reportMutation.isPending}
                  className="rounded-wk-sm border border-danger/50 px-4 py-2 text-sm text-danger hover:bg-danger/10"
                >
                  {reportMutation.isPending ? "در حال ارسال…" : "ثبت گزارش"}
                </button>
                {reportMutation.isSuccess && <p className="text-sm text-teal">گزارش شما ثبت شد. سپاسگزاریم.</p>}
              </div>
            )}
          </div>
        )}
      </div>

      <section className="mt-8">
        <h2 className="font-display text-lg text-ink">نظرها</h2>

        {user ? (
          <div className="mt-3 rounded-wk-md border border-border bg-surface p-4">
            <div className="flex items-center gap-1">
              {[1, 2, 3, 4, 5].map((n) => (
                <button key={n} onClick={() => setRating(n)} aria-label={`${n} ستاره`}>
                  <Star
                    className={`h-5 w-5 ${n <= rating ? "fill-gold text-gold" : "text-border"}`}
                  />
                </button>
              ))}
            </div>
            <textarea
              value={reviewText}
              onChange={(e) => setReviewText(e.target.value)}
              placeholder="تجربه‌تان از این انجمن را بنویسید…"
              rows={3}
              className="mt-3 w-full rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm text-ink placeholder:text-ink-dim"
            />
            <button
              onClick={() => reviewMutation.mutate({ rating, content: reviewText })}
              disabled={reviewMutation.isPending || !reviewText.trim()}
              className="mt-2 rounded-wk-sm bg-gold px-4 py-2 text-sm font-medium text-bg disabled:opacity-40"
            >
              {reviewMutation.isPending ? "در حال ثبت…" : "ثبت نظر"}
            </button>
          </div>
        ) : (
          <p className="mt-2 text-sm text-ink-dim">
            برای ثبت نظر <Link to="/login" className="text-teal hover:underline">وارد شوید</Link>.
          </p>
        )}

        <div className="mt-6 space-y-4">
          {community.reviews?.length ? (
            community.reviews.map((r) => (
              <div key={r.id} className="border-b border-border pb-4">
                <div className="flex items-center justify-between">
                  <span className="text-sm text-ink">{r.user?.display_name || r.user?.username}</span>
                  <span className="lat text-sm text-gold">{r.rating}/5</span>
                </div>
                <p className="mt-1 text-sm text-ink-dim">{r.content}</p>
              </div>
            ))
          ) : (
            <p className="text-sm text-ink-dim">هنوز نظری ثبت نشده است.</p>
          )}
        </div>
      </section>
    </div>
  );
}
