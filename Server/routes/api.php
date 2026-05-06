<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\MatchPhotoController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public auth routes
|--------------------------------------------------------------------------
| Rate-limited heavily because they're the obvious target for brute-force
| attempts. Sanctum's auth:sanctum guard takes over for everything below.
*/
Route::middleware('throttle:auth')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Identity
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // CRUD resources
    Route::apiResource('teams',   TeamController::class);
    Route::apiResource('venues',  VenueController::class);
    Route::apiResource('matches', MatchController::class);

    // Photos are nested under a match — they don't make sense on their own.
    Route::get('/matches/{match}/photos',          [MatchPhotoController::class, 'index']);
    Route::post('/matches/{match}/photos',         [MatchPhotoController::class, 'store']);
    Route::delete('/matches/{match}/photos/{photo}', [MatchPhotoController::class, 'destroy']);

    // Aggregates for the dashboard
    Route::get('/stats/summary', [StatsController::class, 'summary']);
});
