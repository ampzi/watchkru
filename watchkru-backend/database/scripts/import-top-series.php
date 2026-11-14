<?php

/**
 * Imports a best-of TV catalogue using only APIs that are reachable from this
 * machine (TMDB/TVmaze/IMDb are filtered by the local network).
 *
 *  1. database/data/wikidata-candidates.json — Wikidata SPARQL result: TV
 *     series that have a TMDB id, ordered by number of Wikipedia sitelinks,
 *     with their first-air date. (See the fetch command in the README.)
 *  2. Wikipedia REST API — gets the article summary (real description) and its
 *     lead image plus dimensions.
 *  3. Poster handling: if the Wikipedia lead image is portrait (a poster-like
 *     image) it is downloaded locally at 400px wide. Otherwise a typographic
 *     placeholder poster is generated in the project, so no entry ever shows a
 *     broken image and no fake artwork is presented as official.
 *
 * Output: database/data/top-series.json + public/images/posters/series/* in
 * both the backend and the frontend.
 *
 * Run: php database/scripts/import-top-series.php
 */

$backend = dirname(__DIR__, 2);
$frontend = dirname($backend).'/watchkru-frontend';

$dataDir = $backend.'/database/data';
$backendPosters = $backend.'/public/images/posters/series';
$frontendPosters = $frontend.'/public/images/posters/series';

foreach ([$dataDir, $backendPosters, $frontendPosters] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

function http_get(string $url, int $timeout = 40): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_USERAGENT => 'WatchKruCatalogImporter/1.0 (hello@watchkru.test)',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($body !== false && $code >= 200 && $code < 300) ? $body : null;
}

function slugify(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'series-'.substr(md5($title), 0, 8);
}

function xml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

/**
 * Typographic placeholder poster (600×900) used when no portrait artwork is
 * available: brand colours, a film-strip motif and the real title/year.
 */
function placeholder_svg(string $title, ?int $year, string $kind): string
{
    $title = xml($title);
    $yearLabel = $year ? xml((string) $year) : '';
    $kindLabel = $kind === 'series' ? 'سریال' : 'فیلم';

    $perfs = '';
    for ($i = 0; $i < 12; $i++) {
        $perfs .= '<rect x="36" y="'.(60 + $i * 70).'" width="18" height="34" rx="4" fill="#0d0f14" opacity="0.55"/>';
        $perfs .= '<rect x="546" y="'.(60 + $i * 70).'" width="18" height="34" rx="4" fill="#0d0f14" opacity="0.55"/>';
    }

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="900" viewBox="0 0 600 900" role="img" aria-label="{$title}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#191b23"/>
      <stop offset="0.55" stop-color="#101116"/>
      <stop offset="1" stop-color="#222530"/>
    </linearGradient>
    <radialGradient id="glow" cx="0.7" cy="0.15" r="0.75">
      <stop offset="0" stop-color="#e6b85c" stop-opacity="0.22"/>
      <stop offset="1" stop-color="#e6b85c" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="600" height="900" fill="url(#bg)"/>
  <rect width="600" height="900" fill="url(#glow)"/>
  <rect x="30" y="30" width="540" height="840" fill="none" stroke="#303340" stroke-width="2" rx="14"/>
  {$perfs}
  <text x="300" y="120" text-anchor="middle" fill="#77c8b5" font-family="Peyda, Vazirmatn, sans-serif" font-size="24">{$kindLabel}</text>
  <text x="300" y="470" text-anchor="middle" fill="#f4f2ee" font-family="Peyda, Vazirmatn, sans-serif" font-size="54" font-weight="700">{$title}</text>
  <line x1="210" y1="520" x2="390" y2="520" stroke="#e6b85c" stroke-width="3"/>
  <text x="300" y="580" text-anchor="middle" fill="#a5a7b2" font-family="Peyda, Vazirmatn, sans-serif" font-size="30">{$yearLabel}</text>
  <text x="300" y="820" text-anchor="middle" fill="#a5a7b2" font-family="Peyda, Vazirmatn, sans-serif" font-size="20">WatchKru</text>
</svg>
SVG;
}

$existing = ['the-100', 'silo', 'the-bear', 'true-detective'];

$candidatesPath = $dataDir.'/wikidata-candidates.json';

if (! is_file($candidatesPath)) {
    fwrite(STDERR, "Missing {$candidatesPath} — fetch the Wikidata candidates first.\n");
    exit(1);
}

$rows = json_decode((string) file_get_contents($candidatesPath), true)['results']['bindings'] ?? [];
fwrite(STDERR, 'Wikidata candidates: '.count($rows)." rows.\n");

$catalogue = [];
$usedSlugs = [];
$seenTmdb = [];

// Keep anything already imported (the script can be re-run to top up).
$existingJson = $dataDir.'/top-series.json';
if (is_file($existingJson)) {
    foreach (json_decode((string) file_get_contents($existingJson), true)['items'] ?? [] as $old) {
        $catalogue[] = $old;
        $usedSlugs[$old['slug']] = true;
        if (! empty($old['external_id'])) {
            $seenTmdb[(string) $old['external_id']] = true;
        }
    }
    fwrite(STDERR, 'Already imported: '.count($catalogue)."\n");
}

foreach ($rows as $row) {
    if (count($catalogue) >= 110) {
        break;
    }

    $title = $row['itemLabel']['value'] ?? '';
    $tmdb = $row['tmdb']['value'] ?? '';
    $start = $row['start']['value'] ?? $row['date']['value'] ?? null;
    $year = $start ? (int) substr($start, 0, 4) : null;

    if ($title === '' || $tmdb === '' || preg_match('/^Q\d+$/', $title) || isset($seenTmdb[$tmdb])) {
        continue;
    }

    $seenTmdb[$tmdb] = true;

    $slug = slugify($title);
    if (in_array($slug, $existing, true)) {
        continue;
    }

    $n = 2;
    $base = $slug;
    while (isset($usedSlugs[$slug])) {
        $slug = $base.'-'.$n;
        $n++;
    }

    $summaryUrl = 'https://en.wikipedia.org/api/rest_v1/page/summary/'.rawurlencode(str_replace(' ', '_', $title));
    $raw = http_get($summaryUrl, 30);
    $summary = $raw ? json_decode($raw, true) : null;

    // A summary for a disambiguation page is not useful; skip it.
    if (! $summary || ($summary['type'] ?? '') === 'disambiguation') {
        fwrite(STDERR, "skip (no summary): {$title}\n");
        usleep(150000);
        continue;
    }

    $extract = isset($summary['extract']) ? mb_substr($summary['extract'], 0, 900) : null;
    $thumb = $summary['thumbnail']['source'] ?? null;
    $thumbWidth = (int) ($summary['thumbnail']['width'] ?? 0);
    $thumbHeight = (int) ($summary['thumbnail']['height'] ?? 0);

    $filename = $slug.'.jpg';
    $stored = false;

    // Only use lead images that are poster-shaped (portrait). Landscape logos
    // and title cards would be cropped into unreadable shapes on a 2:3 card.
    if ($thumb && $thumbWidth > 0 && $thumbHeight >= $thumbWidth * 1.15) {
        $bigger = str_replace('/'.$thumbWidth.'px-', '/500px-', $thumb);
        $image = http_get($bigger, 40) ?? http_get($thumb, 40);

        if ($image && strlen($image) > 3000) {
            file_put_contents($backendPosters.'/'.$filename, $image);
            @copy($backendPosters.'/'.$filename, $frontendPosters.'/'.$filename);
            $stored = true;
        }
    }

    if (! $stored) {
        $filename = $slug.'.svg';
        $svg = placeholder_svg($title, $year, 'series');
        file_put_contents($backendPosters.'/'.$filename, $svg);
        @copy($backendPosters.'/'.$filename, $frontendPosters.'/'.$filename);
    }

    $catalogue[] = [
        'slug' => $slug,
        'title' => $title,
        'release_year' => $year,
        'overview' => $extract,
        'poster_path' => '/images/posters/series/'.$filename,
        'external_provider' => 'wikidata',
        'external_id' => $tmdb,
        'source_title' => $summary['title'] ?? null,
    ];
    $usedSlugs[$slug] = true;

    fwrite(STDERR, '['.count($catalogue)."] {$title} ({$year}) — ".($stored ? 'artwork' : 'placeholder')."\n");
    usleep(150000);
}

file_put_contents(
    $dataDir.'/top-series.json',
    json_encode([
        'generated_at' => date(DATE_ATOM),
        'source' => 'Wikidata SPARQL (P4983, ranked by sitelinks) + Wikipedia REST summaries; posters downloaded locally or generated as typographic cards',
        'count' => count($catalogue),
        'items' => $catalogue,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

fwrite(STDERR, 'Done: '.count($catalogue)." series imported.\n");
