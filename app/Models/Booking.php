<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'place_id',
        'booking_date',
        'person_count',
        'total_price_ar',
        'total_price_en',
        'total_price_number',
        'status',
    ];

    protected $casts = [
        'booking_date'       => 'date',
        'total_price_number' => 'float',
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

    public function trip()
    {
        return $this->hasOne(Trip::class);
    }

    // ── Scopes ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }
}