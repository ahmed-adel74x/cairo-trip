<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminBookingResource;
use App\Models\Booking;
use App\Models\Trip;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/admin/bookings
    // ──────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['user', 'place'])
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('booking_date', $request->date);
        }

        // Search by user name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $bookings = $query->get();

        return $this->successResponse(
            AdminBookingResource::collection($bookings),
            'bookings_admin_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // PUT /api/admin/bookings/{id}/confirm
    // ──────────────────────────────────────────────────
    public function confirm(int $id): JsonResponse
    {
        $booking = Booking::with(['user', 'place'])->find($id);

        if (!$booking) {
            return $this->errorResponse('booking_not_found', 404);
        }

        if ($booking->status !== 'pending') {
            return $this->errorResponse('booking_cannot_cancel', 422);
        }

        $booking->update(['status' => 'confirmed']);

        // Keep trip as upcoming when confirmed
        Trip::where('booking_id', $booking->id)
            ->update(['status' => 'upcoming']);

        return $this->successResponse(
            new AdminBookingResource($booking->fresh(['user', 'place'])),
            'booking_confirmed',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // PUT /api/admin/bookings/{id}/cancel
    // ──────────────────────────────────────────────────
    public function cancel(int $id): JsonResponse
    {
        $booking = Booking::with(['user', 'place'])->find($id);

        if (!$booking) {
            return $this->errorResponse('booking_not_found', 404);
        }

        if ($booking->status === 'cancelled') {
            return $this->errorResponse('booking_cannot_cancel', 422);
        }

        // Update booking status to cancelled
        $booking->update(['status' => 'cancelled']);

        // ✅ Delete the linked trip completely
        Trip::where('booking_id', $booking->id)->delete();

        // ✅ Decrement user trips_count
        if ($booking->user->trips_count > 0) {
            $booking->user->decrement('trips_count');
        }

        // ✅ Decrement place total_bookings
        $booking->place()->decrement('total_bookings');

        return $this->successResponse(
            new AdminBookingResource($booking->fresh(['user', 'place'])),
            'booking_cancelled',
            200
        );
    }
}