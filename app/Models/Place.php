<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'image_url',
        'is_free',
        'price_ar',
        'price_en',
        'price_number',
        'working_hours_ar',
        'working_hours_en',
        'location_ar',
        'location_en',
        'rating_avg',
        'total_bookings',
        'activities_ar',
        'activities_en',
        'is_active',
    ];

    protected $casts = [
        'is_free'       => 'boolean',
        'is_active'     => 'boolean',
        'price_number'  => 'float',
        'rating_avg'    => 'float',
        'activities_ar' => 'array',
        'activities_en' => 'array',
    ];

    // ── Relationships ──

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}