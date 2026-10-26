<x-layouts.app title="Report de torneio · Eldritch Arena">
 <a class="text-emerald-300 underline" href="{{ route('reports.index') }}">← Todos os reports</a>
 @if($example)<div class="my-5 rounded-xl border border-amber-400/30 bg-amber-500/10 p-4 text-amber-200"><strong>Demonstração fictícia.</strong> Estes resultados não pertencem a jogadores reais e não alimentam rankings.</div>@endif
 <p class="mt-6 text-sm text-emerald-300">{{ $report['game'] }} · {{ $report['format'] }}</p>
 <h1 class="mt-2 text-3xl font-bold">{{ $report['title'] }}</h1>
 <p class="mt-3 text-slate-400">{{ $report['date'] }} · {{ $report['city'] ?: 'Localidade não informada' }} {{ $report['state'] }}</p>
 <p class="mt-2 text-sm text-slate-400">{{ $example ? 'Exemplo de apuração' : 'Resultados declarados e publicados pelo organizador' }} · Duelo 1×1 · Pareamentos manuais</p>
 @include('reports.summary')
</x-layouts.app>
