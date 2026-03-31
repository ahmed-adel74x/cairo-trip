<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\ApiResponseTrait;

class TripResource extends JsonResource
{
    use ApiResponseTrait;

    public function toArray(Request $request): array
    {
        // Check if this trip has been rated
        $rating = $this->rating;

        return [
            'id'           => $this->id,
            'place'        => [
                'id'        => $this->place->id,
                'name'      => [
                    'ar' => $this->place->name_ar,
                    'en' => $this->place->name_en,
                ],
                'image_url' => asset($this->place->image_url),
                'location'  => [
                    'ar' => $this->place->location_ar,
                    'en' => $this->place->location_en,
                ],
            ],
            'trip_date'    => $this->trip_date->format('Y-m-d'),
            'person_count' => $this->person_count,
            'price'        => [
                'ar' => $this->price_ar,
                'en' => $this->price_en,
            ],
            'price_number' => $this->price_number,
            'status'       => $this->status,
            'status_label' => $this->getStatusLabel($this->status),

            // Rating info
            'is_rated'     => !is_null($rating),
            'rating'       => $rating ? [
                'value'   => $rating->rating,
                'label'   => $this->getRatingLabel($rating->rating),
                'comment' => $rating->comment,
            ] : null,

            'created_at'   => $this->created_at->toISOString(),
        ];
    }
}