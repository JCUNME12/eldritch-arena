<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'avatar_color',
        'premium_plan',
        'premium_active',
        'premium_started_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'premium_active' => 'boolean',
            'premium_started_at' => 'datetime',
        ];
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'organizer_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function cardListings(): HasMany
    {
        return $this->hasMany(CardListing::class);
    }

    public function communityTopics(): HasMany
    {
        return $this->hasMany(CommunityTopic::class);
    }

    public function communityComments(): HasMany
    {
        return $this->hasMany(CommunityComment::class);
    }

    public function isPlayer(): bool
    {
        return $this->type === 'player';
    }

    public function isOrganizer(): bool
    {
        return $this->type === 'organizer';
    }

    public function isPremium(): bool
    {
        return (bool) $this->premium_active;
    }
}
