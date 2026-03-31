<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportRequest;
use App\Models\SupportTicket;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class SupportController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // POST /api/support
    // Submit a support ticket (public - no auth needed)
    // ──────────────────────────────────────────────────
    public function store(SupportRequest $request): JsonResponse
    {
        SupportTicket::create([
            'user_id' => auth('sanctum')->id(), // null if not logged in
            'name'    => $request->name ?? 'زائر',
            'email'   => $request->email,
            'phone'   => $request->phone,
            'problem' => $request->problem,
            'status'  => 'pending',
        ]);

        return $this->successResponse(
            null,
            'support_created',
            201
        );
    }
}