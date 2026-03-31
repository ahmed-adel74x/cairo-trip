<?php

use Illuminate\Support\Facades\Route;

// ── User Controllers ──────────────────────────────────────
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PlaceController;
use App\Http\Controllers\API\ExploreController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\FavouriteController;
use App\Http\Controllers\API\TripController;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\API\BudgetController;
use App\Http\Controllers\API\SupportController;

// ── Admin Controllers ─────────────────────────────────────
use App\Http\Controllers\API\Admin\AdminDashboardController;
use App\Http\Controllers\API\Admin\AdminPlaceController;
use App\Http\Controllers\API\Admin\AdminBookingController;
use App\Http\Controllers\API\Admin\AdminUserController;
use App\Http\Controllers\API\Admin\AdminSupportController;

// ═══════════════════════════════════════════════════════════
// PUBLIC ROUTES — No authentication required
// ═══════════════════════════════════════════════════════════

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login',    [AuthController::class, 'login']);

Route::get('places',      [PlaceController::class, 'index']);
Route::get('places/{id}', [PlaceController::class, 'show']);

Route::post('support', [SupportController::class, 'store']);

// ═══════════════════════════════════════════════════════════
// PROTECTED ROUTES — Requires Bearer Token
// ═══════════════════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ──────────────────────────────────────────────
    Route::post('auth/logout',  [AuthController::class, 'logout']);
    Route::get('auth/profile',  [AuthController::class, 'profile']);
    Route::put('auth/profile',  [AuthController::class, 'updateProfile']);

    // ── Place Details ─────────────────────────────────────
    Route::get('places/{id}/details', [PlaceController::class, 'details']);

    // ── Explore ───────────────────────────────────────────
    Route::get('explore',        [ExploreController::class, 'index']);
    Route::get('explore/search', [ExploreController::class, 'search']);
    Route::get('explore/filter', [ExploreController::class, 'filter']);
    Route::get('explore/{id}',   [ExploreController::class, 'show']);

    // ── Bookings ──────────────────────────────────────────
    Route::get('bookings',             [BookingController::class, 'index']);
    Route::post('bookings',            [BookingController::class, 'store']);
    Route::put('bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // ── Favourites ────────────────────────────────────────
    Route::get('favourites',                    [FavouriteController::class, 'index']);
    Route::post('favourites/{place_id}/toggle', [FavouriteController::class, 'toggle']);

    // ── Trips ─────────────────────────────────────────────
    Route::get('trips',           [TripController::class, 'index']);
    Route::get('trips/completed', [TripController::class, 'completed']);
    Route::get('trips/upcoming',  [TripController::class, 'upcoming']);

    // ── Ratings ───────────────────────────────────────────
    Route::post('ratings', [RatingController::class, 'store']);

    // ── Budget ────────────────────────────────────────────
    Route::post('budget/calculate',  [BudgetController::class, 'calculate']);
    Route::get('budget/suggestions', [BudgetController::class, 'suggestions']);

    // ═══════════════════════════════════════════════════════
    // ADMIN ROUTES — Requires Admin Role
    // ═══════════════════════════════════════════════════════

    Route::middleware('admin')->group(function () {

        // ── Dashboard ─────────────────────────────────────
        Route::get('admin/dashboard', [AdminDashboardController::class, 'index']);

        // ── Places CRUD ───────────────────────────────────
        Route::get('admin/places',                    [AdminPlaceController::class, 'index']);
        Route::post('admin/places',                   [AdminPlaceController::class, 'store']);
        Route::get('admin/places/{id}',               [AdminPlaceController::class, 'show']);
        Route::put('admin/places/{id}',               [AdminPlaceController::class, 'update']);
        Route::delete('admin/places/{id}',            [AdminPlaceController::class, 'destroy']);
        Route::put('admin/places/{id}/toggle-active', [AdminPlaceController::class, 'toggleActive']);

        // ── Bookings Management ───────────────────────────
        Route::get('admin/bookings',                  [AdminBookingController::class, 'index']);
        Route::put('admin/bookings/{id}/confirm',     [AdminBookingController::class, 'confirm']);
        Route::put('admin/bookings/{id}/cancel',      [AdminBookingController::class, 'cancel']);

        // ── Users Management ──────────────────────────────
        Route::get('admin/users',        [AdminUserController::class, 'index']);
        Route::get('admin/users/{id}',   [AdminUserController::class, 'show']);
        Route::delete('admin/users/{id}',[AdminUserController::class, 'destroy']);

        // ── Support Management ────────────────────────────
        Route::get('admin/support',                  [AdminSupportController::class, 'index']);
        Route::get('admin/support/{id}',             [AdminSupportController::class, 'show']);
        Route::put('admin/support/{id}/status',      [AdminSupportController::class, 'updateStatus']);
        Route::put('admin/support/{id}/reply',       [AdminSupportController::class, 'reply']);

    });
});