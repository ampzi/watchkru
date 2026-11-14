import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { keepPreviousData, useQuery } from "@tanstack/react-query";
import { searchService } from "../services/authService";
import MediaCard from "../components/common/MediaCard";
import CommunityCard from "../components/common/CommunityCard";
import { EmptyState, ErrorState, LoadingSkeleton } from "../components/common/states";

export default function SearchPage() {
  const [params, setParams] = useSearchParams();
  const initialQ = params.get("q") || "";
  const [q, setQ] = useState(initialQ);
  const [debounced, setDebounced] = useState(initialQ);

  useEffect(() => {
    const t = setTimeout(() => setDebounced(q), 350);
    return () => clearTimeout(t);
  }, [q]);

  useEffect(() => {
    if (debounced) setParams({ q: debounced });
  }, [debounced]); // eslint-disable-line

  const { data, isLoading, isError } = useQuery({
    queryKey: ["search", debounced],
    queryFn: () => searchService.search(debounced),
    enabled: debounced.length >= 2,
    placeholderData: keepPreviousData,
  });

  return (
    <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6">
      <h1 className="font-display text-2xl text-ink">جست‌وجو</h1>
      <input
        autoFocus
        value={q}
        onChange={(e) => setQ(e.target.value)}
        placeholder="نام فیلم، سریال، انجمن یا ژانر — فارسی یا انگلیسی…"
        className="mt-4 w-full rounded-wk-md border border-border bg-surface px-4 py-3 text-ink placeholder:text-ink-dim focus:border-gold/60"
      />

      <div className="mt-8 space-y-10">
        {debounced.length < 2 && (
          <p className="text-sm text-ink-dim">حداقل ۲ حرف وارد کنید.</p>
        )}

        {debounced.length >= 2 && (
          <p className="text-xs text-ink-dim">
            جست‌وجو در نام فارسی، عنوان انگلیسی و نام‌های جایگزین انجام می‌شود؛ از هر حرفی از کلمه هم که شروع کنید
            نتیجه پیدا می‌شود.
          </p>
        )}

        {isLoading && <LoadingSkeleton rows={3} />}
        {isError && <ErrorState message="جست‌وجو با خطا مواجه شد." />}

        {data && (
          <>
            {!!data.media?.length && (
              <section>
                <h2 className="mb-3 text-sm text-ink-dim">فیلم و سریال</h2>
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                  {data.media.map((m) => (
                    <MediaCard key={m.id} media={m} />
                  ))}
                </div>
              </section>
            )}

            {!!data.communities?.length && (
              <section>
                <h2 className="mb-3 text-sm text-ink-dim">انجمن‌ها</h2>
                <div className="grid gap-4 sm:grid-cols-2">
                  {data.communities.map((c) => (
                    <CommunityCard key={c.id} community={c} />
                  ))}
                </div>
              </section>
            )}

            {!data.media?.length && !data.communities?.length && (
              <EmptyState title={`نتیجه‌ای برای «${debounced}» پیدا نشد.`} />
            )}
          </>
        )}
      </div>
    </div>
  );
}
