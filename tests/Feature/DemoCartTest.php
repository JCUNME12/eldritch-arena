<?php

namespace Tests\Feature;

use App\Models\DemoCartOrder;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_expanded_catalog_is_repeatable_and_buy_buttons_are_scoped_to_presenter(): void
    {
        [$user, $item] = $this->prepare();
        $this->artisan('arena:demo-catalog', ['presenter' => $user->email])->assertSuccessful();
        $this->artisan('arena:demo-catalog', ['presenter' => $user->email])->assertSuccessful();
        $this->assertDatabaseCount('inventory_items', 46);
        $this->assertSame(12, $item->fresh()->quantity);
        $this->get('/marketplace?q=Arcane')->assertOk()->assertSee('Comprar Arcane Signet')->assertSee('12,90');
        $this->actingAs(User::factory()->create())->get('/marketplace?q=Arcane')->assertOk()->assertDontSee('Comprar Arcane Signet');
    }

    private function prepare(): array
    {
        $user = User::factory()->create();
        $this->artisan('arena:showcase', ['presenter' => $user->email])->assertSuccessful();
        $this->actingAs($user);
        $items = Store::orderBy('id')->get()->map(fn ($store) => $store->items()->where('sku', 'SOL-01')->first());

        return [$user, $items[0], $items[1]];
    }

    private function buy($items, string $method = 'pix'): DemoCartOrder
    {
        foreach ($items as $item) {
            $this->post(route('cart.update', $item), ['quantity' => 2])->assertSessionHasNoErrors();
        }
        $page = $this->get(route('cart.index'))->assertOk();
        $id = $page->viewData('requestId');
        $this->post(route('cart.checkout'), ['method' => $method, 'request_id' => $id, 'amount_cents' => 1])->assertSessionHasNoErrors()->assertRedirect();

        return DemoCartOrder::where('request_id', $id)->firstOrFail();
    }

    public function test_multi_store_checkout_approval_refund_and_duplicate_requests(): void
    {
        [, $a, $b] = $this->prepare();
        $order = $this->buy([$a, $b]);
        $this->assertSame(9960, (int) $order->payments->sum('amount_cents'));
        $this->assertSame(12, $a->fresh()->quantity);
        $this->post(route('cart.checkout'), ['method' => 'pix', 'request_id' => $order->request_id])->assertRedirect(route('cart.order', $order));
        $this->assertDatabaseCount('demo_cart_orders', 1);
        $this->assertDatabaseCount('demo_payments', 2);
        $this->get(route('cart.order', $order))->assertOk()->assertSee('99,60');
        foreach (['paid', 'paid', 'refunded', 'refunded'] as $status) {
            $this->post(route('cart.transition', $order), compact('status'))->assertSessionHasNoErrors();
            $this->assertSame($status === 'paid' ? 10 : 12, $a->fresh()->quantity);
            $this->assertSame($a->fresh()->quantity, $b->fresh()->quantity);
        }
        $this->assertCount(3, $order->payments()->first()->events);
        $this->post(route('showcase.transition', $order->payments()->first()), ['status' => 'paid'])->assertSessionHasErrors('payment');
    }

    public function test_insufficient_stock_rolls_back_every_line(): void
    {
        [, $a, $b] = $this->prepare();
        $order = $this->buy([$a, $b]);
        $b->forceFill(['quantity' => 1])->save();
        $this->post(route('cart.transition', $order), ['status' => 'paid'])->assertSessionHasErrors('quantity');
        $this->assertSame(12, $a->fresh()->quantity);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(['pending'], $order->payments()->pluck('status')->unique()->values()->all());
    }

    public function test_cart_edits_empty_checkout_and_access_control(): void
    {
        [, $a] = $this->prepare();
        $this->post(route('cart.update', $a), ['quantity' => 99])->assertSessionHasErrors('cart');
        $this->post(route('cart.update', $a), ['quantity' => 2])->assertSessionHasNoErrors();
        $this->post(route('cart.update', $a), ['quantity' => 0])->assertSessionHasNoErrors();
        $page = $this->get(route('cart.index'))->assertSee('Seu carrinho está vazio');
        $this->post(route('cart.checkout'), ['method' => 'pix', 'request_id' => $page->viewData('requestId')])->assertSessionHasErrors('cart');
        $order = $this->buy([$a]);
        $this->actingAs(User::factory()->create());
        $this->get(route('cart.order', $order))->assertNotFound();
        $this->post(route('cart.transition', $order), ['status' => 'paid'])->assertNotFound();
        $this->post(route('cart.update', $a), ['quantity' => 1])->assertNotFound();
        $this->get(route('cart.index'))->assertSee('Seu carrinho está vazio');
    }

    public function test_decline_and_cancel_leave_stock_unchanged(): void
    {
        [, $a] = $this->prepare();
        foreach (['card' => 'declined', 'boleto' => 'cancelled'] as $method => $status) {
            $order = $this->buy([$a], $method);
            $this->post(route('cart.transition', $order), compact('status'))->assertSessionHasNoErrors();
            $this->assertSame(12, $a->fresh()->quantity);
            $this->post(route('cart.transition', $order), ['status' => 'paid'])->assertSessionHasErrors('payment');
        }
    }
}
