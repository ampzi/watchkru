// GitHub Pages serves the site from /<repo>/ and has no PHP, so the static
// build (`npm run build:static`) turns this on. Everything then reads from
// `data/catalog.json` (exported by `php artisan watchkru:export-static`) and
// keeps user state in localStorage.
export const IS_STATIC = import.meta.env.VITE_STATIC === "true";

const base = import.meta.env.BASE_URL || "/";

/** Prefix a server-side path like /images/posters/x.jpg with the Pages base. */
export function asset(path) {
  if (!path) return path;
  if (/^(https?:)?\/\//.test(path) || path.startsWith("data:")) return path;
  if (path.startsWith("/")) return `${base.replace(/\/$/, "")}/${path.slice(1)}`;
  return path;
}
