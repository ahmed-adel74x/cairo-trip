<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminSupportResource;
use App\Models\SupportTicket;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminSupportController extends Controller
{
    use ApiResponseTrait;

    // ──────────────────────────────────────────────────
    // GET /api/admin/support
    // ──────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::with('user')
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, email, or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('problem', 'LIKE', "%{$search}%");
            });
        }

        $tickets = $query->get();

        return $this->successResponse(
            AdminSupportResource::collection($tickets),
            'support_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // GET /api/admin/support/{id}
    // ──────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $ticket = SupportTicket::with('user')->find($id);

        if (!$ticket) {
            return $this->errorResponse('support_not_found', 404);
        }

        return $this->successResponse(
            new AdminSupportResource($ticket),
            'support_ticket_fetched',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // PUT /api/admin/support/{id}/status
    // ──────────────────────────────────────────────────
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,resolved',
        ]);

        if ($validator->fails()) {
            $errors = $this->formatValidationErrors(
                $validator->errors()->toArray()
            );
            return $this->errorResponse('validation_error', 422, $errors);
        }

        $ticket = SupportTicket::with('user')->find($id);

        if (!$ticket) {
            return $this->errorResponse('support_not_found', 404);
        }

        $ticket->update(['status' => $request->status]);

        return $this->successResponse(
            new AdminSupportResource($ticket->fresh('user')),
            'support_status_updated',
            200
        );
    }

    // ──────────────────────────────────────────────────
    // PUT /api/admin/support/{id}/reply
    // ──────────────────────────────────────────────────
    public function reply(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reply' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            $errors = $this->formatValidationErrors(
                $validator->errors()->toArray()
            );
            return $this->errorResponse('validation_error', 422, $errors);
        }

        $ticket = SupportTicket::with('user')->find($id);

        if (!$ticket) {
            return $this->errorResponse('support_not_found', 404);
        }

        $ticket->update([
            'admin_reply' => $request->reply,
            'status'      => 'resolved',
        ]);

        return $this->successResponse(
            new AdminSupportResource($ticket->fresh('user')),
            'support_replied',
            200
        );
    }
}