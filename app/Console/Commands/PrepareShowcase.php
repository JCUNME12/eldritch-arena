<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrepareShowcase extends Command
{
    protected $signature = 'arena:showcase {presenter : E-mail de uma conta existente}';

    protected $description = 'Cria duas lojas isoladas para a mostra, sem alterar lojas reais ou redefinir demonstrações existentes';

    public function handle(): int
    {
        $presenter = User::where('email', $this->argument('presenter'))->first();
        if (! $presenter) {
            $this->error('Conta do apresentador não encontrada.');

            return self::FAILURE;
        }
        DB::transaction(function () use ($presenter) {
            User::whereKey($presenter->id)->lockForUpdate()->firstOrFail();
            foreach (['free' => 'Mesa Inicial', 'plus' => 'Arcana Prime'] as $plan => $name) {
                $email = 'mostra-'.$presenter->id.'-'.$plan.'@eldritch.invalid';
                $user = User::where('email', $email)->first();
                if ($user) {
                    if (! Store::where('user_id', $user->id)->where('is_demo', true)->where('demo_presenter_id', $presenter->id)->exists()) {
                        throw new \RuntimeException('Conta reservada já existe fora da demonstração.');
                    }

                    continue;
                }
                $user = User::create(['name' => $name.' · Demo', 'email' => $email, 'password' => Str::random(64), 'type' => 'organizer', 'premium_active' => $plan === 'plus', 'premium_plan' => $plan === 'plus' ? 'loja_premium' : 'free']);
                $store = new Store(['name' => $name, 'contact_email' => $email, 'description' => 'Loja fictícia para apresentação acadêmica.']);
                $store->user_id = $user->id;
                $store->is_demo = true;
                $store->demo_presenter_id = $presenter->id;
                $store->save();
                foreach ([['SOL-01', 'Sol Ring', 'Magic', '15.00', '24.90', 12], ['BOLT-01', 'Lightning Bolt', 'Magic', '4.00', '8.50', 2], ['DARK-01', 'Mago Negro', 'Yu-Gi-Oh', '80.00', '145.00', 6]] as [$sku, $title, $game, $cost, $price, $quantity]) {
                    $item = $store->items()->create(['sku' => $sku, 'name' => $title, 'game' => $game, 'edition' => 'Acervo demonstrativo', 'rarity' => 'Rara', 'condition' => 'Excelente', 'cost' => $cost, 'price' => $price, 'minimum_quantity' => 3]);
                    app(InventoryService::class)->move($item, $presenter->id, $quantity, 'DEMO · estoque inicial', (string) Str::uuid());
                }
            }
        });
        $this->info('Lojas disponíveis em /mostra para o apresentador autenticado. Nenhuma senha compartilhada foi criada.');

        return self::SUCCESS;
    }
}
