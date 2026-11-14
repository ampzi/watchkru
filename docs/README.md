# WatchKru — static site for GitHub Pages

This folder is a **complete, self-contained static build** of the WatchKru frontend.
GitHub Pages can only serve static files (no PHP, no MySQL), so this version carries
its data with it:

- `assets/` — the compiled React app
- `data/catalog.json` — a snapshot of the catalogue exported from MySQL
  (117 titles, 11 communities, genres, chat demo, demo users)
- `images/`, `fonts/`, `icons.svg` — posters and the Peyda typeface
- `404.html`, `.nojekyll` — Pages plumbing for a single-page app

## What works

| Feature | Behaviour on Pages |
| --- | --- |
| Home, Explore, genres, media details | Full catalogue, client-side filtering and pagination |
| Search (Persian/English, any starting character) | Same normalisation rules as the API, in the browser |
| Communities list/details | Real data from the export |
| Login/register | Demo only: any exported e-mail + password `password`, kept in `localStorage` |
| Chat, groups, notifications | Demo only: seeded from the export, new messages in `localStorage` |
| Contact form | Shows the success state; nothing leaves the browser |
| Admin overview counters | Read-only counters from the export |

No server is involved, so data is per-browser and resets when you clear storage.

## Rebuilding

```bash
# 1. Fresh copy of the catalogue from MySQL
cd watchkru-backend
php artisan watchkru:export-static --path=../docs

# 2. Compile the frontend in static mode into this folder
cd ../watchkru-frontend
npm run build:static        # vite build --mode static --outDir ../docs --emptyOutDir

# 3. Re-export after the build, because --emptyOutDir clears data/
cd ../watchkru-backend
php artisan watchkru:export-static --path=../docs
```

## Publishing

The app uses hash routing (`/#/explore`), so no server rewrites are needed and the build
works at any URL.

**Settings → Pages → Source: Deploy from a branch → Branch `main`, Folder `/docs`.**

GitHub only offers the repository root or `/docs` for branch deployments, which is why the
build lives here. The site is published at `https://ampzi.github.io/watchkru/`.

## Pointing it at a live API instead

Host the Laravel backend anywhere that runs PHP, then rebuild with
`VITE_API_URL=https://api.example.com/api/v1` and without `VITE_STATIC`: the same UI talks
to the real API, with real accounts and chat.
