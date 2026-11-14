<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('external_url');
            // Normalized form of external_url (host + path, no query/scheme/
            // www) used for duplicate detection — see Community::normalizeUrl().
            $table->string('normalized_url')->index();
            $table->foreignId('platform_id')->constrained();
            $table->foreignId('community_category_id')->constrained();
            $table->string('language', 10)->default('fa');
            $table->unsignedInteger('member_count')->nullable();
            $table->timestamp('member_count_verified_at')->nullable();
            $table->enum('activity_status', ['active', 'inactive', 'unknown'])
                ->default('unknown')->index();
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'needs_review'])
                ->default('pending')->index();
            $table->timestamp('last_checked_at')->nullable();
            $table->string('submission_source')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('communities', function (Blueprint $table) {
                $table->fullText(['name', 'description']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
