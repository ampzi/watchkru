import { Moon, Sun } from "lucide-react";
import { useTheme } from "../../hooks/useTheme";

export default function ThemeToggle({ className = "" }) {
  const { theme, toggle } = useTheme();
  const isDark = theme === "dark";

  return (
    <button
      type="button"
      onClick={toggle}
      aria-label={isDark ? "تغییر به حالت روشن" : "تغییر به حالت تیره"}
      title={isDark ? "حالت روشن" : "حالت تیره"}
      className={`inline-flex h-9 w-9 items-center justify-center rounded-wk-sm border border-border text-ink-dim transition-colors hover:border-gold/60 hover:text-ink ${className}`}
    >
      {isDark ? <Sun className="h-4 w-4" strokeWidth={1.75} /> : <Moon className="h-4 w-4" strokeWidth={1.75} />}
    </button>
  );
}
