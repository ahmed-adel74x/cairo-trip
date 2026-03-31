<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'place_id',
        'trip_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'float',
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
        return $this->belongsTo(Trip::class);
    }
}