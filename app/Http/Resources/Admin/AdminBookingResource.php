<?php

namespace App\Http\Resources\Admin;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBookingResource extends JsonResource
{
    use ApiResponseTrait;

    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'user'         => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ],
            'place'        => [
                'id'       => $this->place->id,
                'name'     => [
                    'ar' => $this->place->name_ar,
                    'en' => $this->place->name_en,
                ],
                'location' => [
                    'ar' => $this->place->location_ar,
                    'en' => $this->place->location_en,
                ],
            ],
            'booking_date'       => $this->booking_date->format('Y-m-d'),
            'person_count'       => $this->person_count,
            'total_price'        => [
                'ar' => $this->total_price_ar,
                'en' => $this->total_price_en,
            ],
            'total_price_number' => $this->total_price_number,
            'status'             => $this->status,
            'status_label'       => $this->getStatusLabel($this->status),
            'created_at'         => $this->created_at->toISOString(),
        ];
    }
}