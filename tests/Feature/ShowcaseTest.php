<?php

namespace Tests\Feature;

use App\Models\DemoPayment;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShowcaseTest extends TestCase
{
    use RefreshDatabase;

    private function setupStores(): array
    {
        $actor = User::factory()->create();
        $this->artisan('arena:showcase', ['presenter' => $actor->email])->assertSuccessful();
        $this->actingAs($actor);
        $free = Store::whereHas('user', fn ($q) => $q->where('premium_active', false))->firstOrFail();
        $plus = Store::whereHas('user', fn ($q) => $q->where('premium_active', true))->firstOrFail();

        return [$actor, $free, $plus];
    }

    private function order(Store $store, string $method = 'pix', array $overrides = []): DemoPayment
    {
        $data = array_merge(['purpose' => 'sale', 'method' => $method, 'item_id' => $store->items()->where('sku', 'SOL-01')->first()->id, 'quantity' => 2, 'request_id' => (string) Str::uuid()], $overrides);
        $this->post(route('showcase.create', $store), $data)->assertSessionHasNoErrors()->assertRedirect();

        return DemoPayment::where('request_id', $data['request_id'])->firstOrFail();
    }

    private function transition(DemoPayment $payment, string $status)
    {
        return $this->post(route('showcase.transition', $payment), compact('status'));
    }

    public function test_seed_is_isolated_repeatable_and_all_screens_render(): void
    {
        [$actor, $free, $plus] = $this->setupStores();
        $this->artisan('arena:showcase', ['presenter' => $actor->email])->assertSuccessful();
        $this->assertDatabaseCount('stores', 2);
        $this->assertDatabaseCount('inventory_items', 6);
        $this->assertDatabaseCount('card_listings', 0);
        $this->get('/mostra')->assertOk()->assertSee('Mesa Inicial')->assertSee('Arcana Prime');
        $this->get(route('showcase.store', $free))->assertOk()->assertDontSee('Resultado das vendas aprovadas');
        $this->get(route('showcase.store', $plus))->assertOk()->assertSee('Resultado das vendas aprovadas');
        $this->get(route('showcase.checkout', [$free, 'item' => $free->items()->where('sku', 'SOL-01')->first()->id]))->assertOk()->assertSee('Método de pagamento simulado');
        $this->get(route('showcase.checkout', [$free, 'purpose' => 'subscription']))->assertOk();
        $this->get(route('showcase.report', $free))->assertForbidden();
        $this->get(route('showcase.report', $plus))->assertOk()->assertDownload('vendas-demonstrativas.csv');
    }

    public function test_approval_and_refund_are_idempotent_and_money_is_computed_on_server(): void
    {
        [, $free] = $this->setupStores();
        $item = $free->items()->where('sku', 'SOL-01')->first();
        $payment = $this->order($free, 'pix', ['amount_cents' => 1, 'fee_cents' => 0, 'status' => 'paid']);
        $this->assertSame(4980, $payment->amount_cents);
        $this->assertSame(249, $payment->fee_cents);
        $this->assertSame(12, $item->fresh()->quantity);
        $this->transition($payment, 'paid')->assertSessionHasNoErrors();
        $this->transition($payment, 'paid')->assertSessionHasNoErrors();
        $this->assertSame(10, $item->fresh()->quantity);
        $this->assertSame(2, $item->movements()->count());
        $this->transition($payment, 'refunded')->assertSessionHasNoErrors();
        $this->transition($payment, 'refunded')->assertSessionHasNoErrors();
        $this->assertSame(12, $item->fresh()->quantity);
        $this->assertCount(3, $payment->fresh()->events);
        $this->transition($payment, 'paid')->assertSessionHasErrors('payment');
        $this->get(route('showcase.payment', $payment))->assertOk()->assertSee('Estorno integral simulado');
    }

    public function test_declined_card_and_cancelled_boleto_do_not_change_stock(): void
    {
        [, $free] = $this->setupStores();
        foreach (['card' => 'declined', 'boleto' => 'cancelled'] as $method => $state) {
            $p = $this->order($free, $method);
            $this->transition($p, $state)->assertSessionHasNoErrors();
            $this->transition($p, 'paid')->assertSessionHasErrors('payment');
        }
        $this->assertSame(12, $free->items()->where('sku', 'SOL-01')->first()->quantity);
    }

    public function test_pending_orders_recheck_stock_at_approval(): void
    {
        [, $free] = $this->setupStores();
        $first = $this->order($free, 'pix', ['quantity' => 10]);
        $second = $this->order($free, 'card', ['quantity' => 10]);
        $this->transition($first, 'paid')->assertSessionHasNoErrors();
        $this->transition($second, 'paid')->assertSessionHasErrors('quantity');
        $this->assertSame('pending', $second->fresh()->status);
        $this->assertSame(2, $free->items()->where('sku', 'SOL-01')->first()->quantity);
    }

    public function test_subscription_unlocks_only_after_approval_and_refund_restores_free(): void
    {
        [, $free] = $this->setupStores();
        $subscription = $this->order($free, 'card', ['purpose' => 'subscription', 'item_id' => null, 'quantity' => null]);
        $this->assertSame(4990, $subscription->amount_cents);
        $this->get(route('showcase.report', $free))->assertForbidden();
        $this->transition($subscription, 'paid')->assertSessionHasNoErrors();
        $this->assertTrue($free->user->fresh()->isPremium());
        $this->get(route('showcase.report', $free))->assertOk();
        $sale = $this->order($free);
        $this->assertSame(100, $sale->fee_cents); // 49.80 x 2%, half-up.
        $this->transition($subscription, 'refunded')->assertSessionHasNoErrors();
        $this->assertFalse($free->user->fresh()->isPremium());
        $this->get(route('showcase.report', $free))->assertForbidden();
        $this->assertSame(100, $sale->fresh()->fee_cents); // Prior quotes retain their agreed fee.
    }

    public function test_duplicate_pending_subscriptions_cannot_activate_twice(): void
    {
        [, $free] = $this->setupStores();
        $a = $this->order($free, 'pix', ['purpose' => 'subscription', 'item_id' => null]);
        $b = $this->order($free, 'boleto', ['purpose' => 'subscription', 'item_id' => null]);
        $this->transition($a, 'paid')->assertSessionHasNoErrors();
        $this->transition($b, 'paid')->assertSessionHasErrors('payment');
        $this->assertSame('pending', $b->fresh()->status);
    }

    public function test_other_accounts_and_real_stores_are_rejected(): void
    {
        [$actor, $free, $plus] = $this->setupStores();
        $p = $this->order($free);
        $other = User::factory()->create(['type' => 'organizer']);
        $real = new Store(['name' => 'Real', 'contact_email' => 'real@example.test']);
        $real->user_id = $other->id;
        $real->save();
        $this->get(route('showcase.store', $real))->assertNotFound();
        $this->actingAs($other)->get('/mostra')->assertDontSee('Mesa Inicial');
        $this->get(route('showcase.store', $free))->assertNotFound();
        $this->get(route('showcase.payment', $p))->assertNotFound();
        $this->transition($p, 'paid')->assertNotFound();
        $this->get(route('showcase.report', $plus))->assertNotFound();
        $this->actingAs($actor)->post(route('showcase.create', $free), ['purpose' => 'sale', 'method' => 'pix', 'quantity' => 1, 'item_id' => $plus->items()->where('sku', 'SOL-01')->first()->id, 'request_id' => (string) Str::uuid()])->assertNotFound();
    }

    public function test_double_checkout_submit_creates_one_order_and_rejects_changed_payload(): void
    {
        [, $free] = $this->setupStores();
        $id = (string) Str::uuid();
        $p = $this->order($free, 'pix', ['request_id' => $id]);
        $again = $this->order($free, 'pix', ['request_id' => $id]);
        $this->assertSame($p->id, $again->id);
        $this->assertDatabaseCount('demo_payments', 1);
        $this->post(route('showcase.create', $free), ['purpose' => 'sale', 'method' => 'pix', 'quantity' => 3, 'item_id' => $free->items()->where('sku', 'SOL-01')->first()->id, 'request_id' => $id])->assertSessionHasErrors('payment');
    }

    public function test_plus_report_totals_exclude_refunds_and_subscription_revenue(): void
    {
        [, , $plus] = $this->setupStores();
        $p = $this->order($plus);
        $this->transition($p, 'paid')->assertSessionHasNoErrors();
        $this->get(route('showcase.store', $plus))->assertOk()->assertSee('48,80');
        $csv = $this->get(route('showcase.report', $plus))->streamedContent();
        $this->assertStringContainsString('4980;100;4880', $csv);
        $this->transition($p, 'refunded')->assertSessionHasNoErrors();
        $csv = $this->get(route('showcase.report', $plus))->streamedContent();
        $this->assertStringContainsString('4980;100;0', $csv);
    }
}
