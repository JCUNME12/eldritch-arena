<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlayerMatch extends Model
{
    use SoftDeletes;

    protected $fillable = ['request_id', 'opponent', 'opponent_key', 'result', 'played_on', 'players', 'context', 'notes'];

    protected function casts(): array
    {
        return ['played_on' => 'date', 'players' => 'integer'];
    }

    public function deck()
    {
        return $this->belongsTo(PlayerDeck::class, 'player_deck_id');
    }

    public function resultLabel(): string
    {
        return ['win' => 'Vitória', 'loss' => 'Derrota', 'draw' => 'Empate'][$this->result];
    }
}
