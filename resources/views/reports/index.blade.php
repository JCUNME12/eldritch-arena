<x-layouts.app title="Reports de torneios · Eldritch Arena">
 <p class="text-sm font-bold uppercase tracking-widest text-emerald-300">Circuito competitivo</p>
 <h1 class="mt-2 text-3xl font-bold">O que está sendo jogado nas mesas</h1>
 <p class="my-4 text-slate-400">Resultados publicados pelos organizadores. Os dados representam somente os eventos registrados na Arena, não todo o cenário regional.</p>
 <a class="text-emerald-300 underline" href="{{ route('reports.example') }}">Explorar um report de demonstração</a>
 <form class="my-6 flex flex-wrap gap-3" method="GET">
  <label>Jogo<select class="block rounded bg-slate-900 p-3" name="game"><option value="">Todos</option>@foreach(config('cardgames') as $game=>$formats)<option @selected(request('game')===$game)>{{ $game }}</option>@endforeach</select></label>
  <label>Formato<input class="block rounded bg-slate-900 p-3" name="format" maxlength="80" value="{{ request('format') }}" placeholder="Ex.: Modern"></label>
  <label>Cidade (nome exato)<input class="block rounded bg-slate-900 p-3" name="city" maxlength="80" value="{{ request('city') }}" placeholder="Ex.: Rio Branco"></label>
  <label>UF<input class="block w-24 rounded bg-slate-900 p-3" name="state" maxlength="2" pattern="[A-Z]{2}" value="{{ request('state') }}" placeholder="AC"></label>
  <button class="self-end rounded bg-emerald-700 px-5 py-3">Filtrar</button>
 </form>
 <div class="grid gap-4 md:grid-cols-2">@forelse($events as $event)
  @php($report=$event->report_snapshot)
  <a class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 hover:border-emerald-400/50" href="{{ route('reports.show',$event) }}"><p class="text-sm text-emerald-300">{{ $report['game'] }} · {{ $report['format'] }}</p><h2 class="my-2 text-xl font-bold">{{ $report['title'] }}</h2><p class="text-slate-400">{{ $report['date'] }} · {{ $report['players'] }} participantes</p><p>{{ $report['city'] ?: 'Localidade não informada' }} {{ $report['state'] }}</p></a>
 @empty <div class="rounded-2xl border border-white/10 p-8"><h2 class="font-bold">Nenhum report publicado neste filtro</h2><p class="mt-2 text-slate-400">Após o evento, o organizador confere a apuração e publica os resultados.</p><a class="mt-4 inline-block underline" href="{{ route('tournaments.index',['scope'=>'mine']) }}">Meus torneios</a></div>@endforelse</div>
 <div class="mt-6">{{ $events->links() }}</div>
</x-layouts.app>
