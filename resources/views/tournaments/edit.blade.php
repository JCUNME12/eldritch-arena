<x-layouts.app title="Editar torneio — Eldritch Arena">
    <div class="mx-auto max-w-4xl">
        <section class="arena-card p-6 md:p-8">
            <p class="font-bold text-arena-cyan">Gestão de evento</p>
            <h1 class="arena-section-title">Editar torneio</h1>
            <p class="mt-2 text-slate-400">Atualize as informações que serão exibidas aos participantes.</p>

            <form method="POST" action="{{ route('tournaments.update', $tournament) }}" class="mt-6 grid gap-4">
                @csrf @method('PUT')
                <div><label for="title" class="arena-label">Nome do torneio</label><input id="title" name="title" value="{{ old('title', $tournament->title) }}" required class="arena-input mt-2"></div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div><label for="game" class="arena-label">Jogo</label><input id="game" name="game" value="{{ old('game', $tournament->game) }}" required class="arena-input mt-2"></div>
                    <div><label for="starts_at" class="arena-label">Data e horário</label><input id="starts_at" name="starts_at" value="{{ old('starts_at', $tournament->starts_at->format('Y-m-d\\TH:i')) }}" required type="datetime-local" class="arena-input mt-2"></div>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div><label for="entry_fee" class="arena-label">Inscrição (R$)</label><input id="entry_fee" name="entry_fee" value="{{ old('entry_fee', $tournament->entry_fee) }}" required type="number" min="0" step="0.01" class="arena-input mt-2"></div>
                    <div><label for="slots" class="arena-label">Número de vagas</label><input id="slots" name="slots" value="{{ old('slots', $tournament->slots) }}" required type="number" min="2" max="256" class="arena-input mt-2"></div>
                    <div><label for="location" class="arena-label">Local</label><input id="location" name="location" value="{{ old('location', $tournament->location) }}" required class="arena-input mt-2"></div>
                </div>
                <div><label for="prize" class="arena-label">Premiação</label><input id="prize" name="prize" value="{{ old('prize', $tournament->prize) }}" required class="arena-input mt-2"></div>
                <div><label for="description" class="arena-label">Descrição</label><textarea id="description" name="description" rows="5" class="arena-input mt-2">{{ old('description', $tournament->description) }}</textarea></div>
                <div class="flex flex-col gap-3 sm:flex-row"><button class="arena-btn">Salvar alterações</button><a href="{{ route('tournaments.show', $tournament) }}" class="arena-btn-secondary text-center">Cancelar edição</a></div>
            </form>
        </section>
    </div>
</x-layouts.app>
