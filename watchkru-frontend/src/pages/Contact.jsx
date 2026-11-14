import { useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { Mail, Clock, ShieldCheck, Send, MessageSquare } from "lucide-react";
import { contactService } from "../services/chatService";
import { useAuth } from "../hooks/useAuth";

const CARDS = [
  {
    icon: Mail,
    title: "ایمیل",
    text: "hello@watchkru.test",
    hint: "برای پیشنهاد همکاری، اصلاح اطلاعات یا حذف یک انجمن.",
  },
  {
    icon: Clock,
    title: "زمان پاسخ‌گویی",
    text: "حداکثر ۲ روز کاری",
    hint: "پیام‌های مربوط به گزارش‌های فوری اولویت بالاتری دارند.",
  },
  {
    icon: ShieldCheck,
    title: "حریم خصوصی",
    text: "ایمیل شما عمومی نمی‌شود",
    hint: "فقط برای پاسخ به همین پیام استفاده می‌کنیم.",
  },
];

export default function Contact() {
  const { user } = useAuth();
  const [form, setForm] = useState({
    name: "",
    email: "",
    subject: "",
    body: "",
  });
  const [sent, setSent] = useState(false);

  const mutation = useMutation({
    mutationFn: () => contactService.send(form),
    onSuccess: () => setSent(true),
  });

  function update(key) {
    return (e) => setForm((f) => ({ ...f, [key]: e.target.value }));
  }

  if (sent) {
    return (
      <div className="mx-auto max-w-lg px-4 py-20 text-center sm:px-6">
        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-gold/40 bg-gold/10 p-3">
          <Send className="h-6 w-6 text-gold" strokeWidth={1.75} />
        </div>
        <h1 className="font-display mt-4 text-2xl text-ink">پیام شما ثبت شد</h1>
        <p className="mt-2 text-sm leading-7 text-ink-dim">
          تیم واچ‌کرو پیام را در صف بررسی می‌بیند و در اولین فرصت پاسخ می‌دهد. اگر موضوع فوری است، می‌توانید در
          همان صفحه انجمن مربوطه «گزارش مشکل» بزنید.
        </p>
        <button
          onClick={() => {
            setSent(false);
            setForm({ name: "", email: "", subject: "", body: "" });
          }}
          className="mt-6 rounded-wk-sm border border-border px-4 py-2 text-sm text-ink hover:border-gold/60"
        >
          ارسال پیام دیگر
        </button>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-5xl px-4 py-12 sm:px-6">
      <header className="max-w-2xl">
        <p className="text-sm tracking-wide text-teal">تماس با ما</p>
        <h1 className="font-display mt-2 text-3xl text-ink">با واچ‌کرو حرف بزنید</h1>
        <p className="mt-3 leading-8 text-ink-dim">
          پیشنهاد انجمن تازه، اصلاح اطلاعات یک انجمن، همکاری یا هر مشکل دیگری را از همین صفحه برای ما بفرستید.
          همه پیام‌ها به صف بررسی مدیران می‌رود.
        </p>
      </header>

      <div className="mt-8 grid gap-4 sm:grid-cols-3">
        {CARDS.map((c) => (
          <div key={c.title} className="rounded-wk-md border border-border bg-surface p-5">
            <div className="inline-flex items-center justify-center rounded-full border border-gold/30 bg-gold/10 p-2.5">
              <c.icon className="h-5 w-5 text-gold" strokeWidth={1.75} />
            </div>
            <p className="mt-3 text-sm font-semibold text-ink">{c.title}</p>
            <p className="mt-0.5 text-sm text-teal">{c.text}</p>
            <p className="mt-2 text-xs leading-6 text-ink-dim">{c.hint}</p>
          </div>
        ))}
      </div>

      <div className="mt-8 grid gap-6 md:grid-cols-[1fr_320px]">
        <form
          onSubmit={(e) => {
            e.preventDefault();
            mutation.mutate();
          }}
          className="rounded-wk-md border border-border bg-surface p-5"
        >
          <p className="flex items-center gap-2 text-sm font-semibold text-ink">
            <MessageSquare className="h-4 w-4 text-teal" strokeWidth={1.75} />
            فرم تماس
          </p>

          <div className="mt-4 grid gap-3 sm:grid-cols-2">
            <label className="block">
              <span className="mb-1 block text-xs text-ink-dim">نام</span>
              <input
                value={form.name}
                onChange={update("name")}
                required
                placeholder={user?.display_name || "نام شما"}
                className="w-full rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm text-ink placeholder:text-ink-dim"
              />
            </label>
            <label className="block">
              <span className="mb-1 block text-xs text-ink-dim">ایمیل</span>
              <input
                type="email"
                dir="ltr"
                value={form.email}
                onChange={update("email")}
                required
                placeholder="you@example.com"
                className="w-full rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm text-ink placeholder:text-ink-dim"
              />
            </label>
          </div>

          <label className="mt-3 block">
            <span className="mb-1 block text-xs text-ink-dim">موضوع</span>
            <input
              value={form.subject}
              onChange={update("subject")}
              required
              placeholder="موضوع پیام"
              className="w-full rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm text-ink placeholder:text-ink-dim"
            />
          </label>

          <label className="mt-3 block">
            <span className="mb-1 block text-xs text-ink-dim">متن پیام</span>
            <textarea
              value={form.body}
              onChange={update("body")}
              required
              rows={6}
              placeholder="پیام خود را بنویسید…"
              className="w-full rounded-wk-sm border border-border bg-bg px-3 py-2 text-sm leading-7 text-ink placeholder:text-ink-dim"
            />
          </label>

          {mutation.isError && (
            <p className="mt-2 text-sm text-danger">
              {mutation.error?.response?.data?.message || "ارسال پیام ناموفق بود."}
            </p>
          )}

          <button
            type="submit"
            disabled={mutation.isPending}
            className="mt-4 inline-flex items-center gap-2 rounded-wk-sm bg-gold px-5 py-2.5 text-sm font-medium text-bg hover:bg-gold/90 disabled:opacity-50"
          >
            <Send className="h-4 w-4" strokeWidth={1.75} />
            {mutation.isPending ? "در حال ارسال…" : "ارسال پیام"}
          </button>
        </form>

        <aside className="space-y-4">
          <div className="rounded-wk-md border border-border bg-surface-2/60 p-5">
            <p className="text-sm font-semibold text-ink">می‌خواهید انجمنی اضافه کنید؟</p>
            <p className="mt-1 text-xs leading-6 text-ink-dim">
              برای انجمن‌ها لازم نیست پیام بفرستید؛ از صفحه «معرفی انجمن» استفاده کنید تا مستقیم به صف بررسی
              برود و وضعیتش را در داشبورد ببینید.
            </p>
          </div>
          <div className="rounded-wk-md border border-border bg-surface-2/60 p-5">
            <p className="text-sm font-semibold text-ink">گزارش یک لینک خراب؟</p>
            <p className="mt-1 text-xs leading-6 text-ink-dim">
              سریع‌ترین راه، دکمه «گزارش مشکل» در صفحه همان انجمن است.
            </p>
          </div>
        </aside>
      </div>
    </div>
  );
}
