<div class="space-y-6">
    @if($message)<x-ui-alert tone="success">{{ $message }}</x-ui-alert>@endif

    <x-page-header title="Types de produits" subtitle="Familles : Eau, Jus… (optionnel par produit)">
        <button wire:click="ouvrirModalType" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nouveau type
        </button>
    </x-page-header>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase tracking-wider text-slate-500">
                <th class="py-2">Nom</th><th class="py-2">Description</th><th class="py-2 text-right">Produits</th><th></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach($types as $t)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-2.5 font-medium text-slate-800">{{ $t->nom }}</td>
                    <td class="text-slate-500">{{ $t->description ?? '—' }}</td>
                    <td class="text-right tabular-nums">{{ $t->produits_count }}</td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <button wire:click="edit({{ $t->id }})" class="text-slate-500 hover:bg-slate-100 text-xs font-medium px-2 py-1.5 rounded-lg transition">Modifier</button>
                            <button wire:click="delete({{ $t->id }})" wire:confirm="Supprimer ce type ? Les produits seront détachés." class="text-red-600 hover:bg-red-50 text-xs font-medium px-2 py-1.5 rounded-lg transition">Suppr.</button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($types->isEmpty())
            <x-empty-state message="Aucun type. Crée ta première famille de produits." />
        @endif
    </div>

    @if($modalType)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-50" wire:click="fermerModalType"></div>
            <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md my-8">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h3 class="font-semibold text-lg text-slate-800 leading-tight">{{ $editingId ? 'Modifier le type' : 'Nouveau type' }}</h3>
                        <p class="text-xs text-slate-500">Famille de produits</p>
                    </div>
                    <button wire:click="fermerModalType" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition">✕</button>
                </div>
                <div class="grid gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nom *</label>
                        <input wire:model="nom" placeholder="Ex : Eau" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                        <input wire:model="description" placeholder="Optionnel" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    @error('nom')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="fermerModalType" class="text-sm font-medium text-slate-500 hover:bg-slate-100 px-4 py-2 rounded-lg transition">Annuler</button>
                    <button wire:click="save" class="bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-5 py-2 rounded-lg shadow-sm">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
