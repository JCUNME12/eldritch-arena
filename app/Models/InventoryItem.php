<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = ['sku', 'name', 'game', 'edition', 'rarity', 'condition', 'description', 'image_url', 'cost', 'price', 'minimum_quantity'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'cost' => 'decimal:2', 'quantity' => 'integer', 'minimum_quantity' => 'integer', 'published' => 'boolean', 'archived' => 'boolean'];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function listing()
    {
        return $this->hasOne(CardListing::class);
    }
}
