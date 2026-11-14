<?php

use Illuminate\Support\Facades\Route;

// The public site is rendered by the separate React/Vite frontend; this
// backend is API-only. See the README's SEO section for how public pages
// still get crawlable metadata.
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'WatchKru API. See /api/v1 and the project README.',
    ]);
});
