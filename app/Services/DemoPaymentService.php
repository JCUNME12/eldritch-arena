<?php

namespace App\Services;

use App\Models\DemoPayment;
use App\Models\InventoryItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DemoPaymentService
{
    public const MONTHLY_CENTS = 4990;

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['payment' => $message]);
    }

    public function authorize(Store $store, User $actor): void
    {
        abort_unless($store->is_demo && ($actor->isAdmin() || $store->demo_presenter_id === $actor->id || $store->user_id === $actor->id), 404);
    }

    private function cents(string $value): int
    {
        [$whole, $fraction] = array_pad(explode('.', $value), 2, '0');

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    public function create(Store $store, User $actor, array $data): DemoPayment
    {
        $this->authorize($store, $actor);

        return DB::transaction(function () use ($store, $actor, $data) {
            $store = Store::whereKey($store->id)->lockForUpdate()->firstOrFail();
            $existing = DemoPayment::where('request_id', $data['request_id'])->first();
            if ($existing) {
                if ($existing->store_id !== $store->id || $existing->user_id !== $actor->id || $existing->purpose !== $data['purpose'] || $existing->method !== $data['method'] || $existing->inventory_item_id !== ($data['purpose'] === 'sale' ? (int) $data['item_id'] : null) || $existing->quantity !== ($data['purpose'] === 'sale' ? (int) $data['quantity'] : 1)) {
                    $this->fail('Este envio já foi utilizado. Abra um novo checkout.');
                }

                return $existing;
            }
            $values = ['store_id' => $store->id, 'user_id' => $actor->id, 'request_id' => $data['request_id'], 'purpose' => $data['purpose'], 'method' => $data['method'], 'status' => 'pending', 'events' => [['status' => 'pending', 'at' => now()->toIso8601String()]]];
            if ($data['purpose'] === 'sale') {
                $item = $store->items()->whereKey($data['item_id'])->lockForUpdate()->firstOrFail();
                $quantity = (int) $data['quantity'];
                if ($item->archived || $item->quantity < $quantity || $quantity < 1 || $quantity > 99) {
                    $this->fail('Quantidade indisponível para esta demonstração.');
                }
                $amount = $this->cents($item->price) * $quantity;
                if ($amount < 1) {
                    $this->fail('O produto precisa ter preço maior que zero.');
                }
                $rate = $store->user->isPremium() ? 200 : 500;
                $values += ['inventory_item_id' => $item->id, 'description' => $item->name, 'quantity' => $quantity, 'amount_cents' => $amount, 'cost_cents' => $this->cents($item->cost) * $quantity, 'fee_basis_points' => $rate, 'fee_cents' => intdiv($amount * $rate + 5000, 10000)];
            } else {
                if ($store->user->isPremium()) {
                    $this->fail('Esta loja já possui o Plus ativo. Use a loja gratuita para demonstrar a assinatura.');
                }
                $values += ['description' => 'Arena Plus · um mês demonstrativo', 'amount_cents' => self::MONTHLY_CENTS];
            }

            return DemoPayment::create($values);
        });
    }

    public function transition(DemoPayment $payment, User $actor, string $target): DemoPayment
    {
        $this->authorize($payment->store, $actor);

        return DB::transaction(function () use ($payment, $actor, $target) {
            // All payment operations lock the store first, keeping stock and plan changes serial.
            $store = Store::whereKey($payment->store_id)->lockForUpdate()->firstOrFail();
            $payment = DemoPayment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status === $target) {
                return $payment;
            }
            $allowed = ['pending' => ['paid', 'declined', 'cancelled'], 'paid' => ['refunded']];
            if (! in_array($target, $allowed[$payment->status] ?? [], true)) {
                $this->fail('Esta mudança não é permitida no estado atual do pagamento.');
            }
            if ($payment->purpose === 'sale' && in_array($target, ['paid', 'refunded'], true)) {
                $item = InventoryItem::whereKey($payment->inventory_item_id)->lockForUpdate()->firstOrFail();
                app(InventoryService::class)->move($item, $actor->id, $payment->quantity * ($target === 'paid' ? -1 : 1), 'DEMO #'.$payment->id.($target === 'paid' ? ' · venda aprovada' : ' · estorno integral'), (string) Str::uuid());
            }
            if ($payment->purpose === 'subscription' && in_array($target, ['paid', 'refunded'], true)) {
                $user = User::whereKey($store->user_id)->lockForUpdate()->firstOrFail();
                if ($target === 'paid' && $user->isPremium()) {
                    $this->fail('O Plus já foi ativado por outra operação. Cancele este pagamento pendente.');
                }
                $user->update(['premium_active' => $target === 'paid', 'premium_plan' => $target === 'paid' ? 'loja_premium' : 'free', 'premium_started_at' => $target === 'paid' ? now() : null]);
            }
            $events = $payment->events;
            $events[] = ['status' => $target, 'at' => now()->toIso8601String()];
            $payment->update(['status' => $target, 'events' => $events]);

            return $payment;
        });
    }
}
