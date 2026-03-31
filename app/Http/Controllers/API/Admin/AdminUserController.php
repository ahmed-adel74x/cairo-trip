<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/admin/users
    // ──────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', 'user')
            ->orderByDesc('created_at');

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->get();

        return $this->successResponse(
            AdminUserResource::collection($users),
            'users_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/admin/users/{id}
    // ──────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $user = User::with(['bookings.place', 'trips.place', 'favourites.place'])
            ->find($id);

        if (!$user) {
            return $this->errorResponse('user_not_found', 404);
        }

        // Build detailed response
        $userData = [
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'phone'              => $user->phone,
            'avatar'             => $user->avatar
                                        ? asset('storage/' . $user->avatar)
                                        : null,
            'role'               => $user->role,
            'preferred_language' => $user->preferred_language,
            'trips_count'        => $user->trips_count,
            'favourites_count'   => $user->favourites_count,
            'created_at'         => $user->created_at->toISOString(),

            // Recent bookings
            'recent_bookings' => $user->bookings
                ->sortByDesc('created_at')
                ->take(5)
                ->map(fn($booking) => [
                    'id'           => $booking->id,
                    'place_name'   => [
                        'ar' => $booking->place->name_ar,
                        'en' => $booking->place->name_en,
                    ],
                    'booking_date' => $booking->booking_date->format('Y-m-d'),
                    'status'       => $booking->status,
                    'status_label' => $this->getStatusLabel($booking->status),
                    'total_price'  => [
                        'ar' => $booking->total_price_ar,
                        'en' => $booking->total_price_en,
                    ],
                ])->values(),

            // Favourite places
            'favourite_places' => $user->favourites
                ->take(5)
                ->map(fn($fav) => [
                    'id'   => $fav->place->id,
                    'name' => [
                        'ar' => $fav->place->name_ar,
                        'en' => $fav->place->name_en,
                    ],
                    'image_url' => asset($fav->place->image_url),
                ])->values(),

            // Stats
            'stats' => [
                'total_bookings'   => $user->bookings->count(),
                'total_trips'      => $user->trips->count(),
                'total_favourites' => $user->favourites->count(),
                'completed_trips'  => $user->trips
                    ->where('status', 'completed')->count(),
                'upcoming_trips'   => $user->trips
                    ->where('status', 'upcoming')->count(),
            ],
        ];

        return $this->successResponse(
            $userData,
            'user_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // DELETE /api/admin/users/{id}
    // ──────────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('user_not_found', 404);
        }

        // Cannot delete admin accounts
        if ($user->isAdmin()) {
            return $this->errorResponse('cannot_delete_admin', 422);
        }

        $user->delete();

        return $this->successResponse(
            null,
            'user_deleted',
            200
        );
    }
}