import { Link, NavLink, useNavigate } from "react-router-dom";
import { useState } from "react";
import { Search, Menu, X, Film } from "lucide-react";
import { useAuth } from "../../hooks/useAuth";
import ThemeToggle from "../common/ThemeToggle";
import NotificationBell from "../common/NotificationBell";

const links = [
  { to: "/explore", label: "کاوش" },
  { to: "/explore?type=movie", label: "فیلم‌ها" },
  { to: "/explore?type=series", label: "سریال‌ها" },
  { to: "/about", label: "درباره ما" },
];

export default function Navbar() {
  const [open, setOpen] = useState(false);
  const [q, setQ] = useState("");
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  function submitSearch(e) {
    e.preventDefault();
    if (!q.trim()) return;
    navigate(`/search?q=${encodeURIComponent(q.trim())}`);
    setQ("");
  }

  return (
    <header className="sticky top-0 z-40 border-b border-border bg-bg/90 backdrop-blur-sm">
      <div className="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6">
        <Link to="/" className="flex items-center gap-2 shrink-0">
          <Film className="h-5 w-5 text-gold" strokeWidth={1.75} />
          <span className="font-display text-lg tracking-tight text-ink">
            واچ‌کرو<span className="lat text-gold"> WatchKru</span>
          </span>
        </Link>

        <nav className="hidden items-center gap-5 md:flex">
          {links.map((l) => (
            <NavLink
              key={l.to}
              to={l.to}
              className={({ isActive }) =>
                `text-sm transition-colors ${isActive ? "text-ink" : "text-ink-dim hover:text-ink"}`
              }
            >
              {l.label}
            </NavLink>
          ))}
        </nav>

        <form onSubmit={submitSearch} className="relative ms-auto hidden max-w-xs flex-1 sm:block">
          <Search className="pointer-events-none absolute top-1/2 -translate-y-1/2 inset-inline-start-3 h-4 w-4 text-ink-dim" />
          <input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="جست‌وجوی فیلم، سریال یا انجمن…"
            className="w-full rounded-wk-sm border border-border bg-surface py-2 ps-9 pe-3 text-sm text-ink placeholder:text-ink-dim focus:border-gold/60"
          />
        </form>

        <div className="hidden items-center gap-3 md:flex">
          {user && <NotificationBell />}
          <ThemeToggle />
          {user ? (
            <>
              <Link to="/chat" className="text-sm text-ink-dim hover:text-ink">
                گفت‌وگو
              </Link>
              <Link to="/dashboard" className="text-sm text-ink-dim hover:text-ink">
                {user.display_name || user.username}
              </Link>
              <button onClick={logout} className="text-sm text-ink-dim hover:text-danger">
                خروج
              </button>
            </>
          ) : (
            <>
              <Link to="/login" className="text-sm text-ink-dim hover:text-ink">
                ورود
              </Link>
              <Link
                to="/register"
                className="rounded-wk-sm bg-gold px-3.5 py-1.5 text-sm font-medium text-bg hover:bg-gold/90"
              >
                ثبت‌نام
              </Link>
            </>
          )}
        </div>

        <div className="md:hidden">
          <NotificationBell />
        </div>
        <ThemeToggle className="md:hidden" />

        <button className="md:hidden" onClick={() => setOpen((v) => !v)} aria-label="منو">
          {open ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
        </button>
      </div>

      {open && (
        <div className="border-t border-border px-4 py-4 md:hidden">
          <form onSubmit={submitSearch} className="relative mb-4">
            <Search className="pointer-events-none absolute top-1/2 -translate-y-1/2 inset-inline-start-3 h-4 w-4 text-ink-dim" />
            <input
              value={q}
              onChange={(e) => setQ(e.target.value)}
              placeholder="جست‌وجو…"
              className="w-full rounded-wk-sm border border-border bg-surface py-2 ps-9 pe-3 text-sm text-ink"
            />
          </form>
          <div className="flex flex-col gap-3">
            {links.map((l) => (
              <Link key={l.to} to={l.to} onClick={() => setOpen(false)} className="text-sm text-ink-dim">
                {l.label}
              </Link>
            ))}
            <Link to="/contact" onClick={() => setOpen(false)} className="text-sm text-ink-dim">
              تماس با ما
            </Link>
            <div className="sprocket-rule my-1" />
            {user ? (
              <>
                <Link to="/chat" onClick={() => setOpen(false)} className="text-sm text-ink-dim">
                  گفت‌وگو و اعلان‌ها
                </Link>
                <Link to="/dashboard" onClick={() => setOpen(false)} className="text-sm text-ink-dim">
                  داشبورد من
                </Link>
                <button onClick={logout} className="text-start text-sm text-danger">
                  خروج
                </button>
              </>
            ) : (
              <>
                <Link to="/login" onClick={() => setOpen(false)} className="text-sm text-ink-dim">
                  ورود
                </Link>
                <Link to="/register" onClick={() => setOpen(false)} className="text-sm text-gold">
                  ثبت‌نام
                </Link>
              </>
            )}
          </div>
        </div>
      )}
    </header>
  );
}
