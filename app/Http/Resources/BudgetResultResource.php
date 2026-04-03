<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'description'      => [
                'ar' => $this->description_ar,
                'en' => $this->description_en,
            ],
            'image_url'        => asset($this->image_url),
            'is_free'          => $this->is_free,
            'price'            => [
                'ar' => $this->price_ar,
                'en' => $this->price_en,
            ],
            'price_number'     => $this->price_number,
            'location'         => [
                'ar' => $this->location_ar,
                'en' => $this->location_en,
            ],
            'working_hours'    => [
                'ar' => $this->working_hours_ar,
                'en' => $this->working_hours_en,
            ],
            'rating_avg'       => round($this->rating_avg, 1),
            'total_bookings'   => $this->total_bookings,
            'activities'       => [
                'ar' => $this->activities_ar ?? [],
                'en' => $this->activities_en ?? [],
            ],
            // ── Budget specific ──────────────────────────
            'can_afford'       => $this->can_afford ?? true,
            'price_per_person' => $this->price_number,
            'total_cost'       => $this->total_cost ?? $this->price_number,

            // ── User specific ────────────────────────────
            'is_booked'        => $this->is_booked ?? false,
        ];
    }
}