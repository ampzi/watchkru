<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5, enforced in FormRequest
            $table->text('content');
            $table->timestamps();

            $table->unique(['community_id', 'user_id']); // one review per user per community
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_reviews');
    }
};
