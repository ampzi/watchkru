<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A community can relate to more than one title (e.g. a general
        // "sci-fi series" Discord), so this is a true many-to-many pivot.
        Schema::create('community_media', function (Blueprint $table) {
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['community_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_media');
    }
};
