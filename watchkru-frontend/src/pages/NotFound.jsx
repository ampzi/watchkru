import { Link } from "react-router-dom";

export default function NotFound() {
  return (
    <div className="mx-auto max-w-md px-4 py-28 text-center sm:px-6">
      <p className="font-display text-5xl text-gold">۴۰۴</p>
      <h1 className="mt-3 text-ink">این صفحه پیدا نشد</h1>
      <p className="mt-1 text-sm text-ink-dim">ممکن است آدرس اشتباه باشد یا صفحه حذف شده باشد.</p>
      <Link to="/" className="mt-6 inline-block rounded-wk-sm border border-border px-5 py-2.5 text-sm text-ink hover:border-gold/50">
        بازگشت به خانه
      </Link>
    </div>
  );
}
