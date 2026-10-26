<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerDeck extends Model
{
    protected $fillable = ['name', 'game', 'format', 'archetype', 'notes'];

    protected function casts(): array
    {
        return ['archived' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function matches()
    {
        return $this->hasMany(PlayerMatch::class);
    }
}
