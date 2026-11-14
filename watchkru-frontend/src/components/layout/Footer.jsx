import { Link } from "react-router-dom";

export default function Footer() {
  return (
    <footer className="border-t border-border">
      <div className="sprocket-rule" />
      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <div className="grid gap-8 sm:grid-cols-3">
          <div>
            <p className="font-display text-lg text-ink">
              واچ‌کرو<span className="lat text-gold"> WatchKru</span>
            </p>
            <p className="mt-2 max-w-xs text-sm text-ink-dim">
              پیدا کردن انجمن درست برای هر فیلم و سریال، در یک جا.
            </p>
          </div>
          <div>
            <p className="text-sm text-ink-dim">کاوش</p>
            <ul className="mt-3 space-y-2 text-sm">
              <li><Link to="/explore?type=movie" className="text-ink-dim hover:text-ink">فیلم‌ها</Link></li>
              <li><Link to="/explore?type=series" className="text-ink-dim hover:text-ink">سریال‌ها</Link></li>
              <li><Link to="/explore" className="text-ink-dim hover:text-ink">همه ژانرها</Link></li>
            </ul>
          </div>
          <div>
            <p className="text-sm text-ink-dim">انجمن</p>
            <ul className="mt-3 space-y-2 text-sm">
              <li><Link to="/about" className="text-ink-dim hover:text-ink">درباره واچ‌کرو</Link></li>
              <li><Link to="/contact" className="text-ink-dim hover:text-ink">تماس با ما</Link></li>
              <li><Link to="/chat" className="text-ink-dim hover:text-ink">گفت‌وگوی کاربران</Link></li>
              <li><Link to="/submit-community" className="text-ink-dim hover:text-ink">معرفی انجمن شما</Link></li>
              <li><Link to="/register" className="text-ink-dim hover:text-ink">ساخت حساب کاربری</Link></li>
            </ul>
          </div>
        </div>
        <div className="mt-10 flex flex-col gap-1 text-xs text-ink-dim sm:flex-row sm:items-center sm:justify-between">
          <p>
            © {new Date().getFullYear()} واچ‌کرو. تمامی اطلاعات انجمن‌ها توسط کاربران و تیم بررسی ارسال و تأیید می‌شود.
          </p>
          <p className="lat">
            Posters &amp; artwork: TMDB, Wikipedia
          </p>
        </div>
      </div>
    </footer>
  );
}
