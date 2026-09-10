<div>
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <label class="text-sm text-gray-600">Journée du</label>
        <input type="date" wire:model.live="date" class="border-gray-300 rounded-md shadow-sm text-sm" />
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Produite</div>
            <div class="text-2xl font-bold text-emerald-600">{{ number_format($produite) }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Vendue</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format($vendue) }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Restante</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($restante) }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="text-xs text-gray-500 uppercase">CA jour</div>
            <div class="text-2xl font-bold text-indigo-600">{{ number_format($caJour, 0, ',', ' ') }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="text-xs text-gray-500 uppercase">CA mois</div>
            <div class="text-2xl font-bold text-indigo-400">{{ number_format($caMois, 0, ',', ' ') }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="text-xs text-gray-500 uppercase">Commissions jour</div>
            <div class="text-2xl font-bold text-amber-600">{{ number_format($commissionsJour, 0, ',', ' ') }}</div>
        </div>
    </div>

    @if($alertes->count() || $alertesProduits->count())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-red-700 mb-2">⚠ Stock bas / rupture</h3>
        <ul class="text-sm text-red-700 space-y-1">
            @foreach($alertes as $cat)
                <li>{{ $cat->produit->nom }} — {{ $cat->nom }} : <strong>{{ $cat->stock }}</strong> restant(s)</li>
            @endforeach
            @foreach($alertesProduits as $prod)
                <li>{{ $prod->nom }} : <strong>{{ $prod->stock }}</strong> restant(s)</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h3 class="font-semibold mb-3">Dernières ventes</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500">
                    <th class="py-1">Réf</th><th>Client</th><th class="text-right">Total</th>
                </tr></thead>
                <tbody>
                @forelse($dernieresVentes as $v)
                    <tr class="border-t">
                        <td class="py-1">{{ $v->reference }}</td>
                        <td>{{ $v->client->nom }}</td>
                        <td class="text-right">{{ number_format($v->total, 0, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-2 text-gray-400">Aucune vente.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h3 class="font-semibold mb-3">Dernières productions</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500">
                    <th class="py-1">Réf</th><th>Date</th><th class="text-right">Qté</th>
                </tr></thead>
                <tbody>
                @forelse($dernieresProductions as $p)
                    <tr class="border-t">
                        <td class="py-1">{{ $p->reference }}</td>
                        <td>{{ $p->date_production->format('d/m/Y') }}</td>
                        <td class="text-right">{{ $p->quantiteTotale() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-2 text-gray-400">Aucune production.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4">
        <h3 class="font-semibold mb-3">Membres — CA apporté & commissions (jour sélectionné)</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500">
                <th class="py-1">Membre</th><th class="text-right">CA apporté</th><th class="text-right">Commission due</th>
            </tr></thead>
            <tbody>
            @forelse($classementMembres as $row)
                <tr class="border-t">
                    <td class="py-1">{{ $row['membre']->nom }} ({{ $row['membre']->taux_commission }}%)</td>
                    <td class="text-right">{{ number_format($row['ca'], 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($row['commission'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="py-2 text-gray-400">Aucune vente via membre ce jour.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
