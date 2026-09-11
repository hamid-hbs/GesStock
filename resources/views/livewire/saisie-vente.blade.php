<div class="space-y-6">
    @if($message)<x-ui-alert tone="success">{{ $message }}</x-ui-alert>@endif
    @if($erreur)<x-ui-alert tone="error">{{ $erreur }}</x-ui-alert>@endif

    <x-page-header title="Ventes" subtitle="Sorties de stock : prix auto selon client, commission auto">
        <a href="{{ route('exports.ventes') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:bg-slate-100 border border-slate-200 px-3 py-2 rounded-lg transition">Excel ventes</a>
    </x-page-header>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 sm:p-6">
        <h3 class="font-semibold text-slate-800 mb-4">Nouvelle vente</h3>
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Client *</label>
                <select wire:model.live="client_id" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">— Choisir le client —</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->nom }} [{{ $c->type }}]{{ $c->membre ? ' → '.$c->membre->nom : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Date</label>
                <input type="date" wire:model="date" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
        </div>

        @if($clientSel)
        <div class="grid md:grid-cols-2 gap-3 mb-4">
            <div class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm">
                <div class="font-semibold text-slate-800 mb-1">Client : {{ $clientSel->nom }}</div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                    <x-status-badge :tone="$clientSel->type === 'grossiste' ? 'blue' : 'slate'">{{ $clientSel->type }}</x-status-badge>
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

        <label class="block text-xs font-medium text-slate-600 mb-1">Articles vendus</label>
        @foreach($lignes as $i => $ligne)
        <div class="flex gap-2 mb-2">
            <select wire:model.live="lignes.{{ $i }}.key" class="border-slate-300 rounded-lg text-sm flex-1 focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">— Choisir l'article —</option>
                @foreach($articles as $a)
                    <option value="{{ $a['key'] }}">{{ $a['libelle'] }} (stock : {{ $a['stock'] }})</option>
                @endforeach
            </select>
            <input type="number" min="1" wire:model.live="lignes.{{ $i }}.quantite" title="Quantité" class="border-slate-300 rounded-lg text-sm w-28 tabular-nums focus:border-emerald-500 focus:ring-emerald-500" />
            <button wire:click="retirerLigne({{ $i }})" title="Retirer" class="text-red-500 hover:bg-red-50 px-2 rounded-lg transition">✕</button>
        </div>
        @endforeach

        @if(count($apercu))
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mt-3 text-sm space-y-1">
            @foreach($apercu as $row)
                <div class="flex justify-between gap-3"><span class="text-slate-700">{{ $row['libelle'] }}</span><span class="tabular-nums">{{ number_format($row['prix'], 0, ',', ' ') }} FCFA × qté = <strong>{{ number_format($row['sous_total'], 0, ',', ' ') }} FCFA</strong></span></div>
            @endforeach
            <div class="flex justify-between font-bold text-slate-800 mt-2 pt-2 border-t border-slate-200"><span>Total</span><span class="tabular-nums">{{ number_format($total, 0, ',', ' ') }} FCFA</span></div>
            <div class="flex justify-between text-amber-700"><span>Commission membre</span><span class="tabular-nums">{{ number_format($commission, 0, ',', ' ') }} FCFA</span></div>
        </div>
        @endif

        <div class="flex flex-wrap gap-2 mt-4">
            <button wire:click="ajouterLigne" class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 px-3 py-2 rounded-lg transition">+ Ligne</button>
            <button wire:click="enregistrer" wire:loading.attr="disabled" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-5 py-2 rounded-lg shadow-sm disabled:opacity-50">
                <span wire:loading.remove>Enregistrer la vente</span>
                <span wire:loading>Enregistrement…</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-semibold text-slate-800 mb-3">Dernières ventes</h3>
        <div class="space-y-2">
            @foreach($ventes as $v)
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <button wire:click="basculerVente({{ $v->id }})" class="w-full flex flex-wrap items-center gap-x-4 gap-y-1 text-sm px-4 py-2.5 text-left hover:bg-slate-50 transition">
                    <span class="text-slate-400">{{ isset($ventesOuvertes[$v->id]) ? '▾' : '▸' }}</span>
                    <span class="font-medium text-slate-800">{{ $v->reference }}</span>
                    <span class="text-slate-500">{{ $v->date_vente->format('d/m/Y') }}</span>
                    <span class="font-medium text-slate-700">{{ $v->client->nom }}</span>
                    <x-status-badge :tone="$v->type_client === 'grossiste' ? 'blue' : 'slate'">{{ $v->type_client }}</x-status-badge>
                    <span class="text-slate-500 text-xs">→ {{ $v->membre->nom ?? 'sans apporteur' }}</span>
                    <span class="ml-auto tabular-nums text-slate-600">Qté <strong>{{ $v->quantiteTotale() }}</strong></span>
                    <span class="tabular-nums font-semibold text-slate-800">{{ number_format($v->total, 0, ',', ' ') }} FCFA</span>
                    <span class="tabular-nums text-amber-700 text-xs">comm. {{ number_format($v->commission_membre, 0, ',', ' ') }} FCFA</span>
                    <a href="{{ route('ventes.pdf', $v) }}" target="_blank" wire:click.stop class="text-emerald-700 hover:underline text-xs font-medium">PDF</a>
                </button>
                @if(isset($ventesOuvertes[$v->id]))
                <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="grid md:grid-cols-2 gap-3 mb-3 text-xs text-slate-600">
                        <div><strong>Client :</strong> {{ $v->client->nom }} ({{ $v->type_client }}) · Tél : {{ $v->client->telephone ?? '—' }} · {{ $v->client->adresse ?? '' }}</div>
                        <div><strong>Apporteur :</strong> {{ $v->membre ? $v->membre->nom.' ('.$v->membre->taux_commission.'%)' : '— aucun' }}</div>
                    </div>
                    <table class="w-full text-sm bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <th class="py-2 px-3 font-medium">Article</th>
                            <th class="py-2 px-3 font-medium text-right">PU (FCFA)</th>
                            <th class="py-2 px-3 font-medium text-right">Quantité</th>
                            <th class="py-2 px-3 font-medium text-right">Sous-total (FCFA)</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach($v->lignes as $l)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2 px-3 text-slate-700">{{ $l->libelleArticle() }}</td>
                                <td class="py-2 px-3 text-right tabular-nums">{{ number_format($l->prix_unitaire, 0, ',', ' ') }}</td>
                                <td class="py-2 px-3 text-right tabular-nums">{{ $l->quantite }}</td>
                                <td class="py-2 px-3 text-right tabular-nums font-medium">{{ number_format($l->sous_total, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="flex justify-end gap-6 mt-2 text-sm">
                        <span>Total : <strong class="tabular-nums">{{ number_format($v->total, 0, ',', ' ') }} FCFA</strong></span>
                        <span class="text-amber-700">Commission : <strong class="tabular-nums">{{ number_format($v->commission_membre, 0, ',', ' ') }} FCFA</strong></span>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
