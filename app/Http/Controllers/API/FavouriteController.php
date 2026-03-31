<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavouriteResource;
use App\Models\Favourite;
use App\Models\Place;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/favourites
    // Get all favourites for authenticated user
    // ──────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $favourites = Favourite::with('place')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse(
            FavouriteResource::collection($favourites),
            'favourites_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // POST /api/favourites/{place_id}/toggle
    // Add or remove from favourites
    // ──────────────────────────────────────────────────
    public function toggle(Request $request, int $placeId): JsonResponse
    {
        $user = $request->user();

        // Check place exists
        $place = Place::active()->find($placeId);
        if (!$place) {
            return $this->errorResponse('place_not_found', 404);
        }

        // Check if already favourite
        $favourite = Favourite::where('user_id', $user->id)
            ->where('place_id', $placeId)
            ->first();

        if ($favourite) {
            // Remove from favourites
            $favourite->delete();

            // Decrement user favourites_count
            if ($user->favourites_count > 0) {
                $user->decrement('favourites_count');
            }

            return $this->successResponse(
                [
                    'place_id'     => $placeId,
                    'is_favourite' => false,
                ],
                'favourite_removed',
                200
            );
        }

        // Add to favourites
        Favourite::create([
            'user_id'  => $user->id,
            'place_id' => $placeId,
        ]);

        // Increment user favourites_count
        $user->increment('favourites_count');

        return $this->successResponse(
            [
                'place_id'     => $placeId,
                'is_favourite' => true,
            ],
            'favourite_added',
            200
        );
    }
}