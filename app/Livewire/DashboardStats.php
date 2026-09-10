<?php

namespace App\Livewire;

use App\Models\Categorie;
use App\Models\Production;
use App\Models\ProductionLigne;
use App\Models\Produit;
use App\Models\Vente;
use App\Models\VenteLigne;
use Livewire\Component;

class DashboardStats extends Component
{
    public string $date;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function render()
    {
        $produite = ProductionLigne::whereHas('production',
            fn ($q) => $q->whereDate('date_production', $this->date))->sum('quantite');

        $vendue = VenteLigne::whereHas('vente',
            fn ($q) => $q->whereDate('date_vente', $this->date))->sum('quantite');

        $restanteProduits = Produit::whereDoesntHave('categories')->sum('stock');
        $restanteCategories = Categorie::sum('stock');
        $restante = $restanteProduits + $restanteCategories;

        $caJour = (float) Vente::whereDate('date_vente', $this->date)->sum('total');
        $caMois = (float) Vente::whereYear('date_vente', substr($this->date, 0, 4))
            ->whereMonth('date_vente', substr($this->date, 5, 2))->sum('total');
        $commissionsJour = (float) Vente::whereDate('date_vente', $this->date)->sum('commission_membre');

        $alertes = Categorie::with('produit')->whereColumn('stock', '<=', 'seuil_alerte')
            ->orderBy('stock')->limit(10)->get();
        $alertesProduits = Produit::whereDoesntHave('categories')
            ->whereColumn('stock', '<=', 'seuil_alerte')->orderBy('stock')->limit(10)->get();

        $dernieresVentes = Vente::with('client')->latest('date_vente')->latest('id')->limit(5)->get();
        $dernieresProductions = Production::latest('date_production')->latest('id')->limit(5)->get();

        $classementMembres = Vente::with('membre')
            ->whereDate('date_vente', $this->date)
            ->whereNotNull('membre_id')
            ->get()->groupBy('membre_id')
            ->map(fn ($ventes) => [
                'membre' => $ventes->first()->membre,
                'ca' => $ventes->sum('total'),
                'commission' => $ventes->sum('commission_membre'),
            ])->sortByDesc('ca')->values();

        return view('livewire.dashboard-stats', [
            'produite' => (int) $produite,
            'vendue' => (int) $vendue,
            'restante' => (int) $restante,
            'caJour' => $caJour,
            'caMois' => $caMois,
            'commissionsJour' => $commissionsJour,
            'alertes' => $alertes,
            'alertesProduits' => $alertesProduits,
            'dernieresVentes' => $dernieresVentes,
            'dernieresProductions' => $dernieresProductions,
            'classementMembres' => $classementMembres,
        ]);
    }
}
