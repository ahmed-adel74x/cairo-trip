<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class PlaceController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/places
    // Public - no auth required
    // ──────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $places = Place::active()
            ->orderBy('id')
            ->get();

        return $this->successResponse(
            PlaceResource::collection($places),
            'places_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/places/{id}
    // Public - no auth required
    // ──────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $place = Place::active()->find($id);

        if (!$place) {
            return $this->errorResponse('place_not_found', 404);
        }

        return $this->successResponse(
            new PlaceResource($place),
            'place_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/places/{id}/details
    // Protected - requires auth
    // ──────────────────────────────────────────────────
    public function details(int $id): JsonResponse
    {
        return $this->show($id);
    }
}