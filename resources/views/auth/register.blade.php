<x-layouts.app title="Cadastro — Eldritch Arena">
    <div class="mx-auto max-w-2xl py-10">
        <div class="arena-card p-6 md:p-8">
            <h1 class="font-display text-3xl font-black">Criar conta na Arena</h1>
            <p class="mt-2 text-slate-400">Escolha seu tipo de perfil para receber uma experiência personalizada.</p>
            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
                @csrf
                <div><label class="arena-label">Nome</label><input name="name" value="{{ old('name') }}" required class="arena-input mt-2"></div>
                <div><label class="arena-label">E-mail</label><input type="email" name="email" value="{{ old('email') }}" required class="arena-input mt-2"></div>
                <div class="grid gap-4 md:grid-cols-2"><div><label class="arena-label">Senha</label><input type="password" name="password" required class="arena-input mt-2"></div><div><label class="arena-label">Confirmar senha</label><input type="password" name="password_confirmation" required class="arena-input mt-2"></div></div>
                <div x-data="{ type: '{{ old('type', 'player') }}' }"><label class="arena-label">Tipo de perfil</label><input type="hidden" name="type" :value="type"><div class="mt-3 grid gap-3 md:grid-cols-2"><button type="button" @click="type='player'" :class="type==='player' ? 'border-arena-purple bg-arena-purple/20' : 'border-white/10 bg-white/5'" class="rounded-2xl border p-5 text-left transition"><span class="text-2xl">🎮</span><strong class="mt-2 block">Sou Jogador</strong><span class="text-sm text-slate-400">Inscrição em torneios, marketplace e contador.</span></button><button type="button" @click="type='organizer'" :class="type==='organizer' ? 'border-arena-cyan bg-arena-cyan/20' : 'border-white/10 bg-white/5'" class="rounded-2xl border p-5 text-left transition"><span class="text-2xl">🏪</span><strong class="mt-2 block">Sou Loja/Organizador</strong><span class="text-sm text-slate-400">Criação de torneios e analytics.</span></button></div></div>
                @if ($errors->any())<div class="rounded-2xl border border-red-400/30 bg-red-500/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>@endif
                <button class="arena-btn w-full">Criar conta</button>
                <p class="text-center text-sm text-slate-400">Já tem conta? <a href="{{ route('login') }}" class="font-bold text-arena-cyan">Entrar</a></p>
            </form>
        </div>
    </div>
</x-layouts.app>
