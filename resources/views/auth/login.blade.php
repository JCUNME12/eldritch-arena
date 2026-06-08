<x-layouts.app title="Login — Eldritch Arena">
    <div class="mx-auto max-w-xl py-10">
        <div class="arena-card p-6 md:p-8">
            <h1 class="font-display text-3xl font-black">Entrar na Arena</h1>
            <p class="mt-2 text-slate-400">Use sua conta ou os usuários de demonstração gerados pelo seeder.</p>
            <div class="mt-5 grid gap-3 text-sm md:grid-cols-2"><div class="arena-card-soft p-4"><strong>Jogador demo</strong><p class="text-slate-400">jogador@eldritch.test</p><p class="text-slate-400">password</p></div><div class="arena-card-soft p-4"><strong>Loja demo</strong><p class="text-slate-400">loja@eldritch.test</p><p class="text-slate-400">password</p></div></div>
            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">@csrf
                <div><label class="arena-label">E-mail</label><input type="email" name="email" value="{{ old('email') }}" required class="arena-input mt-2"></div>
                <div><label class="arena-label">Senha</label><input type="password" name="password" required class="arena-input mt-2"></div>
                <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="remember" class="rounded border-white/10 bg-black/30 text-arena-purple focus:ring-arena-purple"> Lembrar acesso</label>
                @if ($errors->any())<div class="rounded-2xl border border-red-400/30 bg-red-500/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>@endif
                <button class="arena-btn w-full">Entrar</button>
                <p class="text-center text-sm text-slate-400">Ainda não tem conta? <a href="{{ route('register') }}" class="font-bold text-arena-cyan">Cadastrar</a></p>
            </form>
        </div>
    </div>
</x-layouts.app>
