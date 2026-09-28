<form method="POST" action="{{ route('store.profile') }}" class="grid gap-4">@csrf @method('PUT')
<label class="arena-label">Nome público da loja<input class="arena-input mt-2" name="name" value="{{ old('name',$store->name ?? '') }}" required maxlength="120"></label>
<label class="arena-label">E-mail público de contato<input class="arena-input mt-2" type="email" name="contact_email" value="{{ old('contact_email',$store->contact_email ?? auth()->user()->email) }}" required maxlength="255"></label>
<label class="arena-label">Sobre a loja<textarea class="arena-input mt-2" name="description" maxlength="1000">{{ old('description',$store->description ?? '') }}</textarea></label>
<p class="text-sm text-slate-400">Nome e e-mail serão exibidos nos anúncios publicados. Seu estoque e custos são privados.</p>
<button class="arena-btn justify-self-start">Salvar loja</button>
</form>
