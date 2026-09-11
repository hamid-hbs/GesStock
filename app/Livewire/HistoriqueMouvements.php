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
    public string $dateDe = '';
    public string $dateA = '';

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function updatingDateDe(): void
    {
        $this->resetPage();
    }

    public function updatingDateA(): void
    {
        $this->resetPage();
    }

    public function reinitialiser(): void
    {
        $this->reset(['type', 'recherche', 'dateDe', 'dateA']);
        $this->resetPage();
    }

    public function render()
    {
        $mouvements = MouvementStock::with(['produit', 'categorie.produit', 'user'])
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->recherche, fn ($q) => $q->whereRaw('LOWER(reference_doc) LIKE ?', ['%'.mb_strtolower($this->recherche).'%']))
            ->when($this->dateDe, fn ($q) => $q->whereDate('created_at', '>=', $this->dateDe))
            ->when($this->dateA, fn ($q) => $q->whereDate('created_at', '<=', $this->dateA))
            ->latest('id')
            ->paginate(20);

        return view('livewire.historique-mouvements', ['mouvements' => $mouvements]);
    }
}
