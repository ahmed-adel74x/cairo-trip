<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlaceStoreRequest;
use App\Http\Requests\Admin\PlaceUpdateRequest;
use App\Http\Resources\Admin\AdminPlaceResource;
use App\Models\Place;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AdminPlaceController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/admin/places
    // ──────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $places = Place::orderByDesc('created_at')->get();

        return $this->successResponse(
            AdminPlaceResource::collection($places),
            'places_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/admin/places/{id}
    // ──────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $place = Place::find($id);

        if (!$place) {
            return $this->errorResponse('place_not_found', 404);
        }

        return $this->successResponse(
            new AdminPlaceResource($place),
            'place_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // POST /api/admin/places
    // ──────────────────────────────────────────────────
    public function store(PlaceStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $path          = $request->file('image')->store('places', 'public');
            $data['image_url'] = 'storage/' . $path;
        } elseif (empty($data['image_url'])) {
            $data['image_url'] = 'places/default.jpg';
        }

        // Remove 'image' key from data (not a DB column)
        unset($data['image']);

        // Set defaults
        $data['is_active']      = $data['is_active'] ?? true;
        $data['rating_avg']     = 0;
        $data['total_bookings'] = 0;

        $place = Place::create($data);

        return $this->successResponse(
            new AdminPlaceResource($place),
            'place_created',
            201
        );
    }

    // ──────────────────────────────────────────────────
    // PUT /api/admin/places/{id}
    // ──────────────────────────────────────────────────
    public function update(PlaceUpdateRequest $request, int $id): JsonResponse
    {
        $place = Place::find($id);

        if (!$place) {
            return $this->errorResponse('place_not_found', 404);
        }

        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if not default
            if ($place->image_url && !str_contains($place->image_url, 'default')) {
                $oldPath = str_replace('storage/', '', $place->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path          = $request->file('image')->store('places', 'public');
            $data['image_url'] = 'storage/' . $path;
        }

        unset($data['image']);

        $place->update($data);

        return $this->successResponse(
            new AdminPlaceResource($place->fresh()),
            'place_updated',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // DELETE /api/admin/places/{id}
    // ──────────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $place = Place::find($id);

        if (!$place) {
            return $this->errorResponse('place_not_found', 404);
        }

        // Delete image if not default
        if ($place->image_url && !str_contains($place->image_url, 'default')) {
            $oldPath = str_replace('storage/', '', $place->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $place->delete();

        return $this->successResponse(
            null,
            'place_deleted',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // PUT /api/admin/places/{id}/toggle-active
    // ──────────────────────────────────────────────────
    public function toggleActive(int $id): JsonResponse
    {
        $place = Place::find($id);

        if (!$place) {
            return $this->errorResponse('place_not_found', 404);
        }

        $place->update(['is_active' => !$place->is_active]);

        $messageKey = $place->is_active ? 'place_activated' : 'place_deactivated';

        return $this->successResponse(
            new AdminPlaceResource($place->fresh()),
            $messageKey,
            200
        );
    }
}