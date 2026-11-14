<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_title')->nullable();
            // Persian translations / alternate spellings for search.
            $table->json('alternative_titles')->nullable();
            $table->string('slug')->unique();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->enum('media_type', ['movie', 'series'])->index();
            $table->date('release_date')->nullable();
            $table->unsignedSmallInteger('release_year')->nullable()->index();
            $table->string('language', 10)->default('fa');
            // movie: released/upcoming. series: ongoing/ended/upcoming.
            $table->string('status')->nullable();
            $table->unsignedSmallInteger('runtime')->nullable();
            $table->unsignedSmallInteger('number_of_seasons')->nullable();
            $table->unsignedSmallInteger('number_of_episodes')->nullable();
            $table->string('director')->nullable();
            $table->json('cast')->nullable();
            // Reserved for a future external metadata provider (e.g. TMDB).
            // The app must work fully with these columns empty.
            $table->string('external_provider')->nullable();
            $table->string('external_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['external_provider', 'external_id']);
            $table->index(['media_type', 'release_year']);
        });

        // Full-text index added conditionally: SQLite (used for tests) has
        // no FULLTEXT support, and the search controller currently uses
        // simple LIKE matching anyway, so this is an optional production
        // optimization rather than a hard requirement.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('media', function (Blueprint $table) {
                $table->fullText(['title', 'original_title', 'overview']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
