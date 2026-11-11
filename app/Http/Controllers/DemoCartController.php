<?php

namespace App\Http\Controllers;

use App\Models\DemoCartOrder;
use App\Models\InventoryItem;
use App\Services\DemoCartService;
use App\Services\DemoPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DemoCartController extends Controller
{
    private function key(Request $r): string
    {
        return 'demo_cart_'.$r->user()->id;
    }

    public function index(Request $r)
    {
        $key = $this->key($r);
        $cart = $r->session()->get($key, []);
        $items = InventoryItem::with('store')->whereIn('id', array_keys($cart))->get();
        foreach ($items as $item) {
            app(DemoPaymentService::class)->authorize($item->store, $r->user());
        }
        $requestId = $r->session()->get($key.'_request', (string) Str::uuid());
        $r->session()->put($key.'_request', $requestId);
        $orders = DemoCartOrder::where('user_id', $r->user()->id)->latest()->limit(10)->get();

        return view('showcase.cart', compact('cart', 'items', 'requestId', 'orders'));
    }

    public function update(Request $r, InventoryItem $item)
    {
        app(DemoPaymentService::class)->authorize($item->store, $r->user());
        $data = $r->validate(['quantity' => 'required|integer|min:0|max:99', 'add' => 'sometimes|boolean']);
        $key = $this->key($r);
        $cart = $r->session()->get($key, []);
        $quantity = (int) $data['quantity'] + ($r->boolean('add') ? ($cart[$item->id] ?? 0) : 0);
        if ($quantity > 0 && ($item->archived || $quantity > min(99, $item->quantity))) {
            throw ValidationException::withMessages(['cart' => 'Quantidade indisponível. Confira o estoque da carta.']);
        }
        if ($quantity === 0) {
            unset($cart[$item->id]);
        } else {
            $cart[$item->id] = $quantity;
        }
        if (count($cart) > 30) {
            throw ValidationException::withMessages(['cart' => 'Limite de 30 produtos por carrinho.']);
        }
        $r->session()->put($key, $cart);
        $r->session()->put($key.'_request', (string) Str::uuid());

        return redirect()->route('cart.index')->with('status', 'Carrinho atualizado.');
    }

    public function checkout(Request $r, DemoCartService $service)
    {
        $data = $r->validate(['method' => 'required|in:pix,card,boleto', 'request_id' => 'required|uuid']);
        $key = $this->key($r);
        $existing = DemoCartOrder::where('request_id', $data['request_id'])->where('user_id', $r->user()->id)->exists();
        abort_unless($existing || $r->session()->get($key.'_request') === $data['request_id'], 409);
        $order = $service->checkout($r->user(), $r->session()->get($key, []), $data['method'], $data['request_id']);
        if ($r->session()->get($key.'_request') === $data['request_id']) {
            $r->session()->forget([$key, $key.'_request']);
        }

        return redirect()->route('cart.order', $order);
    }

    public function order(Request $r, DemoCartOrder $order)
    {
        abort_unless($order->user_id === $r->user()->id, 404);
        $order->load('payments.store');

        return view('showcase.cart-order', compact('order'));
    }

    public function transition(Request $r, DemoCartOrder $order, DemoCartService $service)
    {
        $data = $r->validate(['status' => 'required|in:paid,declined,cancelled,refunded']);
        $service->transition($order, $r->user(), $data['status']);

        return back()->with('status', 'Pedido demonstrativo atualizado. Nenhuma cobrança real.');
    }
}
