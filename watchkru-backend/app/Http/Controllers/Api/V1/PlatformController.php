<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Platform;

class PlatformController extends Controller
{
    use ApiResponses;

    public function index()
    {
        return $this->ok(Platform::orderBy('name')->get(['id', 'name', 'slug', 'icon']));
    }
}
