<div class="space-y-6">
    @if($message)
    <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-500 text-emerald-800 text-sm px-4 py-3 rounded-lg shadow-sm">
        <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ $message }}</span>
    </div>
    @endif
    @if($erreur)
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 border-l-4 border-l-red-500 text-red-800 text-sm px-4 py-3 rounded-lg shadow-sm">
        <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>{{ $erreur }}</span>
    </div>
    @endif

    <x-page-header title="Produits & catégories" subtitle="Articles vendables : produit seul ou décliné en formats">
        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-xs font-medium px-2.5 py-1">{{ $produits->count() }} produit(s)</span>
        <button wire:click="ouvrirModalProduit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nouveau produit
        </button>
    </x-page-header>

    <div class="space-y-5">
        @forelse($produits as $p)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="flex items-center gap-4 px-5 py-4 sm:px-6">
                <button wire:click="basculerProduit({{ $p->id }})" title="Déplier les catégories"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition text-sm">
                    {{ isset($ouverts[$p->id]) ? '▾' : '▸' }}
                </button>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold text-slate-800">{{ $p->nom }}</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 text-xs font-medium px-2.5 py-0.5">{{ $p->type->nom ?? 'Sans type' }}</span>
                        @if($p->categories->count())
                            <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 text-xs font-medium px-2.5 py-0.5">{{ $p->categories->count() }} catégorie(s)</span>
                        @endif
                    </div>
                    @if($p->categories->count())
                        <div class="mt-1 text-xs text-slate-500">Stock total : <span class="font-semibold text-slate-700 tabular-nums">{{ $p->categories->sum('stock') }}</span></div>
                    @else
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                            <span>Part. <span class="font-semibold text-slate-700 tabular-nums">{{ number_format($p->prix_particulier ?? 0, 0, ',', ' ') }}</span></span>
                            <span>Gros <span class="font-semibold text-slate-700 tabular-nums">{{ number_format($p->prix_grossiste ?? 0, 0, ',', ' ') }}</span></span>
                            @if($p->stock <= 0)
                                <span class="inline-flex items-center rounded-full bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-0.5">Rupture</span>
                            @elseif($p->stock <= $p->seuil_alerte)
                                <span class="inline-flex items-center rounded-full bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-0.5">Bas · {{ $p->stock }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold px-2.5 py-0.5">En stock · {{ $p->stock }}</span>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    <button wire:click="basculerProduit({{ $p->id }})" class="inline-flex items-center gap-1 text-emerald-700 hover:bg-emerald-50 text-xs font-medium px-2.5 py-1.5 rounded-lg transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Catégories
                    </button>
                    <button wire:click="edit({{ $p->id }})" title="Modifier" class="inline-flex items-center gap-1 text-slate-500 hover:bg-slate-100 text-xs font-medium px-2.5 py-1.5 rounded-lg transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Modifier
                    </button>
                    <button wire:click="supprimerProduit({{ $p->id }})" wire:confirm="Supprimer ce produit ? (refusé s'il a du stock ou un historique)" title="Supprimer" class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 text-xs font-medium px-2.5 py-1.5 rounded-lg transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Suppr.
                    </button>
                </div>
            </div>

            @if(isset($ouverts[$p->id]))
            <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-slate-700">Catégories de « {{ $p->nom }} »</h4>
                    <button wire:click="ouvrirModalCategorie({{ $p->id }})" class="inline-flex items-center gap-1.5 text-emerald-700 hover:bg-emerald-100 bg-emerald-50 border border-emerald-200 text-xs font-medium px-3 py-1.5 rounded-lg transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Ajouter
                    </button>
                </div>
                @if($p->categories->count())
                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                                <th class="py-2.5 px-4 font-medium">Nom</th>
                                <th class="py-2.5 px-4 font-medium text-right">P. part</th>
                                <th class="py-2.5 px-4 font-medium text-right">P. gros</th>
                                <th class="py-2.5 px-4 font-medium text-right">Stock</th>
                                <th class="py-2.5 px-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach($p->categories as $c)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-2.5 px-4 font-medium text-slate-800">{{ $c->nom }}</td>
                                <td class="py-2.5 px-4 text-right tabular-nums text-slate-600">{{ number_format($c->prix_particulier, 0, ',', ' ') }}</td>
                                <td class="py-2.5 px-4 text-right tabular-nums text-slate-600">{{ number_format($c->prix_grossiste, 0, ',', ' ') }}</td>
                                <td class="py-2.5 px-4 text-right">
                                    @if($c->stock <= 0)
                                        <span class="inline-flex items-center rounded-full bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-0.5">Rupture</span>
                                    @elseif($c->stock <= $c->seuil_alerte)
                                        <span class="inline-flex items-center rounded-full bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-0.5">Bas · {{ $c->stock }}</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold px-2.5 py-0.5 tabular-nums">{{ $c->stock }}</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4">
                                    <div class="flex justify-end gap-1">
                                        <button wire:click="ouvrirModalCategorie({{ $p->id }}, {{ $c->id }})" title="Modifier" class="text-slate-500 hover:bg-slate-100 text-xs font-medium px-2 py-1.5 rounded-lg transition">Modifier</button>
                                        <button wire:click="supprimerCategorie({{ $c->id }})" wire:confirm="Supprimer cette catégorie ? (refusé si stock ou historique)" title="Supprimer" class="text-red-600 hover:bg-red-50 text-xs font-medium px-2 py-1.5 rounded-lg transition">Suppr.</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p class="text-sm text-slate-400 bg-white border border-dashed border-slate-200 rounded-lg px-4 py-3">Aucune catégorie — les prix et le stock sont gérés sur le produit.</p>
                @endif
            </div>
            @endif
        </div>
        @empty
            <div class="bg-white rounded-xl border border-dashed border-slate-300 px-6 py-10 text-center">
                <p class="text-sm text-slate-400 mb-3">Aucun produit pour le moment.</p>
                <button wire:click="ouvrirModalProduit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">＋ Créer le premier produit</button>
            </div>
        @endforelse
    </div>

    @if($modalProduit)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-50" wire:click="fermerModalProduit"></div>
            <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md my-8">
                <div class="flex items-start justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
                        <div>
                            <h3 class="font-semibold text-lg text-slate-800 leading-tight">{{ $editingId ? 'Modifier le produit' : 'Nouveau produit' }}</h3>
                            <p class="text-xs text-slate-500">Renseigne les informations de l'article</p>
                        </div>
                    </div>
                    <button wire:click="fermerModalProduit" title="Fermer" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition">✕</button>
                </div>
                <div class="grid gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                        <div class="flex gap-2">
                            <select wire:model="type_id" class="border-slate-300 rounded-lg text-sm flex-1 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Sans type</option>
                                @foreach($types as $t)<option value="{{ $t->id }}">{{ $t->nom }}</option>@endforeach
                            </select>
                            <button wire:click="ouvrirModalType" title="Gérer les types" class="bg-slate-100 hover:bg-slate-200 text-sm px-3 rounded-lg transition">+ Type</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nom *</label>
                        <input wire:model="nom" placeholder="Ex : Eau alcaline" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Prix particulier</label>
                            <input type="number" step="0.01" min="0" wire:model="prix_particulier" placeholder="0" class="border-slate-300 rounded-lg text-sm w-full tabular-nums focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Prix grossiste</label>
                            <input type="number" step="0.01" min="0" wire:model="prix_grossiste" placeholder="0" class="border-slate-300 rounded-lg text-sm w-full tabular-nums focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Seuil d'alerte</label>
                        <input type="number" min="0" wire:model="seuil_alerte" class="border-slate-300 rounded-lg text-sm w-full tabular-nums focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">Les prix servent uniquement si le produit n'a <strong>pas</strong> de catégories.</p>
                    @error('nom')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="fermerModalProduit" class="text-sm font-medium text-slate-500 hover:bg-slate-100 px-4 py-2 rounded-lg transition">Annuler</button>
                    <button wire:click="sauverProduit" class="bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-5 py-2 rounded-lg shadow-sm">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($modalCategorie)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-50" wire:click="fermerModalCategorie"></div>
            <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md my-8">
                <div class="flex items-start justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        </span>
                        <div>
                            <h3 class="font-semibold text-lg text-slate-800 leading-tight">{{ $cat_editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</h3>
                            <p class="text-xs text-slate-500">Format / déclinaison de l'article</p>
                        </div>
                    </div>
                    <button wire:click="fermerModalCategorie" title="Fermer" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition">✕</button>
                </div>
                <div class="grid gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nom *</label>
                        <input wire:model="cat_nom" placeholder="Ex : 1L" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Prix particulier *</label>
                            <input type="number" step="0.01" min="0" wire:model="cat_pp" placeholder="0" class="border-slate-300 rounded-lg text-sm w-full tabular-nums focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Prix grossiste *</label>
                            <input type="number" step="0.01" min="0" wire:model="cat_pg" placeholder="0" class="border-slate-300 rounded-lg text-sm w-full tabular-nums focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    @error('cat_nom')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="fermerModalCategorie" class="text-sm font-medium text-slate-500 hover:bg-slate-100 px-4 py-2 rounded-lg transition">Annuler</button>
                    <button wire:click="sauverCategorie" class="bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-5 py-2 rounded-lg shadow-sm">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($modalType)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-50" wire:click="fermerModalType"></div>
            <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md my-8">
                <div class="flex items-start justify-between mb-1">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </span>
                        <div>
                            <h3 class="font-semibold text-lg text-slate-800 leading-tight">{{ $type_editingId ? 'Modifier le type' : 'Nouveau type' }}</h3>
                            <p class="text-xs text-slate-500">Le type créé sera auto-sélectionné dans le produit.</p>
                        </div>
                    </div>
                    <button wire:click="fermerModalType" title="Fermer" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition">✕</button>
                </div>
                <div class="grid gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nom *</label>
                        <input wire:model="type_nom" placeholder="Ex : Eau" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                        <input wire:model="type_description" placeholder="Optionnel" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                    @error('type_nom')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="fermerModalType" class="text-sm font-medium text-slate-500 hover:bg-slate-100 px-4 py-2 rounded-lg transition">Fermer</button>
                    <button wire:click="sauverType" class="bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-5 py-2 rounded-lg shadow-sm">Enregistrer</button>
                </div>
                <div class="border-t border-slate-200 mt-5 pt-4 max-h-48 overflow-y-auto space-y-1">
                    @foreach($types as $t)
                        <div class="flex items-center justify-between text-sm py-1.5 px-2 rounded-lg hover:bg-slate-50">
                            <span class="text-slate-700">{{ $t->nom }} <span class="text-slate-400">({{ $t->produits_count }})</span></span>
                            <span class="space-x-1">
                                <button wire:click="editType({{ $t->id }})" class="text-slate-500 hover:bg-slate-100 text-xs font-medium px-2 py-1 rounded-lg transition">Modifier</button>
                                <button wire:click="supprimerType({{ $t->id }})" wire:confirm="Supprimer ce type ? Les produits seront détachés." class="text-red-600 hover:bg-red-50 text-xs font-medium px-2 py-1 rounded-lg transition">Suppr.</button>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
