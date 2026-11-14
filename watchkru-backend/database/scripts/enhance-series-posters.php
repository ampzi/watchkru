<?php

/**
 * Second pass over database/data/top-series.json: for entries that fell back
 * to a generated poster card, try to embed the show's actual Wikipedia lead
 * artwork (logo/title card) into the card as a real image above the title.
 *
 * The artwork is embedded as a base64 data URI inside the SVG because an SVG
 * loaded through <img> cannot reference external files.
 *
 * Run: php database/scripts/enhance-series-posters.php
 */

$backend = dirname(__DIR__, 2);
$frontend = dirname($backend).'/watchkru-frontend';
$backendPosters = $backend.'/public/images/posters/series';
$frontendPosters = $frontend.'/public/images/posters/series';
$jsonPath = $backend.'/database/data/top-series.json';

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

function xml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

$payload = json_decode((string) file_get_contents($jsonPath), true);
$items = $payload['items'] ?? [];
$updated = 0;

foreach ($items as &$item) {
    if (! str_ends_with($item['poster_path'], '.svg')) {
        continue;
    }

    $lookup = $item['source_title'] ?? $item['title'];
    $summaryUrl = 'https://en.wikipedia.org/api/rest_v1/page/summary/'.rawurlencode(str_replace(' ', '_', $lookup));
    $raw = http_get($summaryUrl, 30);
    $summary = $raw ? json_decode($raw, true) : null;
    $thumb = $summary['thumbnail']['source'] ?? null;

    if (! $thumb) {
        continue;
    }

    $thumbWidth = (int) ($summary['thumbnail']['width'] ?? 0);
    $bigger = $thumbWidth > 0 ? str_replace('/'.$thumbWidth.'px-', '/640px-', $thumb) : $thumb;
    $image = http_get($bigger, 40) ?? http_get($thumb, 40);

    if (! $image || strlen($image) < 3000) {
        continue;
    }

    $dataUri = 'data:image/'.(str_contains($thumb, '.svg') ? 'svg+xml' : 'jpeg').';base64,'.base64_encode($image);

    $title = xml($item['title']);
    $year = $item['release_year'] ? xml((string) $item['release_year']) : '';

    $perfs = '';
    for ($i = 0; $i < 12; $i++) {
        $perfs .= '<rect x="36" y="'.(60 + $i * 70).'" width="18" height="34" rx="4" fill="#0d0f14" opacity="0.55"/>';
        $perfs .= '<rect x="546" y="'.(60 + $i * 70).'" width="18" height="34" rx="4" fill="#0d0f14" opacity="0.55"/>';
    }

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="900" viewBox="0 0 600 900" role="img" aria-label="{$title}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#191b23"/>
      <stop offset="0.55" stop-color="#101116"/>
      <stop offset="1" stop-color="#222530"/>
    </linearGradient>
    <radialGradient id="glow" cx="0.7" cy="0.15" r="0.75">
      <stop offset="0" stop-color="#e6b85c" stop-opacity="0.2"/>
      <stop offset="1" stop-color="#e6b85c" stop-opacity="0"/>
    </radialGradient>
    <clipPath id="art"><rect x="60" y="150" width="480" height="380" rx="10"/></clipPath>
  </defs>
  <rect width="600" height="900" fill="url(#bg)"/>
  <rect width="600" height="900" fill="url(#glow)"/>
  <rect x="30" y="30" width="540" height="840" fill="none" stroke="#303340" stroke-width="2" rx="14"/>
  {$perfs}
  <image href="{$dataUri}" x="60" y="150" width="480" height="380" preserveAspectRatio="xMidYMid meet" clip-path="url(#art)" opacity="0.92"/>
  <text x="300" y="120" text-anchor="middle" fill="#77c8b5" font-family="Peyda, Vazirmatn, sans-serif" font-size="24">سریال</text>
  <line x1="210" y1="590" x2="390" y2="590" stroke="#e6b85c" stroke-width="3"/>
  <text x="300" y="680" text-anchor="middle" fill="#f4f2ee" font-family="Peyda, Vazirmatn, sans-serif" font-size="46" font-weight="700">{$title}</text>
  <text x="300" y="740" text-anchor="middle" fill="#a5a7b2" font-family="Peyda, Vazirmatn, sans-serif" font-size="28">{$year}</text>
  <text x="300" y="820" text-anchor="middle" fill="#a5a7b2" font-family="Peyda, Vazirmatn, sans-serif" font-size="20">WatchKru</text>
</svg>
SVG;

    $file = basename($item['poster_path']);
    file_put_contents($backendPosters.'/'.$file, $svg);
    @copy($backendPosters.'/'.$file, $frontendPosters.'/'.$file);

    $updated++;
    fwrite(STDERR, "artwork embedded: {$item['title']}\n");
    usleep(120000);
}

file_put_contents($jsonPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

fwrite(STDERR, "Updated {$updated} cards with real artwork.\n");
