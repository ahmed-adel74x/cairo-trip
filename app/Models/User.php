<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'preferred_language',
        'trips_count',
        'favourites_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Helpers ──────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ── Relationships (used in later phases) ─────────────
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

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
}