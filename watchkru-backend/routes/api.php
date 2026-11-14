<?php

use App\Http\Controllers\Api\V1\Admin\CommunityModerationController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\CommunityCategoryController;
use App\Http\Controllers\Api\V1\CommunityController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\PlatformController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // --- Authentication -----------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])
            ->middleware('throttle:6,1');
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
            ->middleware('throttle:5,1');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])
            ->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // --- Public catalog --------------------------------------------------
    Route::get('media', [MediaController::class, 'index']);
    Route::get('movies', [MediaController::class, 'movies']);
    Route::get('series', [MediaController::class, 'series']);
    Route::get('media/{media:slug}', [MediaController::class, 'show']);

    Route::get('genres', [GenreController::class, 'index']);
    Route::get('platforms', [PlatformController::class, 'index']);
    Route::get('community-categories', [CommunityCategoryController::class, 'index']);

    Route::get('search', SearchController::class);

    Route::post('contact', [ContactController::class, 'store'])
        ->middleware('throttle:5,1');

    // --- Communities -------------------------------------------------
    Route::get('communities', [CommunityController::class, 'index']);
    Route::get('communities/{community:slug}', [CommunityController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('communities', [CommunityController::class, 'store'])
            ->middleware('throttle:10,1');
        Route::post('communities/{community}/report', [CommunityController::class, 'report'])
            ->middleware('throttle:10,1');
        Route::post('communities/{community}/reviews', [CommunityController::class, 'storeReview']);
        Route::put('communities/{community}/reviews', [CommunityController::class, 'updateReview']);
        Route::delete('communities/{community}/reviews', [CommunityController::class, 'destroyReview']);

        Route::post('favorites', [UserController::class, 'addFavorite']);
        Route::delete('favorites', [UserController::class, 'removeFavorite']);

        // --- In-app chat -------------------------------------------------
        // Group creation and deletion are requests: a moderator approves both
        // in the admin panel before they take effect.
        Route::get('chat/groups', [ChatController::class, 'index']);
        Route::post('chat/groups', [ChatController::class, 'store'])
            ->middleware('throttle:5,1');
        Route::get('chat/groups/{group}/messages', [ChatController::class, 'messages']);
        Route::post('chat/groups/{group}/messages', [ChatController::class, 'send'])
            ->middleware('throttle:40,1');
        Route::post('chat/groups/{group}/read', [ChatController::class, 'markRead']);
        Route::get('chat/groups/{group}/members', [ChatController::class, 'members']);
        Route::post('chat/groups/{group}/deletion-request', [ChatController::class, 'requestDeletion']);

        Route::get('user/notifications', [NotificationController::class, 'index']);
        Route::post('user/notifications/read', [NotificationController::class, 'markAllRead']);
    });

    // --- Authenticated user area --------------------------------------
    Route::middleware('auth:sanctum')->prefix('user')->group(function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::get('submissions', [UserController::class, 'submissions']);
        Route::get('reports', [UserController::class, 'reports']);
        Route::get('favorites', [UserController::class, 'favorites']);
    });

    // --- Admin -----------------------------------------------------------
    Route::middleware(['auth:sanctum', 'role:admin,super_admin,moderator'])
        ->prefix('admin')
        ->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index']);

            Route::get('communities', [CommunityModerationController::class, 'index']);
            Route::put('communities/{community}', [CommunityModerationController::class, 'update']);
            Route::post('communities/{community}/approve', [CommunityModerationController::class, 'approve']);
            Route::post('communities/{community}/reject', [CommunityModerationController::class, 'reject']);
            Route::post('communities/{community}/verify', [CommunityModerationController::class, 'markVerified']);
            Route::post('communities/{community}/inactive', [CommunityModerationController::class, 'markInactive']);
            Route::post('communities/{community}/needs-review', [CommunityModerationController::class, 'markNeedsReview']);
            Route::delete('communities/{community}', [CommunityModerationController::class, 'destroy']);

            Route::get('reports', [AdminReportController::class, 'index']);
            Route::put('reports/{report}', [AdminReportController::class, 'update']);
        });
});
