<div>
    @if($message)<div class="bg-green-100 text-green-800 text-sm px-4 py-2 rounded mb-4">{{ $message }}</div>@endif

    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <h3 class="font-semibold mb-3">{{ $editingId ? 'Modifier le type' : 'Nouveau type' }}</h3>
        <div class="flex flex-wrap gap-3">
            <input wire:model="nom" placeholder="Nom (ex : Eau)" class="border-gray-300 rounded-md text-sm" />
            <input wire:model="description" placeholder="Description (optionnel)" class="border-gray-300 rounded-md text-sm flex-1 min-w-52" />
            <button wire:click="save" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md">Enregistrer</button>
            @if($editingId)<button wire:click="$set('editingId', null); $set('nom', ''); $set('description', '')" class="text-sm text-gray-500">Annuler</button>@endif
        </div>
        @error('nom')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500"><th class="py-1">Nom</th><th>Produits</th><th></th></tr></thead>
            <tbody>
            @foreach($types as $t)
                <tr class="border-t">
                    <td class="py-2 font-medium">{{ $t->nom }}</td>
                    <td>{{ $t->produits_count }}</td>
                    <td class="text-right space-x-2">
                        <button wire:click="edit({{ $t->id }})" class="text-indigo-600 text-xs">Modifier</button>
                        <button wire:click="delete({{ $t->id }})" wire:confirm="Supprimer ce type ?" class="text-red-600 text-xs">Supprimer</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
