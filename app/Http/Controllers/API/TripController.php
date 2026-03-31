<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use App\Models\Trip;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/trips
    // All trips for authenticated user
    // ──────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $trips = Trip::with(['place', 'rating'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('trip_date')
            ->get();

        return $this->successResponse(
            TripResource::collection($trips),
            'trips_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/trips/completed
    // Only completed trips
    // ──────────────────────────────────────────────────
    public function completed(Request $request): JsonResponse
    {
        $trips = Trip::with(['place', 'rating'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->orderByDesc('trip_date')
            ->get();

        return $this->successResponse(
            TripResource::collection($trips),
            'trips_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/trips/upcoming
    // Only upcoming trips
    // ──────────────────────────────────────────────────
    public function upcoming(Request $request): JsonResponse
    {
        $trips = Trip::with(['place', 'rating'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'upcoming')
            ->orderBy('trip_date')
            ->get();

        return $this->successResponse(
            TripResource::collection($trips),
            'trips_fetched',
            200
        );
    }
}