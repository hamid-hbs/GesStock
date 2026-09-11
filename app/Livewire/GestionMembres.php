<?php

namespace App\Livewire;

use App\Models\Membre;
use Livewire\Component;
use Livewire\WithPagination;

class GestionMembres extends Component
{
    use WithPagination;

    public string $nom = '';
    public string $telephone = '';
    public string $adresse = '';
    public float $taux_commission = 10;
    public ?int $editingId = null;
    public bool $modalMembre = false;
    public string $recherche = '';
    public string $message = '';

    /** @var array<int, true> ids des membres dépliés */
    public array $ouverts = [];

    protected function rules(): array
    {
        return [
            'nom' => 'required|string|max:150',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
            'taux_commission' => 'required|numeric|min:0|max:100',
        ];
    }

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function basculerMembre(int $id): void
    {
        if (isset($this->ouverts[$id])) {
            unset($this->ouverts[$id]);
        } else {
            $this->ouverts[$id] = true;
        }
    }

    public function ouvrirModalMembre(): void
    {
        $this->reset(['nom', 'telephone', 'adresse', 'editingId']);
        $this->taux_commission = 10;
        $this->modalMembre = true;
    }

    public function fermerModalMembre(): void
    {
        $this->modalMembre = false;
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
        $this->message = $this->editingId ? 'Membre modifié.' : 'Membre créé.';
        $this->modalMembre = false;
    }

    public function edit(int $id): void
    {
        $m = Membre::findOrFail($id);
        $this->editingId = $m->id;
        $this->nom = $m->nom;
        $this->telephone = (string) $m->telephone;
        $this->adresse = (string) $m->adresse;
        $this->taux_commission = (float) $m->taux_commission;
        $this->modalMembre = true;
    }

    public function render()
    {
        return view('livewire.gestion-membres', [
            'membres' => Membre::withCount('clients')
                ->withSum('ventes', 'commission_membre')
                ->with(['clients' => fn ($q) => $q->withSum('ventes as total_achete', 'total')->orderBy('nom')])
                ->when($this->recherche, fn ($q) => $q->whereRaw('LOWER(nom) LIKE ?', ['%'.mb_strtolower($this->recherche).'%']))
                ->orderBy('nom')->paginate(15),
        ]);
    }
}
