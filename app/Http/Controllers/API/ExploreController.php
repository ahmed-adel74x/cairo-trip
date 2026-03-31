<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExploreResource;
use App\Models\Place;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/explore
    // All active places + user favourite/booked status
    // ──────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $places = $this->getPlacesWithUserStatus($request);

        return $this->successResponse(
            ExploreResource::collection($places),
            'explore_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/explore/search?q=xxx
    // Search by name or location (AR or EN)
    // ──────────────────────────────────────────────────
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return $this->successResponse(
                [],
                'no_results',
                200
            );
        }

        $places = Place::active()
            ->where(function ($q) use ($query) {
                $q->where('name_ar',     'LIKE', "%{$query}%")
                  ->orWhere('name_en',     'LIKE', "%{$query}%")
                  ->orWhere('location_ar', 'LIKE', "%{$query}%")
                  ->orWhere('location_en', 'LIKE', "%{$query}%")
                  ->orWhere('description_ar', 'LIKE', "%{$query}%")
                  ->orWhere('description_en', 'LIKE', "%{$query}%");
            })
            ->get();

        $places = $this->attachUserStatus($request, $places);

        return $this->successResponse(
            ExploreResource::collection($places),
            'explore_search_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/explore/filter?is_free=true&location=الجيزة
    // Filter by is_free and/or location
    // ──────────────────────────────────────────────────
    public function filter(Request $request): JsonResponse
    {
        $query = Place::active();

        // Filter by is_free
        if ($request->has('is_free')) {
            $isFree = filter_var($request->get('is_free'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_free', $isFree);
        }

        // Filter by location (AR or EN)
        if ($request->has('location') && !empty($request->get('location'))) {
            $location = $request->get('location');
            $query->where(function ($q) use ($location) {
                $q->where('location_ar', 'LIKE', "%{$location}%")
                  ->orWhere('location_en', 'LIKE', "%{$location}%");
            });
        }

        $places = $query->get();
        $places = $this->attachUserStatus($request, $places);

        return $this->successResponse(
            ExploreResource::collection($places),
            'explore_filter_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/explore/{id}
    // Single place with full details + user status
    // ──────────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $place = Place::active()->find($id);

        if (!$place) {
            return $this->errorResponse('place_not_found', 404);
        }

        $user = $request->user();

        // Check if favourite
        $place->is_favourite = $user->favourites()
            ->where('place_id', $id)
            ->exists();

        // Check if booked (pending or confirmed)
        $place->is_booked = $user->bookings()
            ->where('place_id', $id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        return $this->successResponse(
            new ExploreResource($place),
            'explore_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────

    /**
     * Get all active places with user-specific status attached
     */
    private function getPlacesWithUserStatus(Request $request)
    {
        $places = Place::active()->orderBy('id')->get();
        return $this->attachUserStatus($request, $places);
    }

    /**
     * Attach is_favourite and is_booked to each place
     */
    private function attachUserStatus(Request $request, $places)
    {
        $user = $request->user();

        // Get all user's favourite place IDs in one query
        $favouritePlaceIds = $user->favourites()
            ->pluck('place_id')
            ->toArray();

        // Get all user's active booked place IDs in one query
        $bookedPlaceIds = $user->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('place_id')
            ->toArray();

        // Attach status to each place
        $places->each(function ($place) use ($favouritePlaceIds, $bookedPlaceIds) {
            $place->is_favourite = in_array($place->id, $favouritePlaceIds);
            $place->is_booked    = in_array($place->id, $bookedPlaceIds);
        });

        return $places;
    }
}