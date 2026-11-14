# واچ‌کرو — WatchKru (backend)

Laravel 13 API backend for WatchKru, a Persian-language directory for discovering online communities (Telegram, Discord, Instagram, Reddit, WhatsApp, forums) organized around movies and TV series. This is the **Phase 1 MVP** described in the project brief: a verified, moderated community directory — not yet the internal discussion/watch-together features planned for later phases.

## Why these architectural choices

- **Modular monolith, not microservices.** A single Laravel app organized by domain (`Media`, `Community`, moderation, auth) is enough at MVP scale and keeps deployment and debugging simple. Future phases (discussion boards, watch-together) can become their own modules inside the same app without a rewrite, because `Community` and `Media` are already decoupled — a discussion board for a `Media` row doesn't need to know anything about `Community`.
- **Verification status ≠ activity status.** These are stored as two separate enum columns on `communities` (see Section 7 of the brief). A community can be `verified` and later become `inactive`; the UI never conflates the two.
- **Member counts are null unless verified.** `CommunityResource` only returns `member_count` when `verification_status === verified`, so the frontend can never accidentally display an unverified, possibly-fabricated number as fact (Principle 3: "Never invent community information").
- **Duplicate detection, not auto-merging.** `App\Services\DuplicateCommunityDetector` normalizes URLs (lower-cased host, no `www.`, no trailing slash, no query string) to catch exact-URL duplicates, and flags *similar-name* candidates for the same media item as a warning — it never merges or rejects automatically, per the brief's explicit instruction.
- **REST + Sanctum, token-based.** The frontend is a separate SPA, so Sanctum issues bearer tokens (`personal_access_tokens`) rather than relying on stateful cookie sessions, which keeps the API usable from non-browser clients too. CORS is scoped to `FRONTEND_URL`/`CORS_ALLOWED_ORIGINS` only.
- **Backend-enforced authorization everywhere.** The `role` middleware (`App\Http\Middleware\EnsureUserHasRole`) guards every `/api/v1/admin/*` route; the frontend's own role checks are UX only, never the real gate.
- **External metadata (TMDB) is optional and inert by default.** `media` has nullable `external_provider`/`external_id` columns and `config/services.php` has a `tmdb.enabled` flag defaulting to `false`. Nothing in the app currently calls out to TMDB — admins create/edit titles manually — so the app works with zero external API keys configured, as required.

## Stack

PHP 8.3, Laravel 13, Sanctum (token auth), MySQL/PostgreSQL (SQLite for tests), Form Requests for validation, Eloquent Resources for API shape, a `role` middleware for authorization, an `AuditLog` model for moderation actions.

## Getting started

```bash
cp .env.example .env
composer install
php artisan key:generate

# create the database named in .env (DB_DATABASE=watchkru), then:
php artisan migrate --seed
php artisan serve --port=8787
```

The API listens on **http://localhost:8787**, and the frontend (Vite) on **http://localhost:1258**.

The seeders create (all idempotent — `migrate:fresh --seed` or a plain `db:seed` both work):
- Staff accounts: **admin@watchkru.test / password** (super_admin) and **moderator@watchkru.test / password** (moderator). Change these immediately in any real deployment.
- 8 regular members with Persian names, genres, platforms and community categories.
- 7 real titles with factual metadata (air dates, season/episode counts, creators, main cast): **The 100**, **Silo**, Interstellar, The Bear, Dune: Part Two, True Detective and Oppenheimer. Each has a real poster downloaded to `public/images/posters/<slug>.jpg` (mirrored in the frontend's `public/images/posters/`) and referenced as a relative path, so artwork works on both ports and in a production build; posters/artwork are credited to TMDB in the site footer.
- 11 real, publicly reachable communities for those titles (Reddit subreddits, the The 100 Fandom wiki, the official CW Instagram account, the Apple TV+ Silo press page, Hugh Howey's site), all in `verified` status, plus seeded Persian reviews so rating averages are populated. No random/fake catalog rows are generated.
- 16 reviews, 4 community submissions (2 pending, 1 approved, 1 rejected), 4 reports (2 open, 1 resolved, 1 dismissed) and 24 favorites, so every moderation queue, dashboard chart and the notification bell has realistic content on a fresh install.

Member counts on seeded communities are approximate public subscriber snapshots from November 2026; entries with no public counter keep `member_count` empty rather than inventing a figure.

## Chat, contact and search

**Chat** (`chat_groups`, `chat_group_user`, `chat_messages`)

- Any signed-in user can create a group; it starts as `pending` and only staff can approve it (`Filament → گروه‌های گفت‌وگو`), so a group is invisible in the chat list to outsiders until then. Rejection carries a note that is shown to the creator.
- Deleting a group is also a request: the owner presses «درخواست حذف», the group moves to `deletion_requested`, and staff approve the actual deletion (with a notification back to the owner).
- Messages are plain text (max 2000 chars). The chat page polls `GET /chat/groups/{id}/messages` every 3 s and the group list every 15 s; `last_read_message_id` per member drives the unread badges.
- Every new message also writes a database notification (and staff get one for new groups and deletion requests), which the site's notification bell reads from `GET /user/notifications`.

**Contact** — `POST /contact` (public, throttled) stores the message in `contact_messages`, notifies staff, and the submission is reviewed in `Filament → پیام‌های تماس`.

**Search** — `GET /search?q=` normalises Persian/Arabic letter variants (ي/ی، ك/ک، ة/ه، آ/ا), removes ZWNJ, converts Persian digits and matches the query as a substring (from the first character or any later one) against titles, original titles, alternative Persian titles, slugs, cast and genre names, plus community names and descriptions. Titles that start with the query rank first.

## Best-of series catalogue (110 titles)

`database/data/top-series.json` holds 110 well-known series (Game of Thrones, Breaking Bad, Friends, The Office, Chernobyl, Better Call Saul, …) sourced from **Wikidata**: TV series that have a TMDB id, ranked by Wikipedia sitelinks, with first-air year and the Wikipedia summary as the description. Poster handling is deliberately honest:

- where Wikipedia has a poster-shaped lead image, it is downloaded into `public/images/posters/series/` and used directly;
- otherwise a typographic WatchKru card (SVG) is generated from the real title and year, with the show's actual lead artwork embedded when one exists — no invented movie art.

```bash
# re-fetch the candidate list (one SPARQL query, ~30 s)
curl -G https://query.wikidata.org/sparql --data-urlencode format=json \
  --data-urlencode 'query=SELECT ?item ?itemLabel ?tmdb ?start ?date ?links WHERE { ?item wdt:P4983 ?tmdb ; wikibase:sitelinks ?links . FILTER(?links >= 30) ?item wdt:P31/wdt:P279* wd:Q5398426 . OPTIONAL { ?item wdt:P580 ?start . } OPTIONAL { ?item wdt:P577 ?date . } SERVICE wikibase:label { bd:serviceParam wikibase:language "en". } } ORDER BY DESC(?links) LIMIT 150' \
  -o database/data/wikidata-candidates.json

php database/scripts/import-top-series.php        # fetch summaries + posters
php database/scripts/enhance-series-posters.php   # embed lead artwork into cards
php artisan db:seed --class=SeriesCatalogSeeder   # upsert into media
```

TMDB/TVmaze/IMDb are filtered on this network, which is why the importer uses Wikidata + Wikipedia and stores every image locally.

## Admin panel (Filament 5)

The site is managed through a Filament 5 panel at **http://localhost:8787/admin**, using the same `users` table. Only `moderator`, `admin` and `super_admin` roles can sign in (`User::canAccessPanel()`); sign in with the seeded `admin@watchkru.test` / `password`.

What it includes:

- **Every model has a resource**: media titles, genres, platforms, community categories, communities, reviews, user reports, submissions, users, favorites and audit logs. Lists have search, sortable columns, filters, bulk actions and soft-delete restore.
- **Poster & backdrop fields**: the media form has a dedicated «پوستر و تصویر پس‌زمینه» section with a «لینک پوستر» field accepting either a full `https://` URL or a local path such as `/images/posters/the-100.jpg`, plus a backdrop link used by the title page hero (falls back to the blurred poster when empty).
- **Chat moderation**: «گروه‌های گفت‌وگو» lists every group with status/member/message counts, approve & reject actions (rejection sends the creator a reason) and «تأیید حذف» for deletion requests. «پیام‌های تماس» shows contact-form submissions with read/unread state.
- **Moderation workflows**: approve/verify/reject a community, resolve or dismiss a report, approve or reject a submission — all from the row actions.
- **Dashboard widgets**: headline stats with sparklines plus doughnut, pie, line and bar charts (media mix, verification status, content growth, rating distribution, largest communities).
- **Notification system**: a database-notification bell in the topbar. New community submissions and user reports notify every staff member (`App\Support\AdminNotifier` → `App\Notifications\AdminAlert`), with a direct link and mark-as-read action.
- **Global search** across titles, communities, users, genres and platforms from the topbar.
- **RTL Persian UI**, responsive down to phone widths (collapsible sidebar, card-style tables).

### PHP extensions required by Filament

Filament needs PHP's `intl` and `zip` extensions, and Composer refuses to resolve the Filament packages without them. XAMPP ships both but leaves them commented out; they are now enabled in `F:\xampp\php\php.ini`:

```ini
extension=intl
extension=zip
```

Restart any running PHP process (and `php artisan serve`) after changing `php.ini`. If you ever need to run on a machine where you cannot edit it, load them per-invocation instead:

```bash
cd public && php -d extension=intl -d extension=zip \
  -S 127.0.0.1:8787 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
```

(`php artisan serve` spawns its own PHP process, so `-d` flags passed to the artisan command do not reach it.)

### Running tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`) and cover: registration/login/me, authenticated-only submission, duplicate-URL rejection, invalid URL-scheme rejection, admin approve/reject (and that a regular user is blocked), one-review-per-user-per-community, rating bounds, report creation + admin resolution, and search/filtering.

## API contract

Base path: `/api/v1`. Every response is shaped `{ success, message, data, meta? }` on success or `{ success: false, message, errors }` on failure, matching the brief's example contracts exactly.

```
POST   /auth/register               POST /auth/login          POST /auth/logout (auth)
GET    /auth/me (auth)              POST /auth/forgot-password  POST /auth/reset-password

GET    /media                       GET /movies   GET /series   GET /media/{slug}
GET    /genres                      GET /platforms               GET /community-categories
GET    /search?q=

GET    /search?q=                   (Persian/English, substring, normalised letters)
POST   /contact                     (public, throttled)
GET    /chat/groups                 POST /chat/groups            (auth)
GET    /chat/groups/{id}/messages   POST /chat/groups/{id}/messages
POST   /chat/groups/{id}/read       POST /chat/groups/{id}/deletion-request
GET    /user/notifications          POST /user/notifications/read

GET    /communities                 GET /communities/{slug}
POST   /communities (auth)                                  — submit, duplicate-checked
POST   /communities/{id}/report (auth)
POST|PUT|DELETE /communities/{id}/reviews (auth)

POST   /favorites (auth)            DELETE /favorites (auth)

GET|PUT /user/profile (auth)        GET /user/submissions (auth)
GET    /user/reports (auth)         GET /user/favorites (auth)

# role: admin | super_admin | moderator
GET    /admin/dashboard
GET    /admin/communities           PUT /admin/communities/{id}
POST   /admin/communities/{id}/approve | reject | verify | inactive | needs-review
DELETE /admin/communities/{id}
GET    /admin/reports               PUT /admin/reports/{id}
```

## Database

See `database/migrations/` — tables follow the brief's Section 12 schema almost exactly: `users`, `media` (movies + series in one table, discriminated by `media_type`), `genres` + `genre_media`, `platforms`, `community_categories`, `communities`, `community_media` (many-to-many — a community can relate to more than one title), `community_submissions` (raw submitted payload kept for audit, decoupled from the live `communities` row), `community_reports`, `community_reviews` (unique per user+community), `favorites` (polymorphic, Media or Community), `audit_logs`.

All foreign keys, a unique constraint on `(community_id, user_id)` reviews and on normalized submission URLs' lookup index, and indexes on the columns actually filtered/sorted on (`media_type`, `release_year`, `verification_status`, `activity_status`) are in place. Full-text indexes on `media`/`communities` are added only on MySQL (SQLite, used for the test suite, has no FULLTEXT support) — the search controller itself uses portable `LIKE` queries so it works identically on both.

## SEO note (brief Section 13)

This backend is API-only; the frontend is a Vite/React SPA. For the MVP, the practical choice — documented rather than silently assumed — is:

1. Ship API responses with everything a `<head>` needs (title-worthy fields, `overview` for descriptions, canonical slugs) so the frontend can set `document.title` and meta tags per-route client-side.
2. Before launch, add a thin server-rendering layer for public, crawlable routes only (`/`, `/explore`, `/media/{slug}`, `/communities/{slug}`) — either Vite SSR or a prerendering step (e.g., `vite-plugin-ssr`/`astro` bridge, or a prerender-on-deploy script) — so search engines receive real HTML rather than an empty `<div id="root">`. This is explicitly **not** built in this MVP pass; it's the first item in "Known limitations" below.
3. `robots.txt` should disallow `/dashboard`, `/admin`, `/login`, `/register`, `/submit-community` once the frontend ships them, since those are private/user pages.

## Known limitations (MVP scope)

- **No SSR/prerendering yet** — see the SEO note above. Without it, public pages are not reliably crawlable.
- **No image optimization/CDN pipeline** — `poster_path`/`backdrop_path` are stored as plain URLs; there's no upload/resize flow for admin-added media art yet.
- **No email verification enforcement** — the column and route exist, but no route currently requires `email_verified_at` to be set.
- **No rate-limit-abuse analytics / CAPTCHA** on submission and report endpoints beyond Laravel's built-in `throttle` middleware.
- **No Swagger/OpenAPI file generated** — the contract above is the documentation for now; wiring `darkaonline/l5-swagger` (or similar) is a good next task.
- **Slugs for Persian-named communities** fall back to a random suffix when `Str::slug()` produces an empty string from non-Latin text (expected — Laravel's slugger is ASCII-only). This doesn't block anything but means community URLs are not always human-legible; a transliteration library would improve this later.
- **TMDB integration is scaffolded, not implemented** — the config flag and nullable `external_*` columns exist; no import job has been written. Per the brief, the app is fully usable without it.
- **Phases 2–4** (reviews ✅ already partly in, but full engagement/recommendations, discussion boards with spoiler protection, and watch-together) are intentionally not built — see the brief's own phase breakdown.

## Next logical phase

Phase 2 engagement features that build directly on what's here: personalized recommendations (using the existing `genres`/`favorites` data), activity-status automation (a scheduled job that periodically re-checks `external_url` reachability and updates `last_checked_at`/`activity_status` — currently both are admin-set only), and the SSR/prerendering pass above.
