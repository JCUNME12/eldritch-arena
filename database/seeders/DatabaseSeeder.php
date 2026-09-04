<?php

namespace Database\Seeders;

use App\Models\CardListing;
use App\Models\CommunityComment;
use App\Models\CommunityReaction;
use App\Models\CommunityTopic;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $player = User::updateOrCreate(
            ['email' => 'jogador@eldritch.test'],
            [
                'name' => 'Kael Duelista',
                'password' => Hash::make('password'),
                'type' => 'player',
                'avatar_color' => '#A855F7',
                'premium_plan' => 'player_premium',
                'premium_active' => true,
                'premium_started_at' => now(),
            ]
        );

        $organizer = User::updateOrCreate(
            ['email' => 'loja@eldritch.test'],
            [
                'name' => 'Arcana Store',
                'password' => Hash::make('password'),
                'type' => 'organizer',
                'avatar_color' => '#22D3EE',
                'premium_plan' => 'loja_premium',
                'premium_active' => true,
                'premium_started_at' => now(),
            ]
        );

        $tournaments = [
            ['title' => 'Noite Commander Eldritch', 'game' => 'Magic: The Gathering', 'starts_at' => now()->addDays(3)->setTime(19, 30), 'prize' => 'Boosters + playmat exclusivo', 'entry_fee' => 35, 'slots' => 32, 'location' => 'Arcana Store - Centro', 'highlighted' => true],
            ['title' => 'Liga Pokémon Brasil', 'game' => 'Pokémon', 'starts_at' => now()->addDays(6)->setTime(14, 0), 'prize' => 'Kit treinador + créditos na loja', 'entry_fee' => 25, 'slots' => 24, 'location' => 'Arena Eldritch', 'highlighted' => true],
            ['title' => 'Duelo Yu-Gi-Oh! Neon Cup', 'game' => 'Yu-Gi-Oh!', 'starts_at' => now()->addDays(10)->setTime(16, 0), 'prize' => 'Deck box premium + R$ 300', 'entry_fee' => 30, 'slots' => 40, 'location' => 'Shopping Card Hall', 'highlighted' => false],
        ];

        foreach ($tournaments as $data) {
            Tournament::updateOrCreate(['title' => $data['title']], array_merge($data, [
                'organizer_id' => $organizer->id,
                'description' => 'Evento competitivo com pareamento presencial, ambiente seguro e premiações pensadas para jogadores de TCG.',
                'status' => Tournament::STATUS_PUBLISHED,
            ]));
        }

        $firstTournament = Tournament::first();
        if ($firstTournament) {
            TournamentRegistration::updateOrCreate([
                'user_id' => $player->id,
                'tournament_id' => $firstTournament->id,
            ], ['status' => 'confirmed']);
        }

        $cards = [
            ['user_id' => $organizer->id, 'name' => 'Sol Ring', 'game' => 'Magic', 'edition' => 'Commander', 'rarity' => 'Incomum', 'condition' => 'Excelente', 'description' => 'Carta pronta para Commander, ideal para acelerar mana nos primeiros turnos.', 'price' => 24.90, 'seller_name' => 'Arcana Store', 'seller_type' => 'loja', 'contact_email' => $organizer->email, 'highlighted' => true],
            ['user_id' => $organizer->id, 'name' => 'Charizard ex', 'game' => 'Pokémon', 'edition' => 'Scarlet & Violet', 'rarity' => 'Ultra Rara', 'condition' => 'Novo', 'description' => 'Carta protegida em sleeve desde a abertura do booster.', 'price' => 329.90, 'seller_name' => 'Arcana Store', 'seller_type' => 'loja', 'contact_email' => $organizer->email, 'highlighted' => true],
            ['user_id' => $organizer->id, 'name' => 'Dragão Branco de Olhos Azuis', 'game' => 'Yu-Gi-Oh', 'edition' => 'Legendary Collection', 'rarity' => 'Secreta', 'condition' => 'Excelente', 'description' => 'Clássica carta de colecionador, indicada para vitrine ou deck temático.', 'price' => 189.90, 'seller_name' => 'Arcana Store', 'seller_type' => 'loja', 'contact_email' => $organizer->email, 'highlighted' => true],
            ['user_id' => $player->id, 'name' => 'Lightning Bolt', 'game' => 'Magic', 'edition' => 'Masters', 'rarity' => 'Comum', 'condition' => 'Bom', 'description' => 'Anúncio de player para venda rápida. Possui marcas leves de uso.', 'price' => 8.50, 'seller_name' => $player->name, 'seller_type' => 'player', 'contact_email' => $player->email, 'highlighted' => true],
            ['user_id' => $player->id, 'name' => 'Pikachu Promo', 'game' => 'Pokémon', 'edition' => 'Promo', 'rarity' => 'Promo', 'condition' => 'Excelente', 'description' => 'Carta promocional guardada em sleeve rígido.', 'price' => 79.90, 'seller_name' => $player->name, 'seller_type' => 'player', 'contact_email' => $player->email, 'highlighted' => true],
            ['user_id' => $player->id, 'name' => 'Mago Negro', 'game' => 'Yu-Gi-Oh', 'edition' => 'Starter Deck', 'rarity' => 'Ultra Rara', 'condition' => 'Excelente', 'description' => 'Carta nostálgica para fãs de Yu-Gi-Oh, bem conservada.', 'price' => 145.00, 'seller_name' => $player->name, 'seller_type' => 'player', 'contact_email' => $player->email, 'highlighted' => true],
        ];

        foreach ($cards as $card) {
            CardListing::updateOrCreate(['name' => $card['name'], 'game' => $card['game']], $card);
        }

        $this->seedCommunity($organizer, $player);
    }

    private function seedCommunity(User $organizer, User $player): void
    {
        $topics = [
            [
                'user_id' => $organizer->id,
                'title' => 'Bem-vindos à comunidade Eldritch Arena',
                'category' => 'Avisos',
                'body' => 'Este espaço reúne jogadores, lojistas e organizadores. Use os tópicos para tirar dúvidas, divulgar eventos, discutir decks, combinar partidas e melhorar listas competitivas.',
                'is_pinned' => true,
                'comments' => [
                    ['user_id' => $player->id, 'body' => 'Muito bom ter uma área própria para conversar sobre torneios e marketplace. Vai ajudar bastante a comunidade local.'],
                ],
                'reactions' => [
                    ['user_id' => $player->id, 'type' => 'heart'],
                    ['user_id' => $organizer->id, 'type' => 'like'],
                ],
            ],
            [
                'user_id' => $player->id,
                'title' => 'Qual deck vocês indicam para começar em torneios locais?',
                'category' => 'Decks',
                'body' => 'Estou montando uma lista inicial para jogar presencialmente e queria sugestões de decks com bom custo-benefício. A ideia é competir sem gastar tanto logo no início, mas ainda ter chance contra jogadores mais experientes.',
                'is_pinned' => false,
                'comments' => [
                    ['user_id' => $organizer->id, 'body' => 'Uma boa estratégia é começar por decks consistentes e depois melhorar aos poucos com cartas do marketplace. Também vale acompanhar os torneios da loja para entender o meta local.'],
                ],
                'reactions' => [
                    ['user_id' => $organizer->id, 'type' => 'idea'],
                ],
            ],
            [
                'user_id' => $organizer->id,
                'title' => 'Dicas para comprar cartas com segurança no marketplace',
                'category' => 'Marketplace',
                'body' => 'Confiram sempre o estado da carta, a reputação do vendedor e o contato informado. Quando possível, negociem em eventos ou lojas parceiras e solicitem fotos atuais antes de fechar a compra.',
                'is_pinned' => false,
                'comments' => [
                    ['user_id' => $player->id, 'body' => 'Boa dica. Também acho importante pedir foto da carta antes de fechar negócio.'],
                ],
                'reactions' => [
                    ['user_id' => $player->id, 'type' => 'like'],
                ],
            ],
            [
                'user_id' => $player->id,
                'title' => 'Como vocês se preparam para torneios presenciais?',
                'category' => 'Torneios',
                'body' => 'Queria saber como a galera organiza side deck, sleeves, lista de cartas e rotina de treino antes dos campeonatos. A plataforma poderia ajudar bastante reunindo torneio, comunidade e marketplace no mesmo lugar.',
                'is_pinned' => false,
                'comments' => [
                    ['user_id' => $organizer->id, 'body' => 'Normalmente recomendamos chegar antes, conferir a lista e testar matchups principais durante a semana.'],
                ],
                'reactions' => [
                    ['user_id' => $organizer->id, 'type' => 'fire'],
                ],
            ],
        ];

        foreach ($topics as $topicData) {
            $comments = $topicData['comments'];
            $reactions = $topicData['reactions'];
            unset($topicData['comments'], $topicData['reactions']);

            $topic = CommunityTopic::updateOrCreate(
                ['title' => $topicData['title']],
                $topicData
            );

            foreach ($comments as $commentData) {
                CommunityComment::updateOrCreate([
                    'community_topic_id' => $topic->id,
                    'user_id' => $commentData['user_id'],
                ], [
                    'body' => $commentData['body'],
                ]);
            }

            foreach ($reactions as $reactionData) {
                CommunityReaction::firstOrCreate([
                    'community_topic_id' => $topic->id,
                    'user_id' => $reactionData['user_id'],
                    'type' => $reactionData['type'],
                ]);
            }
        }
    }
}
