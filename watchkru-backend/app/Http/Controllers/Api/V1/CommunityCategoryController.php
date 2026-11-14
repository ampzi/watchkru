<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\CommunityCategory;

class CommunityCategoryController extends Controller
{
    use ApiResponses;

    public function index()
    {
        return $this->ok(CommunityCategory::orderBy('name')->get(['id', 'name', 'slug', 'description']));
    }
}
