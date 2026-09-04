<?php

namespace Tests\Feature;

use App\Models\CommunityTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_react_to_a_topic(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('community.store'), [
            'title' => 'Como preparar meu deck?',
            'category' => 'Decks',
            'body' => 'Quero sugestões para preparar um deck competitivo para o próximo evento.',
        ]);

        $topic = CommunityTopic::query()->sole();
        $response->assertRedirect(route('community.show', $topic));

        $this->actingAs($user)->post(route('community.react', $topic), ['type' => 'fire']);
        $this->assertDatabaseHas('community_reactions', [
            'community_topic_id' => $topic->id,
            'user_id' => $user->id,
            'type' => 'fire',
        ]);

        $this->actingAs($user)->post(route('community.react', $topic), ['type' => 'fire']);
        $this->assertDatabaseCount('community_reactions', 0);
    }

    public function test_unknown_topic_category_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('community.store'), [
            'title' => 'Categoria desconhecida',
            'category' => 'Qualquer coisa',
            'body' => 'Este conteúdo tem tamanho suficiente para passar pela validação do corpo.',
        ])->assertSessionHasErrors('category');
    }

    public function test_user_cannot_edit_another_users_topic(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $topic = CommunityTopic::create([
            'user_id' => $owner->id,
            'title' => 'Tópico protegido',
            'category' => 'Discussão',
            'body' => 'Conteúdo original que somente o proprietário deveria conseguir alterar.',
            'is_pinned' => false,
        ]);

        $this->actingAs($otherUser)->put(route('community.update', $topic), [
            'title' => 'Tentativa de alteração',
            'category' => 'Discussão',
            'body' => 'Conteúdo alterado por uma pessoa que não possui permissão para fazer isso.',
        ])->assertForbidden();

        $this->assertDatabaseHas('community_topics', [
            'id' => $topic->id,
            'title' => 'Tópico protegido',
        ]);
    }
}
