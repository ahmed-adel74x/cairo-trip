<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Place;
use App\Models\Trip;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/bookings
    // Get all bookings for authenticated user
    // ──────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with('place')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse(
            BookingResource::collection($bookings),
            'bookings_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // POST /api/bookings
    // Create a new booking
    // ──────────────────────────────────────────────────
    public function store(BookingRequest $request): JsonResponse
    {
        $user  = $request->user();
        $place = Place::active()->find($request->place_id);

        if (!$place) {
            return $this->errorResponse('place_not_found', 404);
        }

        // Check if user already has active booking for this place
        $existingBooking = Booking::where('user_id', $user->id)
            ->where('place_id', $place->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingBooking) {
            return $this->errorResponse('already_booked', 422);
        }

        // Calculate total price
        $personCount      = $request->person_count;
        $totalPriceNumber = $place->price_number * $personCount;

        // Build bilingual price strings
        if ($place->is_free) {
            $totalPriceAr = 'مجاني';
            $totalPriceEn = 'Free';
        } else {
            $totalPriceAr = ($totalPriceNumber) . ' جنيه';
            $totalPriceEn = $totalPriceNumber . ' EGP';
        }

        // Create booking
        $booking = Booking::create([
            'user_id'            => $user->id,
            'place_id'           => $place->id,
            'booking_date'       => $request->booking_date,
            'person_count'       => $personCount,
            'total_price_ar'     => $totalPriceAr,
            'total_price_en'     => $totalPriceEn,
            'total_price_number' => $totalPriceNumber,
            'status'             => 'pending',
        ]);

        // Create trip record linked to this booking
        Trip::create([
            'user_id'      => $user->id,
            'place_id'     => $place->id,
            'booking_id'   => $booking->id,
            'trip_date'    => $request->booking_date,
            'person_count' => $personCount,
            'price_ar'     => $totalPriceAr,
            'price_en'     => $totalPriceEn,
            'price_number' => $totalPriceNumber,
            'status'       => 'upcoming',
        ]);

        // Update place total_bookings counter
        $place->increment('total_bookings');

        // Update user trips_count
        $user->increment('trips_count');

        $booking->load('place');

        return $this->successResponse(
            new BookingResource($booking),
            'booking_created',
            201
        );
    }

    // ──────────────────────────────────────────────────
    // PUT /api/bookings/{id}/cancel
    // Cancel a booking
    // ──────────────────────────────────────────────────
    public function cancel(Request $request, int $id): JsonResponse
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->find($id);

        if (!$booking) {
            return $this->errorResponse('booking_not_found', 404);
        }

        // Can only cancel pending or confirmed bookings
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return $this->errorResponse('booking_cannot_cancel', 422);
        }

        $booking->update(['status' => 'cancelled']);

        // Update the linked trip status too
        Trip::where('booking_id', $booking->id)
            ->update(['status' => 'upcoming']);

        // Decrement user trips_count
        if ($request->user()->trips_count > 0) {
            $request->user()->decrement('trips_count');
        }

        $booking->load('place');

        return $this->successResponse(
            new BookingResource($booking),
            'booking_cancelled',
            200
        );
    }
}