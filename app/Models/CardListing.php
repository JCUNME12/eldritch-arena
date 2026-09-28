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

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where(fn ($q) => $q->whereNull('inventory_item_id')->orWhereHas('inventoryItem', fn ($i) => $i->where('published', true)->where('archived', false)->where('quantity', '>', 0)));
    }
}
