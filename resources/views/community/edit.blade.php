<x-layouts.app title="Editar tópico — Eldritch Arena">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="font-bold text-arena-cyan">Comunidade</p>
                <h1 class="arena-section-title">Editar tópico</h1>
                <p class="mt-2 text-slate-400">Atualize o conteúdo, categoria ou imagem da sua publicação.</p>
            </div>
            <a href="{{ route('community.show', $topic) }}" class="arena-btn-secondary">Voltar</a>
        </div>

        <form method="POST" action="{{ route('community.update', $topic) }}" enctype="multipart/form-data" class="arena-card grid gap-5 p-6">
            @csrf
            @method('PUT')
            <label class="grid gap-2">
                <span class="text-sm font-bold text-slate-200">Título</span>
                <input name="title" value="{{ old('title', $topic->title) }}" maxlength="140" required style="background-color: #050816 !important; color: #ffffff !important; caret-color: #ffffff !important;" class="rounded-2xl border border-white/10 px-4 py-3 placeholder:text-slate-500 outline-none ring-1 ring-white/10 focus:border-arena-cyan focus:ring-arena-cyan/50">
                @error('title') <span class="text-sm text-red-300">{{ $message }}</span> @enderror
            </label>

            <div class="grid gap-2">
                <span class="text-sm font-bold text-slate-200">Categoria</span>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($categories as $category)
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="{{ $category }}" class="peer sr-only" @checked(old('category', $topic->category) === $category) required>
                            <span class="flex justify-center rounded-2xl border border-white/10 bg-slate-950/80 px-4 py-3 text-sm font-bold text-slate-300 transition peer-checked:border-arena-cyan peer-checked:bg-arena-cyan/15 peer-checked:text-white hover:border-arena-cyan/60">{{ $category }}</span>
                        </label>
                    @endforeach
                </div>
                @error('category') <span class="text-sm text-red-300">{{ $message }}</span> @enderror
            </div>

            <label class="grid gap-2">
                <span class="text-sm font-bold text-slate-200">Conteúdo</span>
                <textarea name="body" rows="8" required style="background-color: #050816 !important; color: #ffffff !important; caret-color: #ffffff !important;" class="rounded-2xl border border-white/10 px-4 py-3 placeholder:text-slate-500 outline-none ring-1 ring-white/10 focus:border-arena-cyan focus:ring-arena-cyan/50">{{ old('body', $topic->body) }}</textarea>
                @error('body') <span class="text-sm text-red-300">{{ $message }}</span> @enderror
            </label>

            @if($topic->image_path)
                <div class="grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <span class="text-sm font-bold text-slate-200">Imagem atual</span>
                    <img src="{{ asset('storage/' . $topic->image_path) }}" alt="Imagem atual do tópico" class="max-h-72 rounded-xl object-contain">
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

            <button class="arena-btn justify-center" type="submit">Salvar alterações</button>
        </form>
    </div>
</x-layouts.app>
