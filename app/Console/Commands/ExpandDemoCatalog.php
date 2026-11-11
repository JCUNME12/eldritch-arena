<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpandDemoCatalog extends Command
{
    protected $signature = 'arena:demo-catalog {presenter}';
    protected $description = 'Acrescenta cartas ficticiamente precificadas às lojas demo sem repor ou alterar itens existentes';

    public function handle(): int
    {
        $actor = User::where('email', $this->argument('presenter'))->firstOrFail();
        $stores = Store::where('is_demo', true)->where('demo_presenter_id', $actor->id)->orderBy('id')->get();
        if ($stores->isEmpty()) {
            $this->error('Prepare primeiro as lojas com arena:showcase.');
            return self::FAILURE;
        }
        $catalog = [
            ['ARCANE', 'Arcane Signet', 'Magic', '12.90'],
            ['COMMAND', 'Command Tower', 'Magic', '9.90'],
            ['COUNTER', 'Counterspell', 'Magic', '7.50'],
            ['SWORDS', 'Swords to Plowshares', 'Magic', '14.90'],
            ['CULTIVATE', 'Cultivate', 'Magic', '4.90'],
            ['NEGATE', 'Negate', 'Magic', '2.50'],
            ['OPT', 'Opt', 'Magic', '1.90'],
            ['PATH', 'Path to Exile', 'Magic', '19.90'],
            ['BEAST', 'Beast Within', 'Magic', '8.90'],
            ['FARSEEK', 'Farseek', 'Magic', '6.50'],
            ['BLUEEYES', 'Blue-Eyes White Dragon', 'Yu-Gi-Oh', '39.90'],
            ['REDEYES', 'Red-Eyes Black Dragon', 'Yu-Gi-Oh', '29.90'],
            ['ASH', 'Ash Blossom & Joyous Spring', 'Yu-Gi-Oh', '24.90'],
            ['VEILER', 'Effect Veiler', 'Yu-Gi-Oh', '9.90'],
            ['RAIGEKI', 'Raigeki', 'Yu-Gi-Oh', '14.90'],
            ['REBORN', 'Monster Reborn', 'Yu-Gi-Oh', '7.90'],
            ['POLY', 'Polymerization', 'Yu-Gi-Oh', '5.90'],
            ['MIRROR', 'Mirror Force', 'Yu-Gi-Oh', '8.90'],
            ['CYBER', 'Cyber Dragon', 'Yu-Gi-Oh', '12.90'],
            ['DARKHOLE', 'Dark Hole', 'Yu-Gi-Oh', '4.90'],
        ];
        $created = DB::transaction(function () use ($actor, $stores, $catalog) {
            $created = 0;
            foreach ($stores as $store) {
                Store::whereKey($store->id)->lockForUpdate()->firstOrFail();
                foreach ($catalog as $index => [$sku, $name, $game, $price]) {
                    $sku = 'DEMO-'.$sku;
                    if ($store->items()->where('sku', $sku)->exists()) { continue; }
                    $item = $store->items()->create([
                        'sku' => $sku, 'name' => $name, 'game' => $game, 'price' => $price,
                        'cost' => number_format((float) $price * .55, 2, '.', ''),
                        'edition' => 'Acervo demonstrativo', 'rarity' => 'Comum', 'condition' => 'Excelente',
                        'minimum_quantity' => 3,
                        'description' => 'Item de demonstração. Preço, edição, raridade e estoque ilustrativos; não representa oferta comercial.',
                    ]);
                    app(InventoryService::class)->move($item, $actor->id, 4 + ($index % 9), 'DEMO · ampliação de catálogo', (string) Str::uuid());
                    $created++;
                }
            }
            return $created;
        });
        $this->info("{$created} produtos demo adicionados. Itens anteriores preservados.");
        return self::SUCCESS;
    }
}
