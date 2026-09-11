<div class="space-y-6">
    @if($message)<x-ui-alert tone="success">{{ $message }}</x-ui-alert>@endif

    <x-page-header title="Membres vendeurs" subtitle="Apporteurs : taux de commission et clients amenés">
        <input wire:model.live.debounce.300ms="recherche" placeholder="Rechercher…" class="border-slate-300 rounded-lg text-sm w-52 focus:border-emerald-500 focus:ring-emerald-500" />
        <button wire:click="ouvrirModalMembre" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nouveau membre
        </button>
    </x-page-header>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase tracking-wider text-slate-500">
                <th class="py-2 w-8"></th><th class="py-2">Nom</th><th class="py-2">Contact</th><th class="py-2">Taux</th><th class="py-2">Clients</th>
                <th class="py-2 text-right">Commissions dues (FCFA)</th><th></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach($membres as $m)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-2.5">
                        <button wire:click="basculerMembre({{ $m->id }})" class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition text-xs" title="Voir ses clients">{{ isset($ouverts[$m->id]) ? '▾' : '▸' }}</button>
                    </td>
                    <td class="py-2.5 font-medium text-slate-800">{{ $m->nom }}</td>
                    <td class="text-slate-500 text-xs">{{ $m->telephone ?? '—' }}{{ $m->adresse ? ' · '.$m->adresse : '' }}</td>
                    <td><x-status-badge tone="amber">{{ $m->taux_commission }}%</x-status-badge></td>
                    <td><button wire:click="basculerMembre({{ $m->id }})" class="text-emerald-700 hover:underline decoration-dotted font-medium">{{ $m->clients_count }} client(s)</button></td>
                    <td class="text-right tabular-nums font-medium">{{ number_format($m->ventes_sum_commission_membre ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-right"><button wire:click="edit({{ $m->id }})" class="text-slate-500 hover:bg-slate-100 text-xs font-medium px-2 py-1.5 rounded-lg transition">Modifier</button></td>
                </tr>
                @if(isset($ouverts[$m->id]))
                <tr class="bg-slate-50">
                    <td></td>
                    <td colspan="6" class="py-3 pr-2">
                        <div class="font-semibold text-xs uppercase tracking-wider text-slate-500 mb-2">Clients amenés par {{ $m->nom }}</div>
                        @if($m->clients->count())
                        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                                    <th class="py-2 px-3 font-medium">Nom</th><th class="py-2 px-3 font-medium">Type</th><th class="py-2 px-3 font-medium">Tél</th><th class="py-2 px-3 font-medium">Adresse</th>
                                    <th class="py-2 px-3 font-medium text-right">Total acheté (FCFA)</th>
                                </tr></thead>
                                <tbody class="divide-y divide-slate-100">
                                @foreach($m->clients as $c)
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-3 font-medium text-slate-800">{{ $c->nom }}</td>
                                        <td class="px-3"><x-status-badge :tone="$c->type === 'grossiste' ? 'blue' : 'slate'">{{ $c->type }}</x-status-badge></td>
                                        <td class="px-3 text-slate-500">{{ $c->telephone ?? '—' }}</td>
                                        <td class="px-3 text-slate-500">{{ $c->adresse ?? '—' }}</td>
                                        <td class="py-2 px-3 text-right tabular-nums font-medium">{{ number_format($c->total_achete ?? 0, 0, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                            <p class="text-sm text-slate-400">Aucun client amené pour le moment.</p>
                        @endif
                    </td>
                </tr>
                @endif
            @endforeach
            </tbody>
        </table>
        @if($membres->isEmpty())
            <x-empty-state message="Aucun membre trouvé." />
        @endif
        <div class="mt-3">{{ $membres->links() }}</div>
    </div>

    @if($modalMembre)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-50" wire:click="fermerModalMembre"></div>
            <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md my-8">
                <div class="flex items-start justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <div>
                            <h3 class="font-semibold text-lg text-slate-800 leading-tight">{{ $editingId ? 'Modifier le membre' : 'Nouveau membre' }}</h3>
                            <p class="text-xs text-slate-500">Vendeur / apporteur de clients</p>
                        </div>
                    </div>
                    <button wire:click="fermerModalMembre" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition">✕</button>
                </div>
                <div class="grid gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nom *</label>
                        <input wire:model="nom" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Téléphone</label>
                            <input wire:model="telephone" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Commission % *</label>
                            <input type="number" step="0.01" min="0" max="100" wire:model="taux_commission" class="border-slate-300 rounded-lg text-sm w-full tabular-nums focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Adresse</label>
                        <input wire:model="adresse" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    @error('nom')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="fermerModalMembre" class="text-sm font-medium text-slate-500 hover:bg-slate-100 px-4 py-2 rounded-lg transition">Annuler</button>
                    <button wire:click="save" class="bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-5 py-2 rounded-lg shadow-sm">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
