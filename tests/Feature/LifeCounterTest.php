<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifeCounterTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_the_multi_game_life_counter(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->get('/contador-de-vida');

        $response
            ->assertOk()
            ->assertSee('Escolha o jogo')
            ->assertSee('x-data="lifeCounter"', false)
            ->assertSee('Iniciar partida')
            ->assertSee('Dano de comandante')
            ->assertSee('Rolar D20');
    }

    public function test_life_counter_requires_authentication(): void
    {
        $this->get('/contador-de-vida')->assertRedirect('/login');
    }
}
