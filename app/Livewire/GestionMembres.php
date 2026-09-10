<?php

namespace App\Livewire;

use App\Models\Membre;
use Livewire\Component;

class GestionMembres extends Component
{
    public string $nom = '';
    public string $telephone = '';
    public string $adresse = '';
    public float $taux_commission = 10;
    public ?int $editingId = null;
    public string $message = '';

    protected function rules(): array
    {
        return [
            'nom' => 'required|string|max:150',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
            'taux_commission' => 'required|numeric|min:0|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();
        Membre::updateOrCreate(['id' => $this->editingId], [
            'nom' => $this->nom,
            'telephone' => $this->telephone ?: null,
            'adresse' => $this->adresse ?: null,
            'taux_commission' => $this->taux_commission,
        ]);
        $this->message = 'Membre enregistré.';
        $this->reset(['nom', 'telephone', 'adresse', 'editingId']);
        $this->taux_commission = 10;
    }

    public function edit(int $id): void
    {
        $m = Membre::findOrFail($id);
        $this->editingId = $m->id;
        $this->nom = $m->nom;
        $this->telephone = (string) $m->telephone;
        $this->adresse = (string) $m->adresse;
        $this->taux_commission = (float) $m->taux_commission;
    }

    /** @var array<int, true> ids des membres dépliés */
    public array $ouverts = [];

    public function basculerMembre(int $id): void
    {
        if (isset($this->ouverts[$id])) {
            unset($this->ouverts[$id]);
        } else {
            $this->ouverts[$id] = true;
        }
    }

    public function render()
    {
        return view('livewire.gestion-membres', [
            'membres' => Membre::withCount('clients')
                ->withSum('ventes', 'commission_membre')
                ->with(['clients' => fn ($q) => $q->withSum('ventes as total_achete', 'total')->orderBy('nom')])
                ->orderBy('nom')->get(),
        ]);
    }
}
