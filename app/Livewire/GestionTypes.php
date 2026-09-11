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
    public bool $modalType = false;
    public string $message = '';

    public function ouvrirModalType(): void
    {
        $this->reset(['nom', 'description', 'editingId']);
        $this->modalType = true;
    }

    public function fermerModalType(): void
    {
        $this->modalType = false;
    }

    public function save(): void
    {
        $this->validate([
            'nom' => ['required', 'string', 'max:100', Rule::unique('types', 'nom')->ignore($this->editingId)],
            'description' => 'nullable|string|max:500',
        ]);
        Type::updateOrCreate(['id' => $this->editingId], [
            'nom' => $this->nom, 'description' => $this->description ?: null,
        ]);
        $this->message = $this->editingId ? 'Type modifié.' : 'Type créé.';
        $this->modalType = false;
    }

    public function edit(int $id): void
    {
        $t = Type::findOrFail($id);
        $this->editingId = $t->id;
        $this->nom = $t->nom;
        $this->description = (string) $t->description;
        $this->modalType = true;
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
