<div>
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4 flex flex-wrap gap-3">
        <select wire:model.live="type" class="border-gray-300 rounded-md text-sm">
            <option value="">Tous types</option>
            <option value="production">Production</option>
            <option value="vente">Vente</option>
            <option value="ajustement">Ajustement</option>
        </select>
        <input wire:model.live.debounce.300ms="recherche" placeholder="Référence document…" class="border-gray-300 rounded-md text-sm" />
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500">
                <th class="py-1">#</th><th>Type</th><th>Article</th><th class="text-right">Qté</th>
                <th class="text-right">Avant → Après</th><th>Document</th><th>Par</th>
            </tr></thead>
            <tbody>
            @foreach($mouvements as $m)
                <tr class="border-t">
                    <td class="py-1 text-gray-400">{{ $m->id }}</td>
                    <td><span class="px-2 py-0.5 rounded text-xs {{ $m->type === 'production' ? 'bg-emerald-100 text-emerald-700' : ($m->type === 'vente' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">{{ $m->type }}</span></td>
                    <td>{{ $m->libelleArticle() }}</td>
                    <td class="text-right">{{ $m->quantite }}</td>
                    <td class="text-right">{{ $m->stock_avant }} → {{ $m->stock_apres }}</td>
                    <td>{{ $m->reference_doc }}</td>
                    <td class="text-gray-500">{{ $m->user->name ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-3">{{ $mouvements->links() }}</div>
    </div>
</div>
