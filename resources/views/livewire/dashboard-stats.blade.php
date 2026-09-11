<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg p-1 shadow-sm">
            @foreach(['jour' => 'Jour', '7j' => '7 jours', '30j' => '30 jours', 'mois' => 'Mois'] as $key => $lib)
                <button wire:click="$set('periode', '{{ $key }}')"
                    class="text-sm font-medium px-3.5 py-1.5 rounded-md transition {{ $periode === $key ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' }}">{{ $lib }}</button>
            @endforeach
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <label>Référence</label>
            <input type="date" wire:model.live="date" class="border-slate-300 rounded-lg shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <x-stat-card label="Produite" :value="number_format($produiteVal)" color="emerald" :trend="$produiteTrend">
            <x-slot:icon><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Vendue" :value="number_format($vendueVal)" color="blue" :trend="$vendueTrend">
            <x-slot:icon><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Restante" :value="number_format($restanteVal)" color="slate">
            <x-slot:icon><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v12a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="CA période" :value="number_format($caVal, 0, ',', ' ')" suffix="FCFA" color="indigo" :trend="$caTrend">
            <x-slot:icon><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Commissions" :value="number_format($commissionsVal, 0, ',', ' ')" suffix="FCFA" color="amber" :trend="$commissionsTrend">
            <x-slot:icon><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot:icon>
        </x-stat-card>
        <x-stat-card label="Alertes stock" :value="$nbAlertes" color="{{ $nbAlertes > 0 ? 'red' : 'emerald' }}">
            <x-slot:icon><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></x-slot:icon>
        </x-stat-card>
    </div>
    <p class="text-xs text-slate-400 -mt-3">{{ $nbVentes }} vente(s) sur la période · tendances vs période précédente.</p>

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 lg:col-span-2">
            <h3 class="font-semibold text-slate-800 mb-1">Chiffre d'affaires</h3>
            <p class="text-xs text-slate-400 mb-3">Par jour sur la période (FCFA)</p>
            <div wire:ignore class="h-64"><canvas id="chart-ca"></canvas></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-semibold text-slate-800 mb-1">Clientèle</h3>
            <p class="text-xs text-slate-400 mb-3">Répartition du CA</p>
            <div wire:ignore class="h-64 flex items-center justify-center"><canvas id="chart-rep"></canvas></div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-semibold text-slate-800 mb-1">Entrées vs sorties</h3>
        <p class="text-xs text-slate-400 mb-3">Quantités produites et vendues par jour</p>
        <div wire:ignore class="h-64"><canvas id="chart-flux"></canvas></div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-slate-800">Stock bas / rupture <span class="ml-1 inline-flex items-center rounded-full bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-0.5">{{ $kpi['alertes']['val'] }}</span></h3>
            <a href="{{ route('productions.index') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 transition text-white text-xs font-medium px-3 py-1.5 rounded-lg">Produire</a>
        </div>
        @if($toutesAlertes->count())
        <div class="grid md:grid-cols-2 gap-2">
            @foreach($toutesAlertes as $a)
            <div class="flex items-center justify-between gap-3 border rounded-lg px-3 py-2 {{ $a['critique'] ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }}">
                <span class="text-sm text-slate-700">{{ $a['libelle'] }}</span>
                <x-status-badge :tone="$a['critique'] ? 'red' : 'amber'">{{ $a['critique'] ? 'Rupture' : 'Bas · '.$a['stock'] }}</x-status-badge>
            </div>
            @endforeach
        </div>
        @else
            <x-empty-state message="Aucune alerte — tous les stocks sont au-dessus des seuils." />
        @endif
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-semibold text-slate-800 mb-3">Top articles vendus</h3>
            @forelse($topArticles as $t)
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-slate-700">{{ $t['libelle'] }}</span>
                    <span class="text-slate-500 tabular-nums">{{ $t['qte'] }} · {{ number_format($t['ca'], 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full"><div class="h-2 bg-emerald-500 rounded-full" style="width: {{ round($t['qte'] / $maxTop * 100) }}%"></div></div>
            </div>
            @empty
                <p class="text-sm text-slate-400">Aucune vente sur la période.</p>
            @endforelse
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-semibold text-slate-800 mb-3">Top membres apporteurs</h3>
            @forelse($classementMembres as $row)
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-slate-700">{{ $row['membre']->nom }} <span class="text-slate-400">({{ $row['membre']->taux_commission }}%)</span></span>
                    <span class="text-slate-500 tabular-nums">{{ number_format($row['ca'], 0, ',', ' ') }} FCFA · <span class="text-amber-700">{{ number_format($row['commission'], 0, ',', ' ') }}</span></span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full"><div class="h-2 bg-amber-500 rounded-full" style="width: {{ round($row['ca'] / $maxMembre * 100) }}%"></div></div>
            </div>
            @empty
                <p class="text-sm text-slate-400">Aucune vente via membre sur la période.</p>
            @endforelse
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-slate-800">Dernières ventes</h3>
                <a href="{{ route('ventes.index') }}" class="text-emerald-700 hover:underline text-xs font-medium">Tout voir →</a>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                @forelse($dernieresVentes as $v)
                    <tr>
                        <td class="py-2 text-slate-400 text-xs">{{ $v->reference }}</td>
                        <td class="py-2 text-slate-700">{{ $v->client->nom }}</td>
                        <td class="py-2 text-right tabular-nums font-medium">{{ number_format($v->total, 0, ',', ' ') }} <span class="text-slate-400 font-normal">FCFA</span></td>
                    </tr>
                @empty
                    <tr><td class="py-2 text-slate-400 text-sm">Aucune vente.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-slate-800">Dernières productions</h3>
                <a href="{{ route('productions.index') }}" class="text-emerald-700 hover:underline text-xs font-medium">Tout voir →</a>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                @forelse($dernieresProductions as $p)
                    <tr>
                        <td class="py-2 text-slate-400 text-xs">{{ $p->reference }}</td>
                        <td class="py-2 text-slate-700">{{ $p->date_production->format('d/m/Y') }}</td>
                        <td class="py-2 text-right tabular-nums font-medium">{{ $p->quantiteTotale() }}</td>
                    </tr>
                @empty
                    <tr><td class="py-2 text-slate-400 text-sm">Aucune production.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const initial = @json($graphs);
    let caChart, fluxChart, repChart;

    function build(data) {
        if (caChart) caChart.destroy();
        if (fluxChart) fluxChart.destroy();
        if (repChart) repChart.destroy();

        caChart = new Chart(document.getElementById('chart-ca'), {
            type: 'line',
            data: { labels: data.labels, datasets: [{ label: 'CA (FCFA)', data: data.ca, borderColor: '#4f46e5', backgroundColor: 'rgba(79,70,229,.12)', fill: true, tension: .35, pointRadius: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
        fluxChart = new Chart(document.getElementById('chart-flux'), {
            type: 'bar',
            data: { labels: data.labels, datasets: [
                { label: 'Produite', data: data.prod, backgroundColor: '#10b981' },
                { label: 'Vendue', data: data.ventes, backgroundColor: '#3b82f6' }
            ] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
        repChart = new Chart(document.getElementById('chart-rep'), {
            type: 'doughnut',
            data: { labels: ['Particulier', 'Grossiste'], datasets: [{ data: data.repartition, backgroundColor: ['#94a3b8', '#3b82f6'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    document.addEventListener('DOMContentLoaded', () => build(initial));
    document.addEventListener('livewire:init', () => {
        Livewire.on('dashboard-refresh', (e) => build(e.graphs ?? e[0]?.graphs ?? initial));
    });
})();
</script>
