# WatchKru — static site for GitHub Pages

This folder is a **complete, self-contained static build** of the WatchKru frontend.
GitHub Pages can only serve static files (no PHP, no MySQL), so this version carries
its data with it:

- `assets/` — the compiled React app
- `data/catalog.json` — a snapshot of the catalogue exported from MySQL
  (117 titles, 11 communities, genres, chat demo, demo users)
- `images/`, `fonts/`, `icons.svg` — posters and the Peyda typeface
- `404.html` — sends unknown paths back to the app

You can host this folder anywhere static (GitHub Pages, Netlify, Cloudflare Pages, …).

## What works

| Feature | Behaviour on Pages |
| --- | --- |
| Home, Explore, genres, media details | Full catalogue, live client-side filtering and pagination |
| Search (Persian/English, any starting character) | Same normalisation rules as the API, running in the browser |
| Communities list/details | Real data from the export |
| Login/register | Demo only: any exported e-mail + password `password`, stored in `localStorage` |
| Chat, groups, notifications | Demo only: seeded from the export, new messages stored in `localStorage` |
| Contact form | Shows the success state; nothing leaves the browser |
| Admin tabs (overview counters) | Read-only counters from the export |

No server is involved, so data is per-browser and resets when you clear storage.

## Rebuilding this folder

From the repository root:

```bash
# 1. Fresh copy of the catalogue from MySQL
cd watchkru-backend
php artisan watchkru:export-static --path=../github

# 2. Compile the frontend in static mode into this folder
cd ../watchkru-frontend
npm install
npm run build:static       # vite build --mode static --outDir ../github --emptyOutDir

# 3. Re-export after the build, because --emptyOutDir clears data/
cd ../watchkru-backend
php artisan watchkru:export-static --path=../github
```

Then commit the changes in this folder.

## Publishing on GitHub Pages

The site uses hash routing (`/#/explore`), so no server rewrites are needed and the
same build works at any URL, root or sub-path.

1. Create a repository on GitHub (public — Pages on a private repo needs a paid plan).
2. Push this project, then point Pages at the `github/` folder:
   **Settings → Pages → Source: Deploy from a branch → Branch: `main` → Folder: `/github`**.
3. Wait for the first deployment; the site appears at
   `https://<username>.github.io/<repo>/` (or `https://<username>.github.io/` when the
   repository itself is named `<username>.github.io`).

The build uses relative asset URLs (`base: './'`), so it does not need to know the
repository name in advance.

## Pointing it at a live API instead

If you later host the Laravel backend (any PHP host), rebuild with
`VITE_API_URL=https://api.example.com/api/v1` and drop `VITE_STATIC`, and the same UI
will talk to the real API, including real accounts and chat.
