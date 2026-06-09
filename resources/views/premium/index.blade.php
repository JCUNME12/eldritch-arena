<x-layouts.app title="Premium — Eldritch Arena">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="font-bold text-arena-gold">Monetização simulada</p>
            <h1 class="arena-section-title">Planos Premium</h1>
            <p class="mt-2 max-w-3xl text-slate-400">Esta tela demonstra como o Eldritch Arena poderia monetizar o aplicativo com assinaturas para players competitivos e lojistas, sem processar pagamento real no protótipo.</p>
        </div>
        <div class="rounded-3xl border border-white/10 bg-white/5 px-5 py-4">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Seu plano</p>
            <p class="text-xl font-black text-white">{{ auth()->user()->isPremium() ? 'Premium ativo' : 'Gratuito' }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <section class="arena-card p-6">
            <p class="font-bold text-arena-cyan">Para jogadores</p>
            <h2 class="mt-2 text-3xl font-black text-white">Player Premium</h2>
            <p class="mt-3 text-slate-400">Ideal para jogadores que participam de torneios, querem acompanhar a comunidade e destacar seus anúncios pessoais no marketplace.</p>
            <p class="mt-5 text-4xl font-black text-arena-gold">R$ 9,90 <span class="text-base text-slate-400">/ mês</span></p>

            <div class="mt-5 grid gap-3 text-sm text-slate-300">
                <p class="rounded-2xl border border-white/10 bg-white/5 p-3">Anúncios pessoais com selo premium.</p>
                <p class="rounded-2xl border border-white/10 bg-white/5 p-3">Alertas e recomendações de torneios.</p>
                <p class="rounded-2xl border border-white/10 bg-white/5 p-3">Perfil de jogador com destaque na comunidade.</p>
            </div>

            <form method="POST" action="{{ route('premium.subscribe') }}" class="mt-6">
                @csrf
                <input type="hidden" name="plan" value="player_premium">
                <button class="arena-btn w-full">Assinar Player Premium</button>
            </form>
        </section>

        <section class="arena-card border-arena-gold/30 p-6">
            <p class="font-bold text-arena-gold">Para lojistas</p>
            <h2 class="mt-2 text-3xl font-black text-white">Loja Premium</h2>
            <p class="mt-3 text-slate-400">Plano para lojas e organizadores que querem vitrine comercial, mais visibilidade e anúncios destacados no marketplace.</p>
            <p class="mt-5 text-4xl font-black text-arena-gold">R$ 29,90 <span class="text-base text-slate-400">/ mês</span></p>

            <div class="mt-5 grid gap-3 text-sm text-slate-300">
                <p class="rounded-2xl border border-white/10 bg-white/5 p-3">Vitrine de loja com selo verificado.</p>
                <p class="rounded-2xl border border-white/10 bg-white/5 p-3">Anúncios destacados automaticamente.</p>
                <p class="rounded-2xl border border-white/10 bg-white/5 p-3">Maior capacidade de divulgação de produtos e torneios.</p>
            </div>

            <form method="POST" action="{{ route('premium.subscribe') }}" class="mt-6">
                @csrf
                <input type="hidden" name="plan" value="loja_premium">
                <button class="arena-btn w-full">Assinar Loja Premium</button>
            </form>
        </section>
    </div>

    <section class="arena-card mt-6 p-6">
        <h2 class="text-2xl font-black text-white">Observação para a banca</h2>
        <p class="mt-3 text-slate-400">O botão de assinatura não realiza cobrança real. Ele apenas ativa o estado premium do usuário no banco de dados para demonstrar o modelo de monetização, a diferenciação entre planos e o impacto visual no marketplace.</p>
    </section>
</x-layouts.app>
