<?php

namespace App\Services;

use App\Models\DemoCartOrder;
use App\Models\InventoryItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DemoCartService
{
    public function checkout(User $actor, array $cart, string $method, string $requestId): DemoCartOrder
    {
        return DB::transaction(function () use ($actor, $cart, $method, $requestId) {
            User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            if ($existing = DemoCartOrder::where('request_id', $requestId)->first()) {
                abort_unless($existing->user_id === $actor->id && $existing->method === $method, 409);

                return $existing;
            }
            if (! $cart || count($cart) > 30) {
                throw ValidationException::withMessages(['cart' => 'Escolha entre 1 e 30 produtos para finalizar.']);
            }
            $items = InventoryItem::with('store')->whereIn('id', array_keys($cart))->orderBy('store_id')->orderBy('id')->get();
            abort_unless($items->count() === count($cart), 422);
            Store::whereIn('id', $items->pluck('store_id'))->orderBy('id')->lockForUpdate()->get();
            $order = DemoCartOrder::create(['user_id' => $actor->id, 'request_id' => $requestId, 'method' => $method, 'status' => 'pending']);
            foreach ($items as $item) {
                $payment = app(DemoPaymentService::class)->create($item->store, $actor, [
                    'request_id' => (string) Str::uuid(), 'purpose' => 'sale', 'method' => $method,
                    'item_id' => $item->id, 'quantity' => $cart[$item->id],
                ]);
                $payment->update(['cart_order_id' => $order->id]);
            }

            return $order;
        });
    }

    public function transition(DemoCartOrder $order, User $actor, string $target): void
    {
        abort_unless($order->user_id === $actor->id, 404);
        DB::transaction(function () use ($order, $actor, $target) {
            Store::whereIn('id', $order->payments()->select('store_id'))->orderBy('id')->lockForUpdate()->get();
            $order = DemoCartOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            foreach ($order->payments()->orderBy('id')->get() as $payment) {
                app(DemoPaymentService::class)->transition($payment, $actor, $target, true);
            }
            $order->update(['status' => $target]);
        });
    }
}
