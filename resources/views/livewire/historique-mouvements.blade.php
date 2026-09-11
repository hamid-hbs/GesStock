<div class="space-y-6">
    <x-page-header title="Mouvements de stock" subtitle="Journal d'audit : chaque entrée et sortie, lecture seule">
        <a href="{{ route('exports.stock') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:bg-slate-100 border border-slate-200 px-3 py-2 rounded-lg transition">Excel stock</a>
    </x-page-header>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
            <select wire:model.live="type" class="border-slate-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Tous types</option>
                <option value="production">Production</option>
                <option value="vente">Vente</option>
                <option value="ajustement">Ajustement</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Référence</label>
            <input wire:model.live.debounce.300ms="recherche" placeholder="Ex : VTE-2026-…" class="border-slate-300 rounded-lg text-sm w-48 focus:border-emerald-500 focus:ring-emerald-500" />
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Du</label>
            <input type="date" wire:model.live="dateDe" class="border-slate-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Au</label>
            <input type="date" wire:model.live="dateA" class="border-slate-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>
        <button wire:click="reinitialiser" class="text-sm font-medium text-slate-500 hover:bg-slate-100 px-3 py-2 rounded-lg transition">Réinitialiser</button>
        <span class="ml-auto text-xs text-slate-400">{{ $mouvements->total() }} mouvement(s)</span>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase tracking-wider text-slate-500">
                <th class="py-2">#</th><th class="py-2">Type</th><th class="py-2">Article</th><th class="py-2 text-right">Qté</th>
                <th class="py-2 text-right">Avant → Après</th><th class="py-2">Document</th><th class="py-2">Par</th><th class="py-2">Le</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach($mouvements as $m)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-2 text-slate-400 tabular-nums">{{ $m->id }}</td>
                    <td><x-status-badge :tone="$m->type === 'production' ? 'emerald' : ($m->type === 'vente' ? 'blue' : 'slate')">{{ $m->type }}</x-status-badge></td>
                    <td class="text-slate-700">{{ $m->libelleArticle() }}</td>
                    <td class="text-right tabular-nums font-medium">{{ $m->type === 'vente' ? '−' : '+' }}{{ $m->quantite }}</td>
                    <td class="text-right tabular-nums text-slate-500">{{ $m->stock_avant }} → <strong class="text-slate-700">{{ $m->stock_apres }}</strong></td>
                    <td class="text-slate-600">{{ $m->reference_doc }}</td>
                    <td class="text-slate-500">{{ $m->user->name ?? '—' }}</td>
                    <td class="text-slate-400 text-xs">{{ $m->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($mouvements->isEmpty())
            <x-empty-state message="Aucun mouvement avec ces filtres." />
        @endif
        <div class="mt-3">{{ $mouvements->links() }}</div>
    </div>
</div>
