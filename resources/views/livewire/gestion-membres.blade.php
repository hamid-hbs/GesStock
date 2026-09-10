<div>
    @if($message)<div class="bg-green-100 text-green-800 text-sm px-4 py-2 rounded mb-4">{{ $message }}</div>@endif

    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <h3 class="font-semibold mb-3">{{ $editingId ? 'Modifier le membre' : 'Nouveau membre (vendeur)' }}</h3>
        <div class="grid md:grid-cols-4 gap-3">
            <input wire:model="nom" placeholder="Nom *" class="border-gray-300 rounded-md text-sm" />
            <input wire:model="telephone" placeholder="Téléphone" class="border-gray-300 rounded-md text-sm" />
            <input wire:model="adresse" placeholder="Adresse" class="border-gray-300 rounded-md text-sm" />
            <div class="flex items-center gap-2">
                <input type="number" step="0.01" min="0" max="100" wire:model="taux_commission" class="border-gray-300 rounded-md text-sm w-24" />
                <span class="text-sm text-gray-500">% commission</span>
            </div>
        </div>
        @error('nom')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
        <button wire:click="save" class="mt-3 bg-indigo-600 text-white text-sm px-4 py-2 rounded-md">Enregistrer</button>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500">
                <th class="py-1"></th><th class="py-1">Nom</th><th>Tél</th><th>Taux</th><th>Clients amenés</th>
                <th class="text-right">Commissions dues (FCFA)</th><th></th>
            </tr></thead>
            <tbody>
            @foreach($membres as $m)
                <tr class="border-t">
                    <td class="py-2 w-6">
                        <button wire:click="basculerMembre({{ $m->id }})" class="text-gray-500" title="Voir ses clients">{{ isset($ouverts[$m->id]) ? '▾' : '▸' }}</button>
                    </td>
                    <td class="py-2 font-medium">{{ $m->nom }}</td>
                    <td>{{ $m->telephone ?? '—' }}</td>
                    <td>{{ $m->taux_commission }}%</td>
                    <td>
                        <button wire:click="basculerMembre({{ $m->id }})" class="text-indigo-600 underline decoration-dotted">{{ $m->clients_count }} client(s)</button>
                    </td>
                    <td class="text-right tabular-nums">{{ number_format($m->ventes_sum_commission_membre ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-right"><button wire:click="edit({{ $m->id }})" class="text-indigo-600 text-xs">Modifier</button></td>
                </tr>
                @if(isset($ouverts[$m->id]))
                <tr class="bg-slate-50">
                    <td></td>
                    <td colspan="6" class="py-3 pr-2">
                        <div class="font-semibold text-xs uppercase tracking-wide text-slate-500 mb-2">Clients amenés par {{ $m->nom }}</div>
                        @if($m->clients->count())
                        <table class="w-full text-sm bg-white rounded border border-slate-200">
                            <thead><tr class="text-left text-gray-500">
                                <th class="py-1 px-2">Nom</th><th>Type</th><th>Tél</th><th>Adresse</th>
                                <th class="text-right">Total acheté (FCFA)</th>
                            </tr></thead>
                            <tbody>
                            @foreach($m->clients as $c)
                                <tr class="border-t">
                                    <td class="py-1 px-2 font-medium">{{ $c->nom }}</td>
                                    <td><span class="px-2 py-0.5 rounded text-xs {{ $c->type === 'grossiste' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ $c->type }}</span></td>
                                    <td>{{ $c->telephone ?? '—' }}</td>
                                    <td class="text-slate-500">{{ $c->adresse ?? '—' }}</td>
                                    <td class="text-right tabular-nums font-medium">{{ number_format($c->total_achete ?? 0, 0, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @else
                            <p class="text-sm text-gray-400">Aucun client amené pour le moment.</p>
                        @endif
                    </td>
                </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
</div>
