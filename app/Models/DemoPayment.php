<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoPayment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['events' => 'array', 'amount_cents' => 'integer', 'cost_cents' => 'integer', 'fee_cents' => 'integer', 'quantity' => 'integer'];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function statusLabel(): string
    {
        return ['pending' => 'Aguardando pagamento', 'paid' => 'Aprovado', 'declined' => 'Recusado', 'cancelled' => 'Cancelado', 'refunded' => 'Estornado'][$this->status];
    }

    public function methodLabel(): string
    {
        return ['pix' => 'Pix', 'card' => 'Cartão', 'boleto' => 'Boleto'][$this->method];
    }
}
