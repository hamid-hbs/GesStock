<div>
    @if($message)<div class="bg-green-100 text-green-800 text-sm px-4 py-2 rounded mb-4">{{ $message }}</div>@endif
    @if($erreur)<div class="bg-red-100 text-red-800 text-sm px-4 py-2 rounded mb-4">{{ $erreur }}</div>@endif

    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <h3 class="font-semibold mb-3">Nouvelle vente (sortie de stock)</h3>
        <div class="grid md:grid-cols-2 gap-3 mb-4">
            <select wire:model.live="client_id" class="border-gray-300 rounded-md text-sm">
                <option value="">— Choisir le client —</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->nom }} [{{ $c->type }}]{{ $c->membre ? ' → '.$c->membre->nom : '' }}</option>
                @endforeach
            </select>
            <input type="date" wire:model="date" class="border-gray-300 rounded-md text-sm" />
        </div>

        @if($clientSel)
        <div class="grid md:grid-cols-2 gap-3 mb-4">
            <div class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm">
                <div class="font-semibold text-slate-800 mb-1">Client : {{ $clientSel->nom }}</div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                    <span class="px-2 py-0.5 rounded {{ $clientSel->type === 'grossiste' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ $clientSel->type }}</span>
                    <span>Tél : {{ $clientSel->telephone ?? '—' }}</span>
                    <span>Adresse : {{ $clientSel->adresse ?? '—' }}</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">Tarif appliqué : <strong>{{ $clientSel->type }}</strong></div>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm">
                @if($clientSel->membre)
                    <div class="font-semibold text-slate-800 mb-1">Apporteur : {{ $clientSel->membre->nom }}</div>
                    <div class="text-xs text-slate-600">Commission {{ $clientSel->membre->taux_commission }}% · Tél : {{ $clientSel->membre->telephone ?? '—' }}</div>
                @else
                    <div class="font-semibold text-slate-500">Sans apporteur</div>
                    <div class="text-xs text-slate-500">Aucune commission sur cette vente.</div>
                @endif
            </div>
        </div>
        @endif

        @foreach($lignes as $i => $ligne)
        <div class="flex gap-2 mb-2">
            <select wire:model.live="lignes.{{ $i }}.key" class="border-gray-300 rounded-md text-sm flex-1">
                <option value="">— Choisir l'article —</option>
                @foreach($articles as $a)
                    <option value="{{ $a['key'] }}">{{ $a['libelle'] }} (stock : {{ $a['stock'] }})</option>
                @endforeach
            </select>
            <input type="number" min="1" wire:model.live="lignes.{{ $i }}.quantite" class="border-gray-300 rounded-md text-sm w-28" />
            <button wire:click="retirerLigne({{ $i }})" class="text-red-600 text-sm px-2">✕</button>
        </div>
        @endforeach

        @if(count($apercu))
        <div class="bg-gray-50 rounded p-3 mt-3 text-sm">
            @foreach($apercu as $row)
                <div class="flex justify-between"><span>{{ $row['libelle'] }}</span><span>{{ number_format($row['prix'], 0, ',', ' ') }} FCFA × qté = <strong>{{ number_format($row['sous_total'], 0, ',', ' ') }} FCFA</strong></span></div>
            @endforeach
            <div class="flex justify-between font-bold mt-2 pt-2 border-t"><span>Total</span><span>{{ number_format($total, 0, ',', ' ') }} FCFA</span></div>
            <div class="flex justify-between text-amber-700"><span>Commission membre</span><span>{{ number_format($commission, 0, ',', ' ') }} FCFA</span></div>
        </div>
        @endif

        <div class="flex gap-3 mt-3">
            <button wire:click="ajouterLigne" class="text-sm text-indigo-600 border border-indigo-200 px-3 py-2 rounded-md">+ Ligne</button>
            <button wire:click="enregistrer" class="bg-blue-600 text-white text-sm px-4 py-2 rounded-md">Enregistrer la vente</button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4">
        <h3 class="font-semibold mb-3">Dernières ventes</h3>
        <div class="space-y-2">
            @foreach($ventes as $v)
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <button wire:click="basculerVente({{ $v->id }})" class="w-full flex flex-wrap items-center gap-x-4 gap-y-1 text-sm px-4 py-2.5 text-left hover:bg-slate-50">
                    <span class="text-slate-400">{{ isset($ventesOuvertes[$v->id]) ? '▾' : '▸' }}</span>
                    <span class="font-medium">{{ $v->reference }}</span>
                    <span class="text-slate-500">{{ $v->date_vente->format('d/m/Y') }}</span>
                    <span class="font-medium">{{ $v->client->nom }}</span>
                    <span class="px-2 py-0.5 rounded text-xs {{ $v->type_client === 'grossiste' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ $v->type_client }}</span>
                    <span class="text-slate-500 text-xs">→ {{ $v->membre->nom ?? 'sans apporteur' }}</span>
                    <span class="ml-auto tabular-nums">Qté <strong>{{ $v->quantiteTotale() }}</strong></span>
                    <span class="tabular-nums font-semibold">{{ number_format($v->total, 0, ',', ' ') }} FCFA</span>
                    <span class="tabular-nums text-amber-700 text-xs">comm. {{ number_format($v->commission_membre, 0, ',', ' ') }} FCFA</span>
                    <a href="{{ route('ventes.pdf', $v) }}" target="_blank" wire:click.stop class="text-indigo-600 text-xs">PDF</a>
                </button>
                @if(isset($ventesOuvertes[$v->id]))
                <div class="border-t bg-slate-50 px-4 py-3">
                    <div class="grid md:grid-cols-2 gap-3 mb-3 text-xs text-slate-600">
                        <div><strong>Client :</strong> {{ $v->client->nom }} ({{ $v->type_client }}) · Tél : {{ $v->client->telephone ?? '—' }} · {{ $v->client->adresse ?? '' }}</div>
                        <div><strong>Apporteur :</strong> {{ $v->membre ? $v->membre->nom.' ('.$v->membre->taux_commission.'%)' : '— aucun' }}</div>
                    </div>
                    <table class="w-full text-sm bg-white rounded">
                        <thead><tr class="text-left text-gray-500">
                            <th class="py-1 px-2">Article</th>
                            <th class="text-right">PU (FCFA)</th>
                            <th class="text-right">Quantité</th>
                            <th class="text-right">Sous-total (FCFA)</th>
                        </tr></thead>
                        <tbody>
                        @foreach($v->lignes as $l)
                            <tr class="border-t">
                                <td class="py-1 px-2">{{ $l->libelleArticle() }}</td>
                                <td class="text-right tabular-nums">{{ number_format($l->prix_unitaire, 0, ',', ' ') }}</td>
                                <td class="text-right tabular-nums">{{ $l->quantite }}</td>
                                <td class="text-right tabular-nums font-medium">{{ number_format($l->sous_total, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="flex justify-end gap-6 mt-2 text-sm">
                        <span>Total : <strong>{{ number_format($v->total, 0, ',', ' ') }} FCFA</strong></span>
                        <span class="text-amber-700">Commission : <strong>{{ number_format($v->commission_membre, 0, ',', ' ') }} FCFA</strong></span>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
