<?php

namespace Tests\Feature;

use App\Models\CardListing;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_cannot_grant_admin_even_with_forged_fields(): void
    {
        $this->post('/cadastro', ['name' => 'Nova conta', 'email' => 'new@example.test', 'password' => 'long-test-password', 'password_confirmation' => 'long-test-password', 'type' => 'organizer', 'is_admin' => true])->assertSessionHasNoErrors();
        $this->assertFalse(User::where('email', 'new@example.test')->firstOrFail()->isAdmin());
        $this->get('/admin')->assertForbidden();
    }

    public function test_admin_can_open_store_without_changing_player_profile(): void
    {
        $user = User::factory()->create(['type' => 'player']);
        $this->artisan('arena:admin', ['email' => $user->email])->assertSuccessful();
        $this->actingAs($user->fresh())->get('/loja')->assertOk();
        $this->assertSame('player', $user->fresh()->type);
    }

    private function owner(): User
    {
        $u = User::factory()->create(['type' => 'organizer']);
        $this->actingAs($u);
        $this->put('/loja/perfil', ['name' => 'Loja de teste', 'contact_email' => 'loja@example.test'])->assertSessionHasNoErrors();

        return $u;
    }

    private function data(array $extra = []): array
    {
        return array_merge(['sku' => 'MTG-01', 'name' => 'Carta estoque', 'game' => 'Magic', 'edition' => 'Teste', 'rarity' => 'Rara', 'condition' => 'Novo', 'cost' => '8.25', 'price' => '12.50', 'quantity' => 5, 'minimum_quantity' => 2], $extra);
    }

    private function item(): InventoryItem
    {
        $this->post('/loja/produtos', $this->data())->assertSessionHasNoErrors();

        return InventoryItem::latest('id')->firstOrFail();
    }

    public function test_store_setup_forms_and_inventory_history_render(): void
    {
        $u = User::factory()->create(['type' => 'organizer']);
        $this->actingAs($u)->get('/loja')->assertOk()->assertSee('Prepare sua loja');
        $this->owner();
        $item = $this->item();
        $this->get('/loja')->assertOk()->assertSee('Carta estoque')->assertSee('41,25');
        $this->get('/loja/produtos/novo')->assertOk();
        $this->get('/loja/produtos/'.$item->id)->assertOk()->assertSee('Estoque inicial');
        $this->get('/loja/produtos/'.$item->id.'/editar')->assertOk();
        $this->assertDatabaseHas('stock_movements', ['inventory_item_id' => $item->id, 'delta' => 5, 'before_quantity' => 0, 'after_quantity' => 5]);
    }

    public function test_guests_players_and_other_store_owners_cannot_access_private_inventory(): void
    {
        $this->get('/loja')->assertRedirect('/login');
        $owner = $this->owner();
        $item = $this->item();
        $this->actingAs(User::factory()->create(['type' => 'player']))->get('/loja')->assertForbidden();
        $this->actingAs(User::factory()->create(['type' => 'organizer']));
        $this->get('/loja/produtos/'.$item->id)->assertNotFound();
        $this->put('/loja/produtos/'.$item->id, $this->data())->assertNotFound();
        $this->post('/loja/produtos/'.$item->id.'/movimentacoes', [])->assertNotFound();
        $this->patch('/loja/produtos/'.$item->id.'/publicacao', ['published' => 1])->assertNotFound();
        $this->patch('/loja/produtos/'.$item->id.'/arquivo', ['archived' => 1])->assertNotFound();
        $this->assertEquals(5, $item->fresh()->quantity);
    }

    public function test_negative_stock_and_duplicate_submissions_are_prevented(): void
    {
        $this->owner();
        $item = $this->item();
        $url = '/loja/produtos/'.$item->id.'/movimentacoes';
        $data = ['direction' => 'out', 'quantity' => 6, 'reason' => 'Venda', 'request_id' => (string) Str::uuid()];
        $this->post($url, $data)->assertSessionHasErrors('quantity');
        $this->assertEquals(5, $item->fresh()->quantity);
        $data['quantity'] = 3;
        $this->post($url, $data)->assertSessionHasNoErrors();
        $this->post($url, $data)->assertSessionHasNoErrors();
        $this->assertEquals(2, $item->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 2);
        $data['quantity'] = 1;
        $this->post($url, $data)->assertSessionHasErrors('quantity');
        $this->assertEquals(2, $item->fresh()->quantity);
    }

    public function test_marketplace_visibility_follows_stock_and_pause_and_does_not_expose_cost(): void
    {
        $owner = $this->owner();
        $item = $this->item();
        $base = '/loja/produtos/'.$item->id;
        $this->patch($base.'/publicacao', ['published' => 1])->assertSessionHasNoErrors();
        $listing = $item->listing()->firstOrFail();
        $this->assertEquals(1, CardListing::available()->count());
        $this->get('/marketplace/'.$listing->id)->assertOk()->assertDontSee('8.25')->assertDontSee('8,25');
        $this->post($base.'/movimentacoes', ['direction' => 'out', 'quantity' => 5, 'reason' => 'Venda confirmada', 'request_id' => (string) Str::uuid()])->assertSessionHasNoErrors();
        $this->assertEquals(0, CardListing::available()->count());
        $this->actingAs(User::factory()->create())->get('/marketplace/'.$listing->id)->assertNotFound();
        $this->actingAs($owner)->post($base.'/movimentacoes', ['direction' => 'in', 'quantity' => 2, 'reason' => 'Reposição', 'request_id' => (string) Str::uuid()])->assertSessionHasNoErrors();
        $this->assertEquals(1, CardListing::available()->count());
        $this->patch($base.'/publicacao', ['published' => 0])->assertSessionHasNoErrors();
        $this->assertEquals(0, CardListing::available()->count());
    }

    public function test_publishing_twice_uses_one_listing_and_metadata_stays_synchronized(): void
    {
        $this->owner();
        $item = $this->item();
        $base = '/loja/produtos/'.$item->id;
        $this->patch($base.'/publicacao', ['published' => 1]);
        $this->patch($base.'/publicacao', ['published' => 1]);
        $this->assertDatabaseCount('card_listings', 1);
        $this->put($base, $this->data(['name' => 'Novo nome', 'price' => '19.99', 'quantity' => 999, 'published' => 1, 'store_id' => 999]))->assertSessionHasNoErrors();
        $this->assertEquals(5, $item->fresh()->quantity);
        $this->assertDatabaseHas('card_listings', ['name' => 'Novo nome', 'price' => '19.99']);
        $this->put('/loja/perfil', ['name' => 'Loja atualizada', 'contact_email' => 'nova@example.test'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('card_listings', ['seller_name' => 'Loja atualizada', 'contact_email' => 'nova@example.test']);
        $listing = $item->listing()->firstOrFail();
        $this->patch('/marketplace/'.$listing->id, [])->assertForbidden();
        $this->delete('/marketplace/'.$listing->id)->assertForbidden();
    }

    public function test_archiving_requires_empty_stock_and_preserves_history(): void
    {
        $this->owner();
        $item = $this->item();
        $base = '/loja/produtos/'.$item->id;
        $this->patch($base.'/arquivo', ['archived' => 1])->assertSessionHasErrors('archived');
        $this->post($base.'/movimentacoes', ['direction' => 'out', 'quantity' => 5, 'reason' => 'Avaria', 'request_id' => (string) Str::uuid()]);
        $this->patch($base.'/arquivo', ['archived' => 1])->assertSessionHasNoErrors();
        $this->assertTrue($item->fresh()->archived);
        $this->post($base.'/movimentacoes', ['direction' => 'in', 'quantity' => 1, 'reason' => 'Compra', 'request_id' => (string) Str::uuid()])->assertSessionHasErrors('quantity');
        $this->patch($base.'/arquivo', ['archived' => 0])->assertSessionHasNoErrors();
        $this->assertFalse($item->fresh()->archived);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_sku_is_unique_per_store_and_uppercase_and_invalid_money_is_rejected(): void
    {
        $this->owner();
        $this->item();
        $this->post('/loja/produtos', $this->data(['sku' => ' mtg-01 ']))->assertSessionHasErrors('sku');
        $this->post('/loja/produtos', $this->data(['sku' => 'NEW', 'price' => '12.345']))->assertSessionHasErrors('price');
        $this->owner();
        $this->item();
        $this->assertDatabaseCount('inventory_items', 2);
    }

    public function test_admin_grant_is_audited_and_cannot_be_self_assigned_by_profile(): void
    {
        $u = User::factory()->create(['type' => 'player']);
        $password = $u->password;
        $this->actingAs($u)->get('/admin')->assertForbidden();
        $this->patch('/perfil/tipo', ['type' => 'admin', 'is_admin' => true])->assertSessionHasErrors('type');
        $this->patch('/perfil/tipo', ['type' => 'organizer', 'is_admin' => true])->assertSessionHasNoErrors();
        $this->assertFalse($u->fresh()->isAdmin());
        $this->artisan('arena:admin', ['email' => $u->email])->assertSuccessful();
        $this->assertTrue($u->fresh()->isAdmin());
        $this->assertSame($password, $u->fresh()->password);
        $this->assertDatabaseCount('admin_access_logs', 1);
        $this->artisan('arena:admin', ['email' => $u->email])->assertSuccessful();
        $this->assertDatabaseCount('admin_access_logs', 1);
        $this->actingAs($u->fresh())->get('/admin')->assertOk()->assertSee('Visão da plataforma');
        $this->artisan('arena:admin', ['email' => $u->email, '--revoke' => true])->assertSuccessful();
        $this->assertFalse($u->fresh()->isAdmin());
    }
}
