<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'place_id',
        'booking_id',
        'trip_date',
        'person_count',
        'price_ar',
        'price_en',
        'price_number',
        'status',
    ];

    protected $casts = [
        'trip_date'    => 'date',
        'price_number' => 'float',
    ];

    // ── Relationships ──────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }
}