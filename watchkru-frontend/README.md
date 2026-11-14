# واچ‌کرو — WatchKru (frontend)

React + Vite frontend for the WatchKru Persian movie/TV community directory. JavaScript (no TypeScript), Tailwind CSS v4, TanStack Query, React Hook Form + Zod, React Router, Persian-first RTL UI.

## Design system

The visual identity is defined in `src/styles/index.css` via Tailwind v4's `@theme`:

- **Palette**: `bg`, `surface`, `surface-2`, `ink`, `ink-dim`, `gold`, `teal`, `danger`, `border` — matching the brand brief. No other colors are introduced anywhere in the app.
- **Type**: **Peyda** for everything — UI text, headlines, ratings and the logotype. The font is self-hosted from the project-root `Peyda/` folder (woff2 + woff copied to `public/fonts/peyda/`), loaded via `@font-face` in `src/styles/index.css` and preloaded in `index.html`; there is no font CDN dependency. Headlines use Peyda's 700 weight instead of a separate display face.
- **Motif**: a "sprocket rule" (film-strip perforation) divider between major sections, horizontal "rail" galleries with scroll-snap for poster rows, flat cards (1px border, no drop shadows) rather than the generic soft-shadow card kit.
- RTL is the default document direction (`<html dir="rtl" lang="fa">`); Latin fragments (English titles, numbers, ratings) are wrapped in `.lat` to isolate their direction inside RTL flow.

## Themes (dark / light)

Dark is the default; the theme switcher sits in the navbar (desktop and mobile) and the choice is saved in `localStorage` under `wk_theme`. On the very first visit the OS `prefers-color-scheme` decides.

- Both themes share the token names from `@theme`; `html.light` in `src/styles/index.css` re-declares the same variables with light values, so no component needs a `dark:` variant. Light gold/teal are darkened to keep contrast on a cream background.
- `useTheme()` (`src/hooks/useTheme.jsx`) owns the state; a tiny inline script in `index.html` applies the saved class **before first paint**, so there is no flash of the wrong theme. `meta[name="theme-color"]` follows the theme too.

## Getting started

```bash
cp .env.example .env   # VITE_API_URL defaults to the Laravel backend on port 8787
npm install
npm run dev            # http://localhost:1258
```

The dev server is pinned to port **1258** (`strictPort: true`); the API runs on port **8787**.

## Pages

Beyond the catalog (home, explore, title, community, search, auth, dashboard) the app now has:

- **`/about`** — the full explanation of the project, verification model and FAQ.
- **`/contact`** — a working contact form (posts to `/contact`) plus icon cards with padded icon tiles.
- **`/chat`** — group chat: conversation list with unread badges, message bubbles, polling (3 s for messages, 15 s for the list), create-group flow (states that admin approval is required) and an owner-only «درخواست حذف» that also waits for admin approval.
- **Notification bell** in the navbar (signed-in users): polls `/user/notifications`, shows unread counts, marks everything read on open and links straight into the chat.

## Structure

```
src/
  app/            App shell + routes
  components/
    auth/         RequireAuth route guard
    common/        MediaCard, CommunityCard, badges, Pagination, empty/error states
    layout/        Navbar, Footer
  hooks/          useAuth (Sanctum token auth context), useTheme
  pages/          Home, Explore, MediaDetails, CommunityDetails, Search,
                  Login, Register, Dashboard, SubmitCommunity, admin/*
  services/       axios client + API service modules matching the backend's
                  /api/v1 routes
  styles/         global CSS + design tokens
```

## Notes / known limitations

- This is an MVP scaffold wired to the API contract documented in the backend README. It assumes a Bearer-token Sanctum flow (`wk_token` in `localStorage`); switch `src/services/api.js` to the cookie-based SPA flow if you prefer that instead.
- Pages fetch real data through TanStack Query — there is no mock data baked in, so you need the backend running (or a mock API) to see populated screens.
- Admin area is a deliberately plainer, denser UI (as instructed in the brief, only the *public* site avoids a dashboard look).
## Performance / UX

- **Route-level code splitting**: every page is a `React.lazy` chunk behind one `Suspense` fallback (poster-grid skeleton), so the home page ships only what it needs.
- **Cache-first navigation**: TanStack Query keeps catalog data for 5 minutes (`staleTime`) and 30 minutes (`gcTime`); returning to a page renders instantly from cache.
- **Prefetch on intent**: hovering or focusing a poster/community card warms its detail query, so the detail page opens without a loading state.
- **No list flashing**: paginated and search lists use `keepPreviousData` and fade while the next page loads instead of collapsing to skeletons.
- **Images**: posters/backdrops are `loading="lazy"` + `decoding="async"`, with reserved dimensions on the detail page to avoid layout shift; route changes scroll back to the top.

Full accessibility and automated test coverage are still left for a follow-up pass — see the backend README's "Known limitations" for the parallel list on the API side.
