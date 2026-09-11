<div class="space-y-6">
    @if($message)<x-ui-alert tone="success">{{ $message }}</x-ui-alert>@endif
    @if($erreur)<x-ui-alert tone="error">{{ $erreur }}</x-ui-alert>@endif

    <x-page-header title="Productions" subtitle="Entrées de stock issues de la production interne">
        <a href="{{ route('exports.stock') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:bg-slate-100 border border-slate-200 px-3 py-2 rounded-lg transition">Excel stock</a>
    </x-page-header>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 sm:p-6">
        <h3 class="font-semibold text-slate-800 mb-4">Nouvelle production</h3>
        <div class="grid md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Date</label>
                <input type="date" wire:model="date" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                <input wire:model="notes" placeholder="Optionnel" class="border-slate-300 rounded-lg text-sm w-full focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
        </div>

        <label class="block text-xs font-medium text-slate-600 mb-1">Articles produits</label>
        @foreach($lignes as $i => $ligne)
        <div class="flex gap-2 mb-2">
            <select wire:model="lignes.{{ $i }}.key" class="border-slate-300 rounded-lg text-sm flex-1 focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">— Choisir l'article —</option>
                @foreach($articles as $a)
                    <option value="{{ $a['key'] }}">{{ $a['libelle'] }} (stock : {{ $a['stock'] }})</option>
                @endforeach
            </select>
            <input type="number" min="1" wire:model="lignes.{{ $i }}.quantite" title="Quantité" class="border-slate-300 rounded-lg text-sm w-28 tabular-nums focus:border-emerald-500 focus:ring-emerald-500" />
            <button wire:click="retirerLigne({{ $i }})" title="Retirer" class="text-red-500 hover:bg-red-50 px-2 rounded-lg transition">✕</button>
        </div>
        @endforeach
        @error('lignes.*.key')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror

        <div class="flex flex-wrap gap-2 mt-4">
            <button wire:click="ajouterLigne" class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 px-3 py-2 rounded-lg transition">+ Ligne</button>
            <button wire:click="enregistrer" wire:loading.attr="disabled" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-medium px-5 py-2 rounded-lg shadow-sm disabled:opacity-50">
                <span wire:loading.remove>Enregistrer la production</span>
                <span wire:loading>Enregistrement…</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-semibold text-slate-800 mb-3">Dernières productions</h3>
        <div class="space-y-2">
            @foreach($productions as $p)
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <button wire:click="basculerProduction({{ $p->id }})" class="w-full flex flex-wrap items-center gap-x-4 gap-y-1 text-sm px-4 py-2.5 text-left hover:bg-slate-50 transition">
                    <span class="text-slate-400">{{ isset($ouvertes[$p->id]) ? '▾' : '▸' }}</span>
                    <span class="font-medium text-slate-800">{{ $p->reference }}</span>
                    <span class="text-slate-500">{{ $p->date_production->format('d/m/Y') }}</span>
                    <span class="ml-auto tabular-nums text-slate-600">{{ $p->lignes->count() }} ligne(s) · <strong>{{ $p->lignes->sum('quantite') }} unités</strong></span>
                    <a href="{{ route('productions.pdf', $p) }}" target="_blank" wire:click.stop class="text-emerald-700 hover:underline text-xs font-medium">PDF</a>
                </button>
                @if(isset($ouvertes[$p->id]))
                <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                    <table class="w-full text-sm bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <th class="py-2 px-3 font-medium">Article</th><th class="py-2 px-3 font-medium text-right">Quantité</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach($p->lignes as $l)
                            <tr><td class="py-2 px-3 text-slate-700">{{ $l->libelleArticle() }}</td><td class="py-2 px-3 text-right tabular-nums font-medium">+{{ $l->quantite }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
