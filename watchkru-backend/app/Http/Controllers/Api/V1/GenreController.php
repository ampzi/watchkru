<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;

class GenreController extends Controller
{
    use ApiResponses;

    public function index()
    {
        return $this->ok(GenreResource::collection(Genre::orderBy('name')->get()));
    }
}
