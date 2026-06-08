<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'title',
        'game',
        'starts_at',
        'prize',
        'entry_fee',
        'slots',
        'location',
        'description',
        'highlighted',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'entry_fee' => 'decimal:2',
            'highlighted' => 'boolean',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function isUserRegistered(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->registrations()->where('user_id', $user->id)->exists();
    }
}
