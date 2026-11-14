import { Link } from "react-router-dom";
import {
  Search,
  ShieldCheck,
  Activity,
  Flag,
  BadgeCheck,
  Database,
  Users,
  Link2,
} from "lucide-react";

const STEPS = [
  {
    icon: Search,
    title: "عنوان را پیدا کنید",
    text: "نام فیلم یا سریال را جست‌وجو کنید یا از صفحه کاوش با فیلتر ژانر، سال و نوع (فیلم/سریال) به آن برسید. صفحه هر عنوان، فهرست انجمن‌های مرتبط با آن را نشان می‌دهد.",
  },
  {
    icon: BadgeCheck,
    title: "انجمن تأییدشده را انتخاب کنید",
    text: "هر انجمن یک وضعیت تأیید و یک وضعیت فعالیت دارد. انجمن‌های تأییدشده توسط تیم بررسی شده‌اند؛ وضعیت فعالیت می‌گوید همان انجمن امروز زنده است یا نه.",
  },
  {
    icon: Link2,
    title: "وارد انجمن شوید",
    text: "لینک هر انجمن به مقصد اصلی آن (تلگرام، دیسکورد، اینستاگرام، ردیت، واتس‌اپ یا فروم) باز می‌شود. واچ‌کرو خودش میزبان گفت‌وگو نیست؛ نقشهٔ راه است، نه اتاق گفت‌وگو.",
  },
  {
    icon: Flag,
    title: "به روز نگهش دارید",
    text: "اگر لینکی خراب شد، انجمنی غیرفعال شد یا توضیحی نادرست بود، گزارش دهید. هر گزارش به صف بررسی مدیران می‌رود و نتیجه‌اش روی همان صفحه اعمال می‌شود.",
  },
];

const PILLARS = [
  {
    icon: Database,
    title: "یک آرشیو، نه ده‌ها لیست پراکنده",
    text: "انجمن‌های فارسی‌زبان فیلم و سریال معمولاً در تلگرام، دیسکورد و اینستاگرام پخش شده‌اند و پیدا کردنشان سخت است. واچ‌کرو آن‌ها را حول «عنوان» سازمان می‌دهد تا از یک نقطه شروع کنید.",
  },
  {
    icon: ShieldCheck,
    title: "تأیید انسانی، نه شمارش خودکار",
    text: "هیچ انجمنی بدون بررسی تیم منتشر نمی‌شود: لینک باز می‌شود، محتوا با عنوان و دسته تطبیق داده می‌شود و تکراری بودن آن با نرمال‌سازی آدرس بررسی می‌شود.",
  },
  {
    icon: Activity,
    title: "وضعیت مشخص، بدون حدس",
    text: "«تأییدشده» یعنی واقعی و معتبر؛ «فعال» یعنی امروز در حال استفاده. این دو هرگز با هم اشتباه نمی‌شوند، چون انجمن سالمی هم می‌تواند بعداً خاموش شود.",
  },
  {
    icon: Users,
    title: "ساخته‌شده با کاربران",
    text: "بیشتر آرشیو از پیشنهاد کاربران می‌آید. هر کسی می‌تواند انجمن معرفی کند؛ تیم آن را بررسی می‌کند و نتیجه را در داشبورد همان کاربر نشان می‌دهد.",
  },
];

const PRINCIPLES = [
  "هیچ عنوان یا انجمن ساختگی به آرشیو اضافه نمی‌شود؛ اگر اطلاعاتی در دسترس نیست، خالی می‌ماند.",
  "تعداد اعضا فقط وقتی نمایش داده می‌شود که عدد عمومی و قابل استناد وجود داشته باشد — عدد تقریبی به‌عنوان واقعیت جا زده نمی‌شود.",
  "محتوای انجمن‌ها کپی نمی‌شود؛ واچ‌کرو فقط لینک، توضیح کوتاه و وضعیت را نگه می‌دارد.",
  "گزارش‌ها و پیشنهادها بدون بررسی منتشر نمی‌شوند و هر تغییر در پنل مدیریت ثبت می‌شود.",
];

const FAQ = [
  {
    q: "واچ‌کرو خودش انجمن یا گروه دارد؟",
    a: "نه. واچ‌کرو یک فهرست قابل جست‌وجو از انجمن‌های موجود در پلتفرم‌های دیگر است. گفت‌وگو، تماشای گروهی و بحث قسمت‌به‌قسمت در همان پلتفرم مقصد اتفاق می‌افتد.",
  },
  {
    q: "تفاوت «تأییدشده» و «فعال» چیست؟",
    a: "تأیید یعنی لینک و مشخصات انجمن بررسی شده و با عنوان مرتبط است. فعال یعنی انجمن در آخرین بررسی هنوز استفاده می‌شود. یک انجمن می‌تواند تأییدشده اما غیرفعال باشد؛ در این حالت برچسب غیرفعال را می‌بینید.",
  },
  {
    q: "چطور انجمن خودم را اضافه کنم؟",
    a: "از صفحه «معرفی انجمن» نام، پلتفرم، لینک، عنوان مرتبط و توضیح را وارد کنید. پیشنهاد شما در صف بررسی قرار می‌گیرد و وضعیتش را از داشبورد خودتان می‌بینید.",
  },
  {
    q: "اگر لینکی خراب بود چه کنم؟",
    a: "در صفحه همان انجمن، دکمه «گزارش مشکل» را بزنید و نوع مشکل را انتخاب کنید. گزارش به‌صورت خودکار به صف بررسی مدیران می‌رسد و پس از تأیید، لینک اصلاح یا حذف می‌شود.",
  },
  {
    q: "اطلاعات کاربران چه می‌شود؟",
    a: "برای ثبت گزارش یا نظر باید وارد حساب شوید؛ فقط نام نمایشی شما کنار نظر دیده می‌شود و ایمیل شما عمومی نیست. برای ساخت حساب تنها نام کاربری، ایمیل و رمز عبور لازم است.",
  },
];

export default function About() {
  return (
    <div>
      <section className="relative overflow-hidden border-b border-border">
        <div
          className="pointer-events-none absolute inset-0 opacity-70"
          style={{
            background:
              "radial-gradient(60% 50% at 85% 0%, rgba(230,184,92,0.10), transparent), radial-gradient(50% 40% at 5% 100%, rgba(119,200,181,0.08), transparent)",
          }}
        />
        <div className="relative mx-auto max-w-4xl px-4 py-16 sm:px-6 sm:py-20">
          <p className="text-sm tracking-wide text-teal">درباره ما</p>
          <h1 className="font-display mt-3 text-3xl leading-tight text-ink sm:text-4xl">
            واچ‌کرو؛ راهنمای پیدا کردن انجمن درست برای هر فیلم و سریال
          </h1>
          <p className="mt-5 max-w-2xl leading-8 text-ink-dim">
            واچ‌کرو یک آرشیو عمومی و قابل جست‌وجو از کانال‌های تلگرام، سرورهای دیسکورد، پیج‌های اینستاگرام،
            انجمن‌های ردیت و گروه‌های واتس‌اپ فارسی‌زبان است که حول فیلم‌ها و سریال‌ها ساخته شده‌اند.
            هدف ساده است: وقتی از یک سریال خوشتان آمد، بدانید کجا می‌توانید درباره‌اش حرف بزنید — بدون
            گشتن بی‌پایان در ده‌ها لینک پراکنده و بدون گرفتار شدن در گروه‌های مرده.
          </p>
          <div className="mt-7 flex flex-wrap gap-3">
            <Link
              to="/explore"
              className="rounded-wk-sm bg-gold px-5 py-2.5 text-sm font-medium text-bg hover:bg-gold/90"
            >
              شروع کاوش
            </Link>
            <Link
              to="/submit-community"
              className="rounded-wk-sm border border-border px-5 py-2.5 text-sm text-ink hover:border-gold/60"
            >
              معرفی انجمن
            </Link>
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-4xl px-4 py-14 sm:px-6">
        <h2 className="font-display text-2xl text-ink">چرا واچ‌کرو وجود دارد</h2>
        <div className="mt-4 space-y-4 leading-8 text-ink-dim">
          <p>
            انجمن‌های فارسی‌زبان فیلم و سریال در چند پلتفرم مختلف زندگی می‌کنند و هیچ مرکزی برای پیدا کردنشان
            نیست. یک گروه تلگرامی خوب معمولاً با معرفی دوستان پیدا می‌شود، لینک‌های قدیمی در پیام‌های پین‌شده
            گم می‌شوند و معلوم نیست کدام گروه هنوز فعال است. برای بیننده‌ای که تازه سریالی را شروع کرده، این
            یعنی وقت گذاشتن روی جست‌وجو به‌جای لذت بردن از بحث.
          </p>
          <p>
            واچ‌کرو این پراکندگی را جمع می‌کند: هر عنوان یک صفحه دارد، هر صفحه فهرست انجمن‌های مرتبط با آن را
            نشان می‌دهد، و هر انجمن دو چیز را شفاف می‌گوید — اینکه بررسی شده است و اینکه امروز زنده است یا نه.
            آرشیو با پیشنهاد کاربران رشد می‌کند و تیم واچ‌کرو هر مورد را پیش از انتشار بررسی می‌کند.
          </p>
        </div>
      </section>

      <div className="sprocket-rule mx-auto max-w-4xl" />

      <section className="mx-auto max-w-4xl px-4 py-14 sm:px-6">
        <h2 className="font-display text-2xl text-ink">اصول کاری ما</h2>
        <ul className="mt-5 space-y-3">
          {PRINCIPLES.map((p) => (
            <li key={p} className="flex gap-3 rounded-wk-md border border-border bg-surface p-4">
              <ShieldCheck className="mt-0.5 h-5 w-5 shrink-0 text-teal" strokeWidth={1.75} />
              <span className="text-sm leading-7 text-ink-dim">{p}</span>
            </li>
          ))}
        </ul>
      </section>

      <div className="sprocket-rule mx-auto max-w-4xl" />

      <section className="mx-auto max-w-4xl px-4 py-14 sm:px-6">
        <h2 className="font-display text-2xl text-ink">واچ‌کرو چطور کار می‌کند</h2>
        <ol className="mt-6 space-y-6">
          {STEPS.map((s, i) => (
            <li key={s.title} className="flex gap-4">
              <div className="flex flex-col items-center">
                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gold/40 bg-gold/10">
                  <s.icon className="h-5 w-5 text-gold" strokeWidth={1.75} />
                </span>
                {i < STEPS.length - 1 && <span className="mt-2 w-px flex-1 bg-border" />}
              </div>
              <div className="pb-2">
                <p className="text-sm font-semibold text-ink">
                  <span className="lat text-ink-dim">{i + 1}. </span>
                  {s.title}
                </p>
                <p className="mt-1 text-sm leading-7 text-ink-dim">{s.text}</p>
              </div>
            </li>
          ))}
        </ol>
      </section>

      <div className="sprocket-rule mx-auto max-w-4xl" />

      <section className="mx-auto max-w-4xl px-4 py-14 sm:px-6">
        <h2 className="font-display text-2xl text-ink">تأیید، فعالیت و اعتماد</h2>
        <p className="mt-4 leading-8 text-ink-dim">
          هر انجمن پیش از انتشار از یک مسیر بررسی عبور می‌کند. اول لینک باز می‌شود و مطمئن می‌شویم به همان
          انجمنی می‌رود که ادعا شده و به محتوای نامرتبط یا صفحه حذف‌شده نمی‌رسد. بعد محتوا با عنوان و دسته
          تطبیق داده می‌شود؛ مثلاً یک گروه درباره «سیلو» نباید در صفحه سریال دیگری ظاهر شود. در پایان، آدرس
          نرمال‌سازی می‌شود تا یک انجمن دو بار ثبت نشود.
        </p>
        <div className="mt-6 grid gap-4 sm:grid-cols-2">
          <div className="rounded-wk-md border border-border bg-surface p-5">
            <p className="flex items-center gap-2 text-sm font-semibold text-ink">
              <BadgeCheck className="h-4 w-4 text-teal" strokeWidth={1.75} />
              وضعیت تأیید
            </p>
            <ul className="mt-3 space-y-2 text-sm leading-7 text-ink-dim">
              <li>تأییدشده: بررسی شده و در فهرست عمومی نمایش داده می‌شود.</li>
              <li>در انتظار بررسی: پیشنهاد کاربر است و هنوز منتشر نشده.</li>
              <li>نیاز به بازبینی: نشانه‌هایی از تغییر یا مشکل دارد و دوباره بررسی می‌شود.</li>
              <li>ردشده: لینک معتبر نیست یا محتوایش با قوانین نمی‌خواند.</li>
            </ul>
          </div>
          <div className="rounded-wk-md border border-border bg-surface p-5">
            <p className="flex items-center gap-2 text-sm font-semibold text-ink">
              <Activity className="h-4 w-4 text-gold" strokeWidth={1.75} />
              وضعیت فعالیت
            </p>
            <ul className="mt-3 space-y-2 text-sm leading-7 text-ink-dim">
              <li>فعال: در آخرین بررسی پیام‌ها یا فعالیت تازه داشته است.</li>
              <li>غیرفعال: مدتی است که رها شده یا لینکش پاسخ نمی‌دهد.</li>
              <li>نامشخص: هنوز بررسی فعالیت انجام نشده است.</li>
            </ul>
            <p className="mt-3 text-xs leading-6 text-ink-dim">
              گزارش کاربران مهم‌ترین منبع این بررسی‌هاست؛ اگر انجمنی را می‌شناسید که وضعیتش عوض شده، همان‌جا
              گزارش دهید.
            </p>
          </div>
        </div>
      </section>

      <div className="sprocket-rule mx-auto max-w-4xl" />

      <section className="mx-auto max-w-4xl px-4 py-14 sm:px-6">
        <h2 className="font-display text-2xl text-ink">چه چیزی در آرشیو هست</h2>
        <div className="mt-6 grid gap-4 sm:grid-cols-3">
          {[
            { value: "فیلم و سریال", label: "عناوین واقعی با ژانر، سال، بازیگران و خلاصه" },
            { value: "۷ پلتفرم", label: "تلگرام، دیسکورد، اینستاگرام، ردیت، واتس‌اپ، فروم و سایر" },
            { value: "تأیید انسانی", label: "هر انجمن پیش از انتشار توسط تیم بررسی می‌شود" },
          ].map((b) => (
            <div key={b.value} className="rounded-wk-md border border-border bg-surface p-5">
              <p className="text-base font-semibold text-ink">{b.value}</p>
              <p className="mt-1 text-sm leading-7 text-ink-dim">{b.label}</p>
            </div>
          ))}
        </div>
        <div className="mt-6 flex flex-wrap gap-3">
          {PILLARS.map((p) => (
            <span
              key={p.title}
              className="inline-flex items-center gap-2 rounded-wk-sm border border-border px-3 py-1.5 text-xs text-ink-dim"
            >
              <p.icon className="h-3.5 w-3.5 text-gold" strokeWidth={1.75} />
              {p.title}
            </span>
          ))}
        </div>
      </section>

      <div className="sprocket-rule mx-auto max-w-4xl" />

      <section className="mx-auto max-w-4xl px-4 py-14 sm:px-6">
        <h2 className="font-display text-2xl text-ink">پرسش‌های متداول</h2>
        <div className="mt-5 divide-y divide-border rounded-wk-md border border-border bg-surface">
          {FAQ.map((item) => (
            <details key={item.q} className="group px-5 py-4">
              <summary className="cursor-pointer list-none text-sm font-medium text-ink marker:hidden">
                <span className="flex items-center justify-between gap-3">
                  {item.q}
                  <span className="text-ink-dim transition-transform group-open:rotate-45">+</span>
                </span>
              </summary>
              <p className="mt-3 text-sm leading-7 text-ink-dim">{item.a}</p>
            </details>
          ))}
        </div>
      </section>

      <section className="border-t border-border bg-surface">
        <div className="mx-auto flex max-w-4xl flex-col items-start gap-4 px-4 py-14 sm:flex-row sm:items-center sm:justify-between sm:px-6">
          <div>
            <h2 className="font-display text-xl text-ink">انجمنی می‌شناسید که در آرشیو نیست؟</h2>
            <p className="mt-1 text-sm text-ink-dim">
              معرفی شما پس از بررسی در صفحه عنوان مربوطه منتشر می‌شود.
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
