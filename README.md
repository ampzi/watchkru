# WatchKru

A Persian (RTL) movie & TV catalogue: find the right community — Telegram channels,
Discord servers, Instagram pages — for any film or series.

- **`watchkru-backend/`** — Laravel 13 REST API + Filament 5 admin panel (MySQL/MariaDB)
- **`watchkru-frontend/`** — React 19 + Vite + Tailwind 4 storefront (Persian RTL, Peyda font, dark/light)
- **`github/`** — self-contained static build of the storefront, published on GitHub Pages

## Live site

https://ampzi.github.io/watchkru/ — static build (catalogue bundled as JSON, demo login and
chat stored in the browser). See [`github/README.md`](github/README.md) for details.

## Try it locally

```bash
# backend (API + admin on http://127.0.0.1:8787)
cd watchkru-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8787

# frontend (http://localhost:1258)
cd ../watchkru-frontend
npm install
npm run dev
```

Demo accounts (password `password`): `admin@watchkru.test` (super admin),
`moderator@watchkru.test`, members `sara@`, `amir@`, `mina@`, `reza@`, `hossein@`.

## Rebuilding the static site

GitHub Pages serves the `github/` folder from the `main` branch. After changing content:

```bash
cd watchkru-backend && php artisan watchkru:export-static --path=../github
cd ../watchkru-frontend && npm run build:static
cd ../watchkru-backend && php artisan watchkru:export-static --path=../github
cd .. && git add -A && git commit -m "Rebuild static site" && git push
```

The export runs twice on purpose: the Vite build empties `github/` before writing,
which removes the JSON snapshot.

## Pages setup (once)

**Settings → Pages → Source: Deploy from a branch → Branch `main`, Folder `/github`.**
