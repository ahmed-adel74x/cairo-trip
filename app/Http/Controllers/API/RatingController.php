<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RatingRequest;
use App\Models\Rating;
use App\Models\Trip;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class RatingController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // POST /api/ratings
    // Rate a completed trip
    // ──────────────────────────────────────────────────
    public function store(RatingRequest $request): JsonResponse
    {
        $user = $request->user();

        // Find the trip
        $trip = Trip::with('place')->find($request->trip_id);

        if (!$trip) {
            return $this->errorResponse('trip_not_found', 404);
        }

        // Make sure trip belongs to this user
        if ($trip->user_id !== $user->id) {
            return $this->errorResponse('rating_not_your_trip', 403);
        }

        // Trip must be completed
        if ($trip->status !== 'completed') {
            return $this->errorResponse('rating_trip_not_valid', 422);
        }

        // Check if already rated
        $existingRating = Rating::where('user_id', $user->id)
            ->where('trip_id', $trip->id)
            ->first();

        if ($existingRating) {
            return $this->errorResponse('rating_exists', 422);
        }

        // Create rating
        $rating = Rating::create([
            'user_id'  => $user->id,
            'place_id' => $trip->place_id,
            'trip_id'  => $trip->id,
            'rating'   => $request->rating,
            'comment'  => $request->comment,
        ]);

        // Update place rating_avg
        $this->updatePlaceRating($trip->place_id);

        return $this->successResponse(
            [
                'rating'  => $rating->rating,
                'label'   => $this->getRatingLabel($rating->rating),
                'comment' => $rating->comment,
            ],
            'rating_created',
            201
        );
    }

    // ──────────────────────────────────────────────────
    // PRIVATE: Recalculate place rating average
    // ──────────────────────────────────────────────────
    private function updatePlaceRating(int $placeId): void
    {
        $avgRating = Rating::where('place_id', $placeId)->avg('rating');

        \App\Models\Place::find($placeId)?->update([
            'rating_avg' => round($avgRating, 2),
        ]);
    }
}