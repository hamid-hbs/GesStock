<div>
    @if($message)<div class="bg-green-100 text-green-800 text-sm px-4 py-2 rounded mb-4">{{ $message }}</div>@endif

    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <h3 class="font-semibold mb-3">{{ $editingId ? 'Modifier le client' : 'Nouveau client' }}</h3>
        <div class="grid md:grid-cols-5 gap-3">
            <input wire:model="nom" placeholder="Nom *" class="border-gray-300 rounded-md text-sm" />
            <select wire:model="type" class="border-gray-300 rounded-md text-sm">
                <option value="particulier">Particulier</option>
                <option value="grossiste">Grossiste</option>
            </select>
            <select wire:model="membre_id" class="border-gray-300 rounded-md text-sm">
                <option value="">Sans membre apporteur</option>
                @foreach($membres as $m)
                    <option value="{{ $m->id }}">{{ $m->nom }} ({{ $m->taux_commission }}%)</option>
                @endforeach
            </select>
            <input wire:model="telephone" placeholder="Téléphone" class="border-gray-300 rounded-md text-sm" />
            <input wire:model="adresse" placeholder="Adresse" class="border-gray-300 rounded-md text-sm" />
        </div>
        @error('nom')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
        <button wire:click="save" class="mt-3 bg-indigo-600 text-white text-sm px-4 py-2 rounded-md">Enregistrer</button>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500">
                <th class="py-1">Nom</th><th>Type</th><th>Apporteur</th>
                <th class="text-right">Total acheté</th><th></th>
            </tr></thead>
            <tbody>
            @foreach($clients as $c)
                <tr class="border-t">
                    <td class="py-2 font-medium">{{ $c->nom }}</td>
                    <td><span class="px-2 py-0.5 rounded text-xs {{ $c->type === 'grossiste' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ $c->type }}</span></td>
                    <td>{{ $c->membre->nom ?? '—' }}</td>
                    <td class="text-right">{{ number_format($c->ventes_sum_total ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-right"><button wire:click="edit({{ $c->id }})" class="text-indigo-600 text-xs">Modifier</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
