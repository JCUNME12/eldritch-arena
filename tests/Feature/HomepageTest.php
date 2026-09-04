<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_the_public_presentation(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('Sua próxima partida começa aqui.')
            ->assertSee('Criar minha conta')
            ->assertSee('Já tenho uma conta')
            ->assertDontSee('Sua arena');
    }

    public function test_authenticated_user_sees_a_personal_homepage(): void
    {
        $user = User::factory()->create([
            'name' => 'João da Arena',
            'type' => 'player',
        ]);

        $response = $this->actingAs($user)->get(route('home.auth'));

        $response
            ->assertOk()
            ->assertSee('Olá,')
            ->assertSee('João')
            ->assertSee('Jogador conectado')
            ->assertSee('Abrir contador de vida')
            ->assertSee('Resumo da conta')
            ->assertDontSee('Criar minha conta');
    }

    public function test_admin_sees_the_admin_shortcut_on_the_homepage(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('home.auth'));

        $response
            ->assertOk()
            ->assertSee('Administrador conectado')
            ->assertSee('Painel administrativo')
            ->assertSee(route('admin.index'), false);
    }

    public function test_account_homepage_requires_authentication(): void
    {
        $this->get(route('home.auth'))
            ->assertRedirect(route('login'));
    }
}
