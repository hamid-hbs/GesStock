<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Membre;
use Livewire\Component;

class GestionClients extends Component
{
    public string $nom = '';
    public string $type = 'particulier';
    public ?int $membre_id = null;
    public string $telephone = '';
    public string $adresse = '';
    public ?int $editingId = null;
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
        $this->message = 'Client enregistré.';
        $this->reset(['nom', 'type', 'membre_id', 'telephone', 'adresse', 'editingId']);
        $this->type = 'particulier';
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
    }

    public function render()
    {
        return view('livewire.gestion-clients', [
            'clients' => Client::with('membre')->withSum('ventes', 'total')->orderBy('nom')->get(),
            'membres' => Membre::where('actif', true)->orderBy('nom')->get(),
        ]);
    }
}
