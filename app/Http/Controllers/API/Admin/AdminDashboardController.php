<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Place;
use App\Models\Rating;
use App\Models\SupportTicket;
use App\Models\Trip;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/admin/dashboard
    // ──────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        // ── Users Stats ──────────────────────────────
        $totalUsers      = User::where('role', 'user')->count();
        $newUsersToday   = User::where('role', 'user')
            ->whereDate('created_at', today())
            ->count();
        $newUsersThisMonth = User::where('role', 'user')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ── Places Stats ─────────────────────────────
        $totalPlaces    = Place::count();
        $activePlaces   = Place::where('is_active', true)->count();
        $inactivePlaces = Place::where('is_active', false)->count();
        $freePlaces     = Place::where('is_free', true)->count();

        // ── Bookings Stats ────────────────────────────
        $totalBookings     = Booking::count();
        $pendingBookings   = Booking::where('status', 'pending')->count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();
        $todayBookings     = Booking::whereDate('created_at', today())->count();

        // ── Trips Stats ───────────────────────────────
        $totalTrips     = Trip::count();
        $upcomingTrips  = Trip::where('status', 'upcoming')->count();
        $completedTrips = Trip::where('status', 'completed')->count();

        // ── Revenue Stats ─────────────────────────────
        $totalRevenue = Booking::whereIn('status', ['pending', 'confirmed'])
            ->sum('total_price_number');
        $monthlyRevenue = Booking::whereIn('status', ['pending', 'confirmed'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price_number');

        // ── Support Stats ─────────────────────────────
        $totalTickets    = SupportTicket::count();
        $pendingTickets  = SupportTicket::where('status', 'pending')->count();
        $inProgressTickets = SupportTicket::where('status', 'in_progress')->count();
        $resolvedTickets = SupportTicket::where('status', 'resolved')->count();

        // ── Ratings Stats ─────────────────────────────
        $totalRatings   = Rating::count();
        $averageRating  = Rating::avg('rating') ?? 0;

        // ── Most Booked Places ────────────────────────
        $mostBookedPlaces = Place::orderByDesc('total_bookings')
            ->take(5)
            ->get()
            ->map(fn($place) => [
                'id'             => $place->id,
                'name'           => [
                    'ar' => $place->name_ar,
                    'en' => $place->name_en,
                ],
                'total_bookings' => $place->total_bookings,
                'rating_avg'     => round($place->rating_avg, 1),
                'image_url'      => asset($place->image_url),
            ]);

        // ── Recent Bookings ───────────────────────────
        $recentBookings = Booking::with(['user', 'place'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn($booking) => [
                'id'           => $booking->id,
                'user_name'    => $booking->user->name,
                'place_name'   => [
                    'ar' => $booking->place->name_ar,
                    'en' => $booking->place->name_en,
                ],
                'booking_date' => $booking->booking_date->format('Y-m-d'),
                'total_price'  => [
                    'ar' => $booking->total_price_ar,
                    'en' => $booking->total_price_en,
                ],
                'status'       => $booking->status,
                'status_label' => $this->getStatusLabel($booking->status),
            ]);

        // ── Recent Users ──────────────────────────────
        $recentUsers = User::where('role', 'user')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn($user) => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'created_at' => $user->created_at->toISOString(),
            ]);

        return $this->successResponse(
            [
                // ── Summary Cards ──────────────────────
                'summary' => [
                    'users' => [
                        'total'       => $totalUsers,
                        'new_today'   => $newUsersToday,
                        'this_month'  => $newUsersThisMonth,
                        'label'       => [
                            'ar' => 'المستخدمون',
                            'en' => 'Users',
                        ],
                    ],
                    'places' => [
                        'total'    => $totalPlaces,
                        'active'   => $activePlaces,
                        'inactive' => $inactivePlaces,
                        'free'     => $freePlaces,
                        'label'    => [
                            'ar' => 'الأماكن',
                            'en' => 'Places',
                        ],
                    ],
                    'bookings' => [
                        'total'     => $totalBookings,
                        'pending'   => $pendingBookings,
                        'confirmed' => $confirmedBookings,
                        'cancelled' => $cancelledBookings,
                        'today'     => $todayBookings,
                        'label'     => [
                            'ar' => 'الحجوزات',
                            'en' => 'Bookings',
                        ],
                    ],
                    'trips' => [
                        'total'     => $totalTrips,
                        'upcoming'  => $upcomingTrips,
                        'completed' => $completedTrips,
                        'label'     => [
                            'ar' => 'الرحلات',
                            'en' => 'Trips',
                        ],
                    ],
                    'revenue' => [
                        'total'   => [
                            'number' => $totalRevenue,
                            'ar'     => number_format($totalRevenue, 0) . ' جنيه',
                            'en'     => number_format($totalRevenue, 0) . ' EGP',
                        ],
                        'monthly' => [
                            'number' => $monthlyRevenue,
                            'ar'     => number_format($monthlyRevenue, 0) . ' جنيه',
                            'en'     => number_format($monthlyRevenue, 0) . ' EGP',
                        ],
                        'label'   => [
                            'ar' => 'الإيرادات',
                            'en' => 'Revenue',
                        ],
                    ],
                    'support' => [
                        'total'       => $totalTickets,
                        'pending'     => $pendingTickets,
                        'in_progress' => $inProgressTickets,
                        'resolved'    => $resolvedTickets,
                        'label'       => [
                            'ar' => 'الدعم الفني',
                            'en' => 'Support',
                        ],
                    ],
                    'ratings' => [
                        'total'   => $totalRatings,
                        'average' => round($averageRating, 1),
                        'label'   => $this->getRatingLabel(round($averageRating)),
                    ],
                ],

                // ── Charts Data ────────────────────────
                'most_booked_places' => $mostBookedPlaces,
                'recent_bookings'    => $recentBookings,
                'recent_users'       => $recentUsers,
            ],
            'dashboard_fetched',
            200
        );
    }
}