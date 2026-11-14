import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import { Search, ShieldCheck, Activity, BadgeCheck, Flag } from "lucide-react";
import { mediaService, communityService } from "../services/authService";
import MediaCard from "../components/common/MediaCard";
import CommunityCard from "../components/common/CommunityCard";
import { PosterGridSkeleton, EmptyState, ErrorState } from "../components/common/states";

function Rail({ title, subtitle, to, children }) {
  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <div className="mb-4 flex items-end justify-between">
        <div>
          <h2 className="font-display text-xl text-ink">{title}</h2>
          {subtitle && <p className="mt-0.5 text-sm text-ink-dim">{subtitle}</p>}
        </div>
        {to && (
          <Link to={to} className="text-sm text-gold hover:text-gold/80">
            مشاهده همه
          </Link>
        )}
      </div>
      {children}
    </section>
  );
}

function PopularMedia() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ["media", "popular"],
    queryFn: () => mediaService.list({ sort: "popular", per_page: 12 }),
  });

  if (isLoading) return <PosterGridSkeleton count={7} />;
  if (isError) return <ErrorState message="فیلم‌ها و سریال‌های محبوب بارگذاری نشد." />;
  if (!data?.data?.length)
    return <EmptyState title="هنوز محتوایی ثبت نشده است." hint="به‌زودی عناوین محبوب اینجا نمایش داده می‌شوند." />;

  return (
    <div className="rail -mx-4 flex gap-4 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6">
      {data.data.map((m) => (
        <div key={m.id} className="rail-item w-[168px] shrink-0 sm:w-[192px]">
          <MediaCard media={m} />
        </div>
      ))}
    </div>
  );
}

function TrendingCommunities() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ["communities", "trending"],
    queryFn: () => communityService.list({ sort: "trending", per_page: 6, verification_status: "verified" }),
  });

  if (isLoading)
    return (
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {Array.from({ length: 3 }).map((_, i) => (
          <div key={i} className="h-32 animate-pulse rounded-wk-md border border-border bg-surface-2/60" />
        ))}
      </div>
    );
  if (isError) return <ErrorState message="انجمن‌های پرطرفدار بارگذاری نشد." />;
  if (!data?.data?.length)
    return <EmptyState title="هنوز انجمنی تأیید نشده است." hint="اولین نفری باشید که انجمنی معرفی می‌کند." />;

  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {data.data.map((c) => (
        <CommunityCard key={c.id} community={c} />
      ))}
    </div>
  );
}

function RecentCommunities() {
  const { data, isLoading } = useQuery({
    queryKey: ["communities", "recent"],
    queryFn: () => communityService.list({ sort: "newest", per_page: 6, verification_status: "verified" }),
  });

  if (isLoading || !data?.data?.length) return null;

  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {data.data.map((c) => (
        <CommunityCard key={c.id} community={c} />
      ))}
    </div>
  );
}

const ABOUT_POINTS = [
  {
    icon: ShieldCheck,
    title: "همه‌چیز بررسی‌شده",
    text: "هیچ لینکی بدون باز شدن و تطبیق با عنوان منتشر نمی‌شود؛ آدرس‌ها هم برای جلوگیری از ثبت تکراری نرمال می‌شوند.",
  },
  {
    icon: Activity,
    title: "وضعیت شفاف",
    text: "تأییدشده یعنی معتبر، فعال یعنی امروز زنده. این دو جدا نشان داده می‌شوند تا در گروه‌های رهاشده وقت تلف نکنید.",
  },
  {
    icon: Flag,
    title: "با گزارش شما به‌روز می‌ماند",
    text: "لینک خراب یا انجمن غیرفعال را گزارش دهید؛ گزارش به صف بررسی مدیران می‌رود و وضعیت همان صفحه اصلاح می‌شود.",
  },
];

const ABOUT_STEPS = [
  { n: "۱", title: "عنوان را پیدا کنید", text: "با جست‌وجو یا فیلترهای کاوش" },
  { n: "۲", title: "انجمن را انتخاب کنید", text: "تأییدشده و فعال را ببینید" },
  { n: "۳", title: "در پلتفرم مقصد بحث کنید", text: "تلگرام، دیسکورد، ردیت…" },
  { n: "۴", title: "وضعیت را به‌روز نگه دارید", text: "گزارش یا معرفی انجمن تازه" },
];

function AboutSection() {
  return (
    <section className="border-t border-border">
      <div className="mx-auto max-w-7xl px-4 py-14 sm:px-6">
        <div className="flex flex-wrap items-end justify-between gap-3">
          <div className="max-w-2xl">
            <p className="text-sm tracking-wide text-teal">درباره واچ‌کرو</p>
            <h2 className="font-display mt-2 text-2xl text-ink sm:text-3xl">
              یک نقشهٔ راه برای انجمن‌های فارسی فیلم و سریال
            </h2>
            <p className="mt-3 leading-8 text-ink-dim">
              انجمن‌های فارسی‌زبان هر سریال در تلگرام، دیسکورد، اینستاگرام و ردیت پخش شده‌اند؛ پیدا کردنشان
              سخت است و معلوم نیست کدام‌شان هنوز زنده است. واچ‌کرو آن‌ها را حول هر عنوان جمع می‌کند، پیش از
              انتشار بررسی می‌کند و وضعیت تأیید و فعالیت هر انجمن را شفاف نشان می‌دهد. واچ‌کرو خودش میزبان
              گفت‌وگو نیست؛ فقط راه رسیدن به انجمن درست را کوتاه می‌کند.
            </p>
          </div>
          <Link to="/about" className="text-sm text-gold hover:text-gold/80">
            بیشتر درباره واچ‌کرو
          </Link>
        </div>

        <div className="mt-8 grid gap-4 sm:grid-cols-3">
          {ABOUT_POINTS.map((p) => (
            <div key={p.title} className="rounded-wk-md border border-border bg-surface p-5">
              <p.icon className="h-5 w-5 text-gold" strokeWidth={1.75} />
              <h3 className="mt-3 text-sm font-semibold text-ink">{p.title}</h3>
              <p className="mt-1 text-sm leading-7 text-ink-dim">{p.text}</p>
            </div>
          ))}
        </div>

        <div className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          {ABOUT_STEPS.map((s) => (
            <div
              key={s.n}
              className="flex items-start gap-3 rounded-wk-md border border-border bg-surface-2/60 p-4"
            >
              <span className="lat flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-gold/40 text-xs text-gold">
                {s.n}
              </span>
              <div>
                <p className="text-sm text-ink">{s.title}</p>
                <p className="mt-0.5 text-xs text-ink-dim">{s.text}</p>
              </div>
            </div>
          ))}
        </div>

        <div className="mt-6 flex flex-wrap items-center gap-3 text-xs text-ink-dim">
          <span className="inline-flex items-center gap-1.5">
            <BadgeCheck className="h-3.5 w-3.5 text-teal" strokeWidth={1.75} />
            تأیید انسانی هر انجمن
          </span>
          <span>· بدون دادهٔ ساختگی در آرشیو</span>
          <span>· تعداد اعضا فقط وقتی عمومی باشد</span>
        </div>
      </div>
    </section>
  );
}

function PopularGenres() {
  const { data } = useQuery({ queryKey: ["genres"], queryFn: mediaService.genres });
  if (!data?.length) return null;

  return (
    <div className="flex flex-wrap gap-2">
      {data.slice(0, 12).map((g) => (
        <Link
          key={g.id}
          to={`/explore?genre=${g.slug}`}
          className="rounded-wk-sm border border-border px-3.5 py-1.5 text-sm text-ink-dim transition-colors hover:border-gold/50 hover:text-ink"
        >
          {g.name}
        </Link>
      ))}
    </div>
  );
}

export default function Home() {
  const [q, setQ] = useState("");
  const navigate = useNavigate();

  function submitSearch(e) {
    e.preventDefault();
    if (!q.trim()) return;
    navigate(`/search?q=${encodeURIComponent(q.trim())}`);
  }

  return (
    <div>
      <section className="relative overflow-hidden border-b border-border">
        <div
          className="pointer-events-none absolute inset-0 opacity-70"
          style={{
            background:
              "radial-gradient(60% 50% at 80% 0%, rgba(230,184,92,0.10), transparent), radial-gradient(50% 40% at 10% 100%, rgba(119,200,181,0.08), transparent)",
          }}
        />
        <div className="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28">
          <p className="lat text-sm tracking-wide text-teal">WatchKru</p>
          <h1 className="font-display mt-3 max-w-2xl text-4xl leading-tight text-ink sm:text-5xl">
            انجمن درست را برای هر فیلم و سریالی که دوست دارید پیدا کنید
          </h1>
          <p className="mt-4 max-w-xl text-ink-dim">
            کانال‌های تلگرام، سرورهای دیسکورد و پیج‌های اینستاگرام فارسی‌زبان مربوط به سریال و فیلم مورد علاقه‌تان،
            همه در یک آرشیو قابل جست‌وجو.
          </p>
          <form onSubmit={submitSearch} className="relative mt-8 max-w-lg">
            <Search className="pointer-events-none absolute top-1/2 -translate-y-1/2 inset-inline-start-4 h-4 w-4 text-ink-dim" />
            <input
              value={q}
              onChange={(e) => setQ(e.target.value)}
              placeholder="مثلاً «سایلو» یا «اینترستلار»…"
              className="w-full rounded-wk-md border border-border bg-surface py-3.5 ps-11 pe-32 text-ink placeholder:text-ink-dim focus:border-gold/60"
            />
            <button
              type="submit"
              className="absolute top-1/2 -translate-y-1/2 inset-inline-end-1.5 rounded-wk-sm bg-gold px-4 py-2 text-sm font-medium text-bg hover:bg-gold/90"
            >
              جست‌وجو
            </button>
          </form>
        </div>
      </section>

      <Rail title="محبوب این روزها" to="/explore?sort=popular">
        <PopularMedia />
      </Rail>

      <div className="sprocket-rule mx-auto max-w-7xl" />

      <Rail title="انجمن‌های پرطرفدار" subtitle="تأییدشده توسط تیم واچ‌کرو">
        <TrendingCommunities />
      </Rail>

      <Rail title="ژانرهای محبوب">
        <PopularGenres />
      </Rail>

      <Rail title="تازه‌ترین انجمن‌ها">
        <RecentCommunities />
      </Rail>

      <AboutSection />

      <section className="border-t border-border bg-surface">
        <div className="mx-auto flex max-w-7xl flex-col items-start gap-4 px-4 py-14 sm:flex-row sm:items-center sm:justify-between sm:px-6">
          <div>
            <h2 className="font-display text-xl text-ink">انجمنی برای معرفی دارید؟</h2>
            <p className="mt-1 text-sm text-ink-dim">
              انجمن خود را ثبت کنید تا پس از بررسی، در صفحه فیلم یا سریال مربوطه نمایش داده شود.
            </p>
          </div>
          <Link
            to="/submit-community"
            className="shrink-0 rounded-wk-sm bg-gold px-5 py-2.5 text-sm font-medium text-bg hover:bg-gold/90"
          >
            معرفی انجمن شما
          </Link>
        </div>
      </section>
    </div>
  );
}
