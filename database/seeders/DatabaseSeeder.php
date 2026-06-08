<?php

namespace Database\Seeders;

use App\Models\CardListing;
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
            ]
        );

        $organizer = User::updateOrCreate(
            ['email' => 'loja@eldritch.test'],
            [
                'name' => 'Arcana Store',
                'password' => Hash::make('password'),
                'type' => 'organizer',
                'avatar_color' => '#22D3EE',
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
            ['name' => 'Sol Ring', 'game' => 'Magic', 'rarity' => 'Incomum', 'condition' => 'Near Mint', 'price' => 24.90, 'seller_name' => 'Arcana Store', 'highlighted' => true],
            ['name' => 'Charizard ex', 'game' => 'Pokémon', 'rarity' => 'Ultra Rara', 'condition' => 'Mint', 'price' => 329.90, 'seller_name' => 'Poké Vault', 'highlighted' => true],
            ['name' => 'Dragão Branco de Olhos Azuis', 'game' => 'Yu-Gi-Oh', 'rarity' => 'Secreta', 'condition' => 'Excelente', 'price' => 189.90, 'seller_name' => 'Duel Shop', 'highlighted' => true],
            ['name' => 'Lightning Bolt', 'game' => 'Magic', 'rarity' => 'Comum', 'condition' => 'Good', 'price' => 8.50, 'seller_name' => 'Arcana Store', 'highlighted' => false],
            ['name' => 'Pikachu Promo', 'game' => 'Pokémon', 'rarity' => 'Promo', 'condition' => 'Near Mint', 'price' => 79.90, 'seller_name' => 'Poké Vault', 'highlighted' => false],
            ['name' => 'Mago Negro', 'game' => 'Yu-Gi-Oh', 'rarity' => 'Ultra Rara', 'condition' => 'Excelente', 'price' => 145.00, 'seller_name' => 'Duel Shop', 'highlighted' => false],
        ];

        foreach ($cards as $card) {
            CardListing::updateOrCreate(['name' => $card['name'], 'game' => $card['game']], $card);
        }
    }
}
