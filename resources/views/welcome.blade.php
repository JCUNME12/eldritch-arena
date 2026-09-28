<x-layouts.app title="Eldritch Arena — Seu próximo encontro começa aqui">
 <section class="hero">
<div>
<p class="eyebrow">PARA QUEM VIVE O JOGO</p>
<h1>Seu deck.<br>Sua mesa.<br>
<em>Sua comunidade.</em>
</h1>
<p class="mt-6 max-w-lg text-lg leading-8 text-slate-400">Do primeiro duelo à próxima mesa de Commander. Encontre torneios, troque estratégias e leve suas partidas para a Arena.</p>
<div class="flex flex-wrap gap-3 mt-8">
<a class="arena-btn" href="{{ route('life-counter') }}">Abrir mesa de jogo ↗</a>
<a class="arena-btn-secondary" href="{{ auth()->check() ? route('dashboard') : route('register') }}">{{ auth()->check() ? 'Ir para meu painel' : 'Fazer parte da Arena' }}</a>
</div>
<p class="mt-6 text-xs tracking-widest text-slate-500">MAGIC: THE GATHERING · YU-GI-OH! · POKÉMON</p>
</div>
<div class="hero-art" aria-hidden="true">
<div class="identity-card">
<p>ESTRATÉGIA</p>
<span class="sigil">♧</span>
<p>CONHEÇA SEU JOGO</p>
</div>
<div class="identity-card identity-card-middle">
<p>ELDRITCH ARENA</p>
<span class="sigil">E</span>
<p>ENCONTRE SUA MESA</p>
</div>
<div class="identity-card">
<p>COMUNIDADE</p>
<span class="sigil">◇</span>
<p>JOGUE JUNTO</p>
</div>
</div>
</section>
 <section class="pb-12">
<a class="feature-row" href="{{ route('life-counter') }}">
<span>01</span>
<h2>Uma mesa, vários formatos.</h2>
<p>De 1 a 6 jogadores, vida, veneno e dano de comandante. Dados, moeda e histórico sempre à mão.</p>
</a>
<a class="feature-row" href="{{ route('tournaments.index') }}">
<span>02</span>
<h2>Encontre seu próximo torneio.</h2>
<p>Consulte formatos, vagas e horários. Organize um encontro ou reserve seu lugar para jogar.</p>
</a>
<a class="feature-row" href="{{ route('community') }}">
<span>03</span>
<h2>O jogo continua fora da mesa.</h2>
<p>Compartilhe listas e estratégias na comunidade, ou encontre a próxima carta da sua coleção nos classificados.</p>
</a>
</section>
</x-layouts.app>
