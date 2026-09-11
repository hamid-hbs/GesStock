<div class="space-y-6">
    @if($message)<x-ui-alert tone="success">{{ $message }}</x-ui-alert>@endif

    <x-page-header title="Clients" subtitle="{{ $clients->total() }} client(s) · tarif auto selon type">
        <input wire:model.live.debounce.300ms="recherche" placeholder="Rechercher…" class="border-slate-300 rounded-lg text-sm w-52 focus:border-emerald-500 focus:ring-emerald-500" />
        <button wire:click="ouvrirModalClient" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nouveau client
        </button>
    </x-page-header>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase tracking-wider text-slate-500">
                <th class="py-2">Nom</th><th class="py-2">Type</th><th class="py-2">Apporteur</th><th class="py-2">Contact</th>
                <th class="py-2 text-right">Total acheté (FCFA)</th><th></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach($clients as $c)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-2.5 font-medium text-slate-800">{{ $c->nom }}</td>
                    <td><x-status-badge :tone="$c->type === 'grossiste' ? 'blue' : 'slate'">{{ $c->type }}</x-status-badge></td>
                    <td class="text-slate-600">{{ $c->membre->nom ?? '—' }}</td>
                    <td class="text-slate-500 text-xs">{{ $c->telephone ?? '—' }}{{ $c->adresse ? ' · '.$c->adresse : '' }}</td>
                    <td class="text-right tabular-nums font-medium">{{ number_format($c->ventes_sum_total ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-right"><button wire:click="edit({{ $c->id }})" class="text-slate-500 hover:bg-slate-100 text-xs font-medium px-2 py-1.5 rounded-lg transition">Modifier</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($clients->isEmpty())
            <x-empty-state message="Aucun client trouvé." />
        @endif
        <div class="mt-3">{{ $clients->links() }}</div>
    </div>

    @if($modalClient)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-50" wire:click="fermerModalClient"></div>
            <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md my-8">
                <div class="flex items-start justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <div>
                            <h3 class="font-semibold text-lg text-slate-800 leading-tight">{{ $editingId ? 'Modifier le client' : 'Nouveau client' }}</h3>
                            <p class="text-xs text-slate-500">Le type détermine le tarif appliqué</p>
                        </div>
                    </div>
                    <button wire:click="fermerModalClient" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition">✕</button>
                </div>
                <div class="grid gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nom *</label>
                        <input wire:model="nom" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                            <select wire:model="type" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="particulier">Particulier</option>
                                <option value="grossiste">Grossiste</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Apporteur</label>
                            <select wire:model="membre_id" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Aucun</option>
                                @foreach($membres as $m)
                                    <option value="{{ $m->id }}">{{ $m->nom }} ({{ $m->taux_commission }}%)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Téléphone</label>
                            <input wire:model="telephone" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Adresse</label>
                            <input wire:model="adresse" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    @error('nom')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="fermerModalClient" class="text-sm font-medium text-slate-500 hover:bg-slate-100 px-4 py-2 rounded-lg transition">Annuler</button>
                    <button wire:click="save" class="bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-5 py-2 rounded-lg shadow-sm">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
