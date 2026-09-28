<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function move(InventoryItem $item, int $actor, int $delta, string $reason, string $requestId): void
    {
        try {
            DB::transaction(function () use ($item, $actor, $delta, $reason, $requestId) {
                $locked = InventoryItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
                $existing = StockMovement::where('request_id', $requestId)->first();
                if ($existing) {
                    if ($existing->inventory_item_id === $locked->id && $existing->user_id === $actor && $existing->delta === $delta && $existing->reason === $reason) {
                        return;
                    }
                    throw ValidationException::withMessages(['quantity' => 'Esta operação já foi utilizada. Recarregue a página.']);
                }
                if ($locked->archived) {
                    throw ValidationException::withMessages(['quantity' => 'Reative o produto antes de movimentar o estoque.']);
                }
                $after = $locked->quantity + $delta;
                if ($delta === 0 || $after < 0 || $after > 999999) {
                    throw ValidationException::withMessages(['quantity' => 'Movimentação inválida: estoque deve ficar entre zero e 999.999.']);
                }
                $locked->movements()->create(['user_id' => $actor, 'request_id' => $requestId, 'delta' => $delta, 'before_quantity' => $locked->quantity, 'after_quantity' => $after, 'reason' => $reason]);
                $locked->quantity = $after;
                $locked->save();
            });
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['quantity' => 'Esta operação já foi registrada. Recarregue a página para conferir o saldo.']);
        }
    }

    public function syncListing(InventoryItem $item): void
    {
        $store = $item->store;
        $listing = $item->listing()->first();
        if (! $listing && ! $item->published) {
            return;
        }
        $data = $item->only(['name', 'game', 'edition', 'rarity', 'condition', 'description', 'image_url', 'price']);
        $data += ['user_id' => $store->user_id, 'seller_name' => $store->name, 'seller_type' => 'loja', 'contact_email' => $store->contact_email, 'highlighted' => $store->user->isPremium()];
        if ($listing) {
            $listing->update($data);
        } else {
            $item->listing()->create($data);
        }
    }
}
