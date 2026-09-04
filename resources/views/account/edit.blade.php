<x-layouts.app title="Minha conta — Eldritch Arena">
    <div class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-2">
        <section class="arena-card p-6 md:p-8">
            <p class="font-bold text-arena-cyan">Perfil</p><h1 class="arena-section-title">Minha conta</h1><p class="mt-2 text-slate-400">Atualize as informações exibidas na Arena.</p>
            <form method="POST" action="{{ route('account.update') }}" class="mt-6 grid gap-4">@csrf @method('PATCH')
                <div><label for="name" class="arena-label">Nome</label><input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required class="arena-input mt-2"></div>
                <div><label for="email" class="arena-label">E-mail</label><input id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required type="email" class="arena-input mt-2"></div>
                <div><label for="type" class="arena-label">Tipo de perfil</label><select id="type" name="type" class="arena-input mt-2"><option value="player" @selected(old('type', auth()->user()->type) === 'player')>Jogador</option><option value="organizer" @selected(old('type', auth()->user()->type) === 'organizer')>Loja/Organizador</option></select></div>
                <button class="arena-btn w-fit">Salvar perfil</button>
            </form>
        </section>
        <section class="arena-card p-6 md:p-8">
            <p class="font-bold text-arena-gold">Segurança</p><h2 class="arena-section-title">Alterar senha</h2><p class="mt-2 text-slate-400">Use uma senha longa e exclusiva para proteger sua conta.</p>
            <form method="POST" action="{{ route('account.password') }}" class="mt-6 grid gap-4">@csrf @method('PUT')
                <div><label for="current_password" class="arena-label">Senha atual</label><input id="current_password" name="current_password" required type="password" autocomplete="current-password" class="arena-input mt-2"></div>
                <div><label for="password" class="arena-label">Nova senha</label><input id="password" name="password" required type="password" autocomplete="new-password" class="arena-input mt-2"></div>
                <div><label for="password_confirmation" class="arena-label">Confirmar nova senha</label><input id="password_confirmation" name="password_confirmation" required type="password" autocomplete="new-password" class="arena-input mt-2"></div>
                <button class="arena-btn w-fit">Atualizar senha</button>
            </form>
        </section>
    </div>
</x-layouts.app>
