<?php

namespace App\Livewire;

use App\Models\MouvementStock;
use Livewire\Component;
use Livewire\WithPagination;

class HistoriqueMouvements extends Component
{
    use WithPagination;

    public string $type = '';
    public string $recherche = '';

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $mouvements = MouvementStock::with(['produit', 'categorie.produit', 'user'])
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->recherche, fn ($q) => $q->where('reference_doc', 'ilike', '%'.$this->recherche.'%'))
            ->latest('id')
            ->paginate(20);

        return view('livewire.historique-mouvements', ['mouvements' => $mouvements]);
    }
}
