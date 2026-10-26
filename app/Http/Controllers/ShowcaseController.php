<?php

namespace App\Http\Controllers;

use App\Models\DemoPayment;
use App\Models\Store;
use App\Services\DemoPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShowcaseController extends Controller
{
    public function index(Request $r)
    {
        $stores = Store::with('user')->where('is_demo', true)->where(fn ($q) => $q->where('demo_presenter_id', $r->user()->id)->orWhere('user_id', $r->user()->id))->get();

        return view('showcase.index', compact('stores'));
    }

    public function store(Request $r, Store $store, DemoPaymentService $service)
    {
        $service->authorize($store, $r->user());
        $store->load('user', 'items');
        $orders = DemoPayment::where('store_id', $store->id)->latest('id')->paginate(10);
        $sales = DemoPayment::where('store_id', $store->id)->where('purpose', 'sale');
        $stats = (clone $sales)->where('status', 'paid')->selectRaw('COUNT(*) as paid, COALESCE(SUM(amount_cents),0) as gross, COALESCE(SUM(fee_cents),0) as fees, COALESCE(SUM(cost_cents),0) as cost')->first();
        $pending = (clone $sales)->where('status', 'pending')->count();

        return view('showcase.store', compact('store', 'orders', 'stats', 'pending'));
    }

    public function checkout(Request $r, Store $store, DemoPaymentService $service)
    {
        $service->authorize($store, $r->user());
        $data = $r->validate(['purpose' => ['nullable', 'in:sale,subscription'], 'item' => ['nullable', 'integer']]);
        $purpose = $data['purpose'] ?? 'sale';
        $item = $purpose === 'sale' ? $store->items()->findOrFail($data['item'] ?? 0) : null;

        return view('showcase.checkout', ['store' => $store, 'item' => $item, 'purpose' => $purpose, 'requestId' => (string) Str::uuid()]);
    }

    public function create(Request $r, Store $store, DemoPaymentService $service)
    {
        $service->authorize($store, $r->user());
        $data = $r->validate(['purpose' => ['required', 'in:sale,subscription'], 'method' => ['required', 'in:pix,card,boleto'], 'request_id' => ['required', 'uuid'], 'item_id' => ['required_if:purpose,sale', 'nullable', 'integer'], 'quantity' => ['required_if:purpose,sale', 'nullable', 'integer', 'min:1', 'max:99']]);
        $payment = $service->create($store, $r->user(), $data);

        return redirect()->route('showcase.payment', $payment);
    }

    public function payment(Request $r, DemoPayment $payment, DemoPaymentService $service)
    {
        $service->authorize($payment->store, $r->user());

        return view('showcase.payment', compact('payment'));
    }

    public function transition(Request $r, DemoPayment $payment, DemoPaymentService $service)
    {
        $service->authorize($payment->store, $r->user());
        $data = $r->validate(['status' => ['required', 'in:paid,declined,cancelled,refunded']]);
        $service->transition($payment, $r->user(), $data['status']);

        return back()->with('status', 'Cenário demonstrativo atualizado. Nenhum dinheiro foi movimentado.');
    }

    public function report(Request $r, Store $store, DemoPaymentService $service)
    {
        $service->authorize($store, $r->user());
        abort_unless($store->user->isPremium(), 403, 'Relatório disponível no plano Plus.');

        return response()->streamDownload(function () use ($store) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['DEMONSTRAÇÃO SEM VALOR FINANCEIRO', 'Método', 'Estado', 'Bruto (centavos)', 'Taxa ilustrativa (centavos)', 'Líquido (centavos)'], ';', '"', '');
            foreach (DemoPayment::where('store_id', $store->id)->where('purpose', 'sale')->orderBy('id')->cursor() as $p) {
                fputcsv($out, ['DEMO-'.$p->id, $p->methodLabel(), $p->statusLabel(), $p->amount_cents, $p->fee_cents, $p->status === 'paid' ? $p->amount_cents - $p->fee_cents : 0], ';', '"', '');
            }
            fclose($out);
        }, 'vendas-demonstrativas.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
