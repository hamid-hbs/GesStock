<?php

namespace App\Livewire;

use App\Models\Type;
use Illuminate\Validation\Rule;
use Livewire\Component;

class GestionTypes extends Component
{
    public string $nom = '';
    public string $description = '';
    public ?int $editingId = null;
    public string $message = '';

    protected function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100', Rule::unique('types', 'nom')->ignore($this->editingId)],
            'description' => 'nullable|string|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();
        Type::updateOrCreate(['id' => $this->editingId], [
            'nom' => $this->nom, 'description' => $this->description ?: null,
        ]);
        $this->message = 'Type enregistré.';
        $this->reset(['nom', 'description', 'editingId']);
    }

    public function edit(int $id): void
    {
        $t = Type::findOrFail($id);
        $this->editingId = $t->id;
        $this->nom = $t->nom;
        $this->description = (string) $t->description;
    }

    public function delete(int $id): void
    {
        Type::findOrFail($id)->delete();
        $this->message = 'Type supprimé (produits détachés).';
    }

    public function render()
    {
        return view('livewire.gestion-types', [
            'types' => Type::withCount('produits')->orderBy('nom')->get(),
        ]);
    }
}
