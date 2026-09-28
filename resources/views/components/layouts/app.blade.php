<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0D0718">
    <title>{{ $title ?? 'Eldritch Arena' }}</title>
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen pb-24 lg:pb-0">
    <a href="#main-content" class="sr-only focus:not-sr-only">Pular para o conteúdo</a>
    <x-top-nav />
    <main id="main-content" class="relative z-10 mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-5 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-200">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())<div role="alert" class="mb-5 rounded-lg border border-red-400/30 bg-red-500/10 p-4 text-red-200"><p class="font-bold">Confira os dados informados</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        {{ $slot ?? '' }}
        @yield('content')
    </main>
    <footer class="mx-auto max-w-7xl px-6 py-8 text-xs text-slate-500">Eldritch Arena · Encontre sua mesa. <a class="ml-3 underline" href="{{ route('premium') }}">Arena Plus</a></footer>
    @auth
        <x-bottom-nav />
    @endauth
</body>
</html>
