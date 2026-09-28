<?php

namespace App\Http\Controllers;

use App\Models\CardListing;
use App\Models\InventoryItem;
use App\Models\Store;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    private function store(Request $r): ?Store
    {
        return Store::where('user_id', $r->user()->id)->first();
    }

    private function owned(Request $r, InventoryItem $item): void
    {
        abort_unless($item->store->user_id === $r->user()->id, 404);
    }

    public function index(Request $r)
    {
        $filters = $r->validate(['q' => ['nullable', 'string', 'max:100'], 'stock' => ['nullable', 'in:low,zero,archived']]);
        $store = $this->store($r);
        if (! $store) {
            return view('store.setup');
        }
        $items = $store->items()->where('archived', ($filters['stock'] ?? '') === 'archived')
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where(fn ($s) => $s->whereLike('name', '%'.$v.'%')->orWhereLike('sku', '%'.$v.'%')))
            ->when(($filters['stock'] ?? '') === 'low', fn ($q) => $q->whereColumn('quantity', '<=', 'minimum_quantity'))
            ->when(($filters['stock'] ?? '') === 'zero', fn ($q) => $q->where('quantity', 0))
            ->orderBy('name')->paginate(15)->withQueryString();
        $stats = $store->items()->where('archived', false)->selectRaw('COUNT(*) as products, COALESCE(SUM(quantity),0) as units, COALESCE(SUM(quantity * cost),0) as cost_value')->first();
        $low = $store->items()->where('archived', false)->whereColumn('quantity', '<=', 'minimum_quantity')->count();

        return view('store.index', compact('store', 'items', 'stats', 'low'));
    }

    public function saveStore(Request $r)
    {
        $data = $r->validate(['name' => ['required', 'string', 'max:120'], 'contact_email' => ['required', 'email', 'max:255'], 'description' => ['nullable', 'string', 'max:1000']]);
        DB::transaction(function () use ($r, $data) {
            $r->user()->newQuery()->whereKey($r->user()->id)->lockForUpdate()->firstOrFail();
            $store = $this->store($r) ?? new Store;
            $store->fill($data);
            $store->user_id = $r->user()->id;
            $store->save();
            CardListing::where('user_id', $r->user()->id)->whereHas('inventoryItem', fn ($q) => $q->where('store_id', $store->id))->update(['seller_name' => $store->name, 'contact_email' => $store->contact_email]);
        });

        return redirect()->route('store.index')->with('status', 'Dados da loja salvos.');
    }

    public function create(Request $r)
    {
        $store = $this->store($r);
        if (! $store) {
            return redirect()->route('store.index');
        }

return view('store.form', ['item' => new InventoryItem, 'store' => $store]);
    }

    private function data(Request $r, Store $store, ?InventoryItem $item = null): array
    {
        $r->merge(['sku' => is_string($r->sku) ? strtoupper(trim($r->sku)) : $r->sku]);

        return $r->validate([
            'sku' => ['required', 'string', 'max:60', Rule::unique('inventory_items')->where('store_id', $store->id)->ignore($item?->id)],
            'name' => ['required', 'string', 'max:120'], 'game' => ['required', 'in:Magic,Pokémon,Yu-Gi-Oh'],
            'edition' => ['nullable', 'string', 'max:120'], 'rarity' => ['required', 'in:Comum,Incomum,Rara,Mítica,Ultra Rara,Secreta,Promo'],
            'condition' => ['required', 'in:Novo,Excelente,Bom,Usado,Danificado'],
            'description' => ['nullable', 'string', 'max:1000'], 'image_url' => ['nullable', 'url:http,https', 'max:500'],
            'cost' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'], 'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
            'minimum_quantity' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);
    }

    public function saveItem(Request $r, InventoryService $service)
    {
        $store = $this->store($r);
        abort_unless($store, 404);
        $data = $this->data($r, $store);
        $r->validate(['quantity' => ['required', 'integer', 'min:0', 'max:999999']]);
        $item = DB::transaction(function () use ($r, $store, $data, $service) {
            $item = $store->items()->create($data);
            if ((int) $r->quantity > 0) {
                $service->move($item, $r->user()->id, (int) $r->quantity, 'Estoque inicial', (string) Str::uuid());
            }

            return $item;
        });

        return redirect()->route('store.show', $item)->with('status', 'Produto cadastrado no estoque.');
    }

    public function show(Request $r, InventoryItem $item)
    {
        $this->owned($r, $item);

        return view('store.show', ['item' => $item, 'movements' => $item->movements()->with('user')->latest('id')->paginate(20)]);
    }

    public function edit(Request $r, InventoryItem $item)
    {
        $this->owned($r, $item);

        return view('store.form', ['item' => $item, 'store' => $item->store]);
    }

    public function update(Request $r, InventoryItem $item, InventoryService $service)
    {
        $this->owned($r, $item);
        $data = $this->data($r, $item->store, $item);
        DB::transaction(function () use ($item, $data, $service) {
            $locked = InventoryItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
            $locked->update($data);
            $service->syncListing($locked);
        });

        return redirect()->route('store.show', $item)->with('status', 'Produto atualizado.');
    }

    public function move(Request $r, InventoryItem $item, InventoryService $service)
    {
        $this->owned($r, $item);
        $data = $r->validate(['direction' => ['required', 'in:in,out'], 'quantity' => ['required', 'integer', 'min:1', 'max:999999'], 'reason' => ['required', 'string', 'max:255'], 'request_id' => ['required', 'uuid']]);
        $service->move($item, $r->user()->id, (int) $data['quantity'] * ($data['direction'] === 'in' ? 1 : -1), $data['reason'], $data['request_id']);

        return redirect()->route('store.show', $item)->with('status', 'Movimentação registrada.');
    }

    public function publish(Request $r, InventoryItem $item, InventoryService $service)
    {
        $this->owned($r, $item);
        $r->validate(['published' => ['required', 'boolean']]);
        DB::transaction(function () use ($r, $item, $service) {
            $locked = InventoryItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
            if ($r->boolean('published') && ($locked->quantity === 0 || $locked->archived)) {
                throw ValidationException::withMessages(['published' => 'Adicione estoque e reative o produto antes de publicar.']);
            }
            $locked->published = $r->boolean('published');
            $locked->save();
            $service->syncListing($locked);
        });

        return back()->with('status', $r->boolean('published') ? 'Produto publicado no marketplace.' : 'Anúncio pausado.');
    }

    public function archive(Request $r, InventoryItem $item)
    {
        $this->owned($r, $item);
        $r->validate(['archived' => ['required', 'boolean']]);
        DB::transaction(function () use ($r, $item) {
            $locked = InventoryItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
            if ($r->boolean('archived') && $locked->quantity > 0) {
                throw ValidationException::withMessages(['archived' => 'Registre a saída do saldo antes de arquivar.']);
            }
            $locked->archived = $r->boolean('archived');
            $locked->published = false;
            $locked->save();
        });

        return back()->with('status',$r->boolean('archived') ? 'Produto arquivado; histórico preservado.' : 'Produto reativado, com anúncio pausado.');
    }
}
