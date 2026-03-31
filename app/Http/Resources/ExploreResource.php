<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExploreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'description'    => [
                'ar' => $this->description_ar,
                'en' => $this->description_en,
            ],
            'image_url'      => asset($this->image_url),
            'is_free'        => $this->is_free,
            'price'          => [
                'ar' => $this->price_ar,
                'en' => $this->price_en,
            ],
            'price_number'   => $this->price_number,
            'working_hours'  => [
                'ar' => $this->working_hours_ar,
                'en' => $this->working_hours_en,
            ],
            'location'       => [
                'ar' => $this->location_ar,
                'en' => $this->location_en,
            ],
            'rating_avg'     => round($this->rating_avg, 1),
            'total_bookings' => $this->total_bookings,
            'activities'     => [
                'ar' => $this->activities_ar ?? [],
                'en' => $this->activities_en ?? [],
            ],
            // ── user-specific fields ──
            'is_favourite'   => $this->is_favourite ?? false,
            'is_booked'      => $this->is_booked ?? false,
        ];
    }
}