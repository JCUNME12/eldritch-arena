<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'game',
        'rarity',
        'condition',
        'price',
        'image_url',
        'seller_name',
        'highlighted',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'highlighted' => 'boolean',
        ];
    }
}
