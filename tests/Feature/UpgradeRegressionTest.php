<?php

namespace Tests\Feature;

use App\Models\CommunityTopic;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpgradeRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_and_guest_protection(): void
    {
        foreach (['/', '/login', '/cadastro', '/up'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_registration_login_and_logout(): void
    {
        $this->post('/cadastro', [
            'name' => 'Upgrade Player', 'email' => 'upgrade@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123', 'type' => 'player',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->post('/login', ['email' => 'upgrade@example.test', 'password' => 'password123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_authenticated_pages_for_both_profile_types(): void
    {
        $this->seed();
        foreach (['player', 'organizer'] as $type) {
            $this->actingAs(User::factory()->create(['type' => $type]));
            foreach (['/dashboard', '/torneios', '/torneios/criar', '/marketplace', '/marketplace/vender',
                '/comunidade', '/comunidade/novo', '/premium', '/contador-de-vida'] as $url) {
                $this->get($url)->assertOk();
            }
        }
    }

    public function test_tournament_creation_and_registration(): void
    {
        $organizer = User::factory()->create(['type' => 'organizer']);
        $this->actingAs($organizer)->post('/torneios', [
            'title' => 'Upgrade Cup', 'game' => 'Magic', 'format' => 'Standard', 'starts_at' => '2027-01-10 18:00:00',
            'prize' => 'Cards', 'entry_fee' => 10, 'slots' => 16, 'location' => 'Arena',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $tournament = Tournament::sole();
        $this->get('/torneios/'.$tournament->id)->assertOk();
        $player = User::factory()->create();
        $this->actingAs($player)->post('/torneios/'.$tournament->id.'/inscrever')->assertRedirect();
        $this->assertDatabaseHas('tournament_registrations', ['tournament_id' => $tournament->id, 'user_id' => $player->id]);
    }

    public function test_community_write_read_reaction_and_authorization(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/comunidade', [
            'title' => 'Upgrade discussion', 'category' => 'Decks', 'body' => 'A sufficiently long discussion about cards.',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $topic = CommunityTopic::sole();
        $url = '/comunidade/'.$topic->id;
        $this->get($url)->assertOk();
        $this->post($url.'/comentarios', ['body' => 'Test comment'])->assertRedirect();
        $this->post($url.'/reacoes', ['type' => 'like'])->assertRedirect();
        $this->assertDatabaseHas('community_reactions', ['community_topic_id' => $topic->id, 'type' => 'like']);
        $this->actingAs(User::factory()->create())->delete($url)->assertForbidden();
        $this->actingAs($owner)->delete($url)->assertRedirect();
        $this->assertDatabaseMissing('community_topics', ['id' => $topic->id]);
    }
}
