import { useEffect, useMemo, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { keepPreviousData, useQuery } from "@tanstack/react-query";
import { SlidersHorizontal } from "lucide-react";
import { mediaService } from "../services/authService";
import MediaCard from "../components/common/MediaCard";
import Pagination from "../components/common/Pagination";
import { PosterGridSkeleton, EmptyState, ErrorState } from "../components/common/states";

const SORTS = [
  { value: "popular", label: "محبوب‌ترین" },
  { value: "newest", label: "جدیدترین" },
  { value: "communities", label: "بیشترین انجمن" },
];

export default function Explore() {
  const [params, setParams] = useSearchParams();
  const page = Number(params.get("page") || 1);

  const filters = useMemo(
    () => ({
      q: params.get("q") || "",
      type: params.get("type") || "",
      genre: params.get("genre") || "",
      year: params.get("year") || "",
      sort: params.get("sort") || "popular",
      page,
      per_page: 18,
    }),
    [params, page]
  );

  const { data: genres } = useQuery({ queryKey: ["genres"], queryFn: mediaService.genres });

  const { data, isLoading, isFetching, isError, refetch } = useQuery({
    queryKey: ["media", "explore", filters],
    queryFn: () => mediaService.list(filters),
    // Keep the previous page on screen while the next one loads instead of
    // flashing the skeleton grid.
    placeholderData: keepPreviousData,
  });

  function update(patch) {
    const next = new URLSearchParams(params);
    Object.entries(patch).forEach(([k, v]) => {
      if (v) next.set(k, v);
      else next.delete(k);
    });
    if (!("page" in patch)) next.delete("page");
    setParams(next);
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <h1 className="font-display text-2xl text-ink">کاوش فیلم و سریال</h1>
      <p className="mt-1 text-sm text-ink-dim">فیلترها در آدرس صفحه ذخیره می‌شوند و قابل اشتراک‌گذاری هستند.</p>

      <div className="mt-6 flex flex-wrap items-center gap-2 rounded-wk-md border border-border bg-surface p-3">
        <SlidersHorizontal className="h-4 w-4 shrink-0 text-ink-dim" />

        <select
          value={filters.type}
          onChange={(e) => update({ type: e.target.value })}
          className="rounded-wk-sm border border-border bg-bg px-3 py-1.5 text-sm text-ink"
        >
          <option value="">فیلم و سریال</option>
          <option value="movie">فقط فیلم</option>
          <option value="series">فقط سریال</option>
        </select>

        <select
          value={filters.genre}
          onChange={(e) => update({ genre: e.target.value })}
          className="rounded-wk-sm border border-border bg-bg px-3 py-1.5 text-sm text-ink"
        >
          <option value="">همه ژانرها</option>
          {genres?.map((g) => (
            <option key={g.id} value={g.slug}>
              {g.name}
            </option>
          ))}
        </select>

        <input
          value={filters.year}
          onChange={(e) => update({ year: e.target.value })}
          placeholder="سال انتشار"
          inputMode="numeric"
          className="lat w-28 rounded-wk-sm border border-border bg-bg px-3 py-1.5 text-sm text-ink placeholder:text-ink-dim"
        />

        <select
          value={filters.sort}
          onChange={(e) => update({ sort: e.target.value })}
          className="ms-auto rounded-wk-sm border border-border bg-bg px-3 py-1.5 text-sm text-ink"
        >
          {SORTS.map((s) => (
            <option key={s.value} value={s.value}>
              {s.label}
            </option>
          ))}
        </select>
      </div>

      <div className={`mt-8 transition-opacity ${isFetching ? "opacity-60" : "opacity-100"}`}>
        {isLoading && (
          <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            {Array.from({ length: 12 }).map((_, i) => (
              <div key={i} className="animate-pulse">
                <div className="aspect-[2/3] rounded-wk-md border border-border bg-surface-2/60" />
              </div>
            ))}
          </div>
        )}

        {isError && <ErrorState onRetry={refetch} message="بارگذاری فهرست با خطا مواجه شد." />}

        {!isLoading && !isError && !data?.data?.length && (
          <EmptyState title="موردی با این فیلترها پیدا نشد." hint="فیلترها را تغییر دهید یا جست‌وجوی دیگری امتحان کنید." />
        )}

        {!isLoading && !isError && !!data?.data?.length && (
          <div className="grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            {data.data.map((m) => (
              <MediaCard key={m.id} media={m} />
            ))}
          </div>
        )}

        <Pagination meta={data?.meta} onPageChange={(p) => update({ page: String(p) })} />
      </div>
    </div>
  );
}
