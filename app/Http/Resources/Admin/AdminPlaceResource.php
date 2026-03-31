<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPlaceResource extends JsonResource
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
            'is_active'      => $this->is_active,
            'is_active_label' => [
                'ar' => $this->is_active ? 'مفعّل' : 'موقوف',
                'en' => $this->is_active ? 'Active' : 'Inactive',
            ],
            'created_at'     => $this->created_at->toISOString(),
            'updated_at'     => $this->updated_at->toISOString(),
        ];
    }
}