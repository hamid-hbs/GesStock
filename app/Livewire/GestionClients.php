<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Membre;
use Livewire\Component;
use Livewire\WithPagination;

class GestionClients extends Component
{
    use WithPagination;

    public string $nom = '';
    public string $type = 'particulier';
    public ?int $membre_id = null;
    public string $telephone = '';
    public string $adresse = '';
    public ?int $editingId = null;
    public bool $modalClient = false;
    public string $recherche = '';
    public string $message = '';

    protected function rules(): array
    {
        return [
            'nom' => 'required|string|max:150',
            'type' => 'required|in:particulier,grossiste',
            'membre_id' => 'nullable|exists:membres,id',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
        ];
    }

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function ouvrirModalClient(): void
    {
        $this->reset(['nom', 'membre_id', 'telephone', 'adresse', 'editingId']);
        $this->type = 'particulier';
        $this->modalClient = true;
    }

    public function fermerModalClient(): void
    {
        $this->modalClient = false;
    }

    public function save(): void
    {
        $this->validate();
        Client::updateOrCreate(['id' => $this->editingId], [
            'nom' => $this->nom,
            'type' => $this->type,
            'membre_id' => $this->membre_id,
            'telephone' => $this->telephone ?: null,
            'adresse' => $this->adresse ?: null,
        ]);
        $this->message = $this->editingId ? 'Client modifié.' : 'Client créé.';
        $this->modalClient = false;
    }

    public function edit(int $id): void
    {
        $c = Client::findOrFail($id);
        $this->editingId = $c->id;
        $this->nom = $c->nom;
        $this->type = $c->type;
        $this->membre_id = $c->membre_id;
        $this->telephone = (string) $c->telephone;
        $this->adresse = (string) $c->adresse;
        $this->modalClient = true;
    }

    public function render()
    {
        return view('livewire.gestion-clients', [
            'clients' => Client::with('membre')->withSum('ventes', 'total')
                ->when($this->recherche, fn ($q) => $q->whereRaw('LOWER(nom) LIKE ?', ['%'.mb_strtolower($this->recherche).'%']))
                ->orderBy('nom')->paginate(15),
            'membres' => Membre::where('actif', true)->orderBy('nom')->get(),
        ]);
    }
}
