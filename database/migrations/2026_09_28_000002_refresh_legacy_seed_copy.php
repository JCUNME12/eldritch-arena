<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function copies(): array
    {
        return [
            'Este espaço foi criado para centralizar conversas entre players, lojistas e organizadores. Use os tópicos para tirar dúvidas, divulgar eventos, discutir decks, combinar partidas, melhorar listas competitivas e demonstrar para a banca como a comunidade funciona dentro do sistema.' => 'Este espaço foi criado para centralizar conversas entre players, lojistas e organizadores. Use os tópicos para tirar dúvidas, divulgar eventos, discutir decks, combinar partidas, melhorar listas competitivas e encontrar novos parceiros de jogo.',
            'Confiram sempre o estado da carta, a reputação do vendedor, o contato informado e, quando possível, negociem em eventos ou lojas parceiras. Para o TCC, essa discussão demonstra como marketplace e comunidade se conectam dentro do Eldritch Arena.' => 'Confiram sempre o estado da carta, a reputação do vendedor, o contato informado e, quando possível, negociem em eventos ou lojas parceiras. Combine os detalhes da negociação diretamente com o vendedor.',
        ];
    }

    public function up(): void
    {
        foreach ($this->copies() as $old => $new) {
            DB::table('community_topics')->where('body', $old)->update(['body' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->copies() as $old => $new) {
            DB::table('community_topics')->where('body', $new)->update(['body' => $old]);
        }
    }
};
