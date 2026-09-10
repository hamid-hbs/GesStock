<div>
    @if($message)<div class="bg-green-100 text-green-800 text-sm px-4 py-2 rounded mb-4">{{ $message }}</div>@endif
    @if($erreur)<div class="bg-red-100 text-red-800 text-sm px-4 py-2 rounded mb-4">{{ $erreur }}</div>@endif

    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <h3 class="font-semibold mb-3">Nouvelle production (entrée de stock)</h3>
        <div class="grid md:grid-cols-3 gap-3 mb-4">
            <input type="date" wire:model="date" class="border-gray-300 rounded-md text-sm" />
            <input wire:model="notes" placeholder="Notes (optionnel)" class="border-gray-300 rounded-md text-sm md:col-span-2" />
        </div>

        @foreach($lignes as $i => $ligne)
        <div class="flex gap-2 mb-2">
            <select wire:model="lignes.{{ $i }}.key" class="border-gray-300 rounded-md text-sm flex-1">
                <option value="">— Choisir l'article —</option>
                @foreach($articles as $a)
                    <option value="{{ $a['key'] }}">{{ $a['libelle'] }} (stock : {{ $a['stock'] }})</option>
                @endforeach
            </select>
            <input type="number" min="1" wire:model="lignes.{{ $i }}.quantite" class="border-gray-300 rounded-md text-sm w-28" />
            <button wire:click="retirerLigne({{ $i }})" class="text-red-600 text-sm px-2">✕</button>
        </div>
        @endforeach
        @error('lignes.*.key')<div class="text-red-600 text-xs">{{ $message }}</div>@enderror

        <div class="flex gap-3 mt-3">
            <button wire:click="ajouterLigne" class="text-sm text-indigo-600 border border-indigo-200 px-3 py-2 rounded-md">+ Ligne</button>
            <button wire:click="enregistrer" class="bg-emerald-600 text-white text-sm px-4 py-2 rounded-md">Enregistrer la production</button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4">
        <h3 class="font-semibold mb-3">Dernières productions</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="py-1">Réf</th><th>Date</th><th class="text-right">Lignes</th></tr></thead>
            <tbody>
            @foreach($productions as $p)
                <tr class="border-t"><td class="py-1">{{ $p->reference }}</td><td>{{ $p->date_production->format('d/m/Y') }}</td><td class="text-right">{{ $p->lignes_count }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
