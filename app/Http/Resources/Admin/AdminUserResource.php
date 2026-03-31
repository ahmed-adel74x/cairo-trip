<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'email'              => $this->email,
            'phone'              => $this->phone,
            'avatar'             => $this->avatar
                                        ? asset('storage/' . $this->avatar)
                                        : null,
            'role'               => $this->role,
            'role_label'         => [
                'ar' => $this->role === 'admin' ? 'مدير' : 'مستخدم',
                'en' => $this->role === 'admin' ? 'Admin' : 'User',
            ],
            'preferred_language' => $this->preferred_language,
            'trips_count'        => $this->trips_count,
            'favourites_count'   => $this->favourites_count,

            // Detailed counts from relations
            'bookings_count'     => $this->whenLoaded('bookings',
                fn() => $this->bookings->count(), 0
            ),
            'created_at'         => $this->created_at->toISOString(),
        ];
    }
}