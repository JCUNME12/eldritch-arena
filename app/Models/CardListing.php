<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'game',
        'edition',
        'rarity',
        'condition',
        'description',
        'price',
        'image_url',
        'seller_name',
        'seller_type',
        'contact_email',
        'highlighted',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'highlighted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
