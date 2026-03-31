<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavouriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'place'        => [
                'id'             => $this->place->id,
                'name'           => [
                    'ar' => $this->place->name_ar,
                    'en' => $this->place->name_en,
                ],
                'description'    => [
                    'ar' => $this->place->description_ar,
                    'en' => $this->place->description_en,
                ],
                'image_url'      => asset($this->place->image_url),
                'is_free'        => $this->place->is_free,
                'price'          => [
                    'ar' => $this->place->price_ar,
                    'en' => $this->place->price_en,
                ],
                'price_number'   => $this->place->price_number,
                'location'       => [
                    'ar' => $this->place->location_ar,
                    'en' => $this->place->location_en,
                ],
                'rating_avg'     => round($this->place->rating_avg, 1),
                'total_bookings' => $this->place->total_bookings,
            ],
            'created_at'   => $this->created_at->toISOString(),
        ];
    }
}