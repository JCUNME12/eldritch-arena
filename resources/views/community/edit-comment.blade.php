<x-layouts.app title="Editar comentário — Eldritch Arena">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="font-bold text-arena-cyan">Comunidade</p>
                <h1 class="arena-section-title">Editar comentário</h1>
                <p class="mt-2 text-slate-400">Atualize sua resposta no tópico “{{ $comment->topic->title }}”.</p>
            </div>
            <a href="{{ route('community.show', $comment->topic) }}" class="arena-btn-secondary">Voltar</a>
        </div>

        <form method="POST" action="{{ route('community.comments.update', $comment) }}" enctype="multipart/form-data" class="arena-card grid gap-5 p-6">
            @csrf
            @method('PUT')
            <label class="grid gap-2">
                <span class="text-sm font-bold text-slate-200">Comentário</span>
                <textarea name="body" rows="6" required style="background-color: #050816 !important; color: #ffffff !important; caret-color: #ffffff !important;" class="rounded-2xl border border-white/10 px-4 py-3 placeholder:text-slate-500 outline-none ring-1 ring-white/10 focus:border-arena-cyan focus:ring-arena-cyan/50">{{ old('body', $comment->body) }}</textarea>
                @error('body') <span class="text-sm text-red-300">{{ $message }}</span> @enderror
            </label>

            @if($comment->image_path)
                <div class="grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <span class="text-sm font-bold text-slate-200">Imagem atual</span>
                    <img src="{{ asset('storage/' . $comment->image_path) }}" alt="Imagem atual do comentário" class="max-h-72 rounded-xl object-contain">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-white/20 bg-slate-950 text-arena-cyan">
                        Remover imagem atual
                    </label>
                </div>
            @endif

            <label class="grid gap-2">
                <span class="text-sm font-bold text-slate-200">Nova imagem opcional</span>
                <input type="file" name="image" accept="image/*" style="background-color: #050816 !important; color: #ffffff !important;" class="rounded-2xl border border-white/10 px-4 py-3 text-sm text-slate-300 file:mr-4 file:rounded-full file:border-0 file:bg-arena-cyan file:px-4 file:py-2 file:text-sm file:font-black file:text-slate-950 hover:file:bg-white">
                @error('image') <span class="text-sm text-red-300">{{ $message }}</span> @enderror
            </label>

            <button class="arena-btn justify-center" type="submit">Salvar comentário</button>
        </form>
    </div>
</x-layouts.app>
