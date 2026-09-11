<?php

namespace App\Livewire;

use App\Models\Categorie;
use App\Models\Production;
use App\Models\ProductionLigne;
use App\Models\Produit;
use App\Models\Vente;
use App\Models\VenteLigne;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;

class DashboardStats extends Component
{
    public string $periode = 'jour'; // jour|7j|30j|mois
    public string $date;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function updatedPeriode(): void
    {
        $this->dispatch('dashboard-refresh', graphs: $this->donnees()['graphs']);
    }

    public function updatedDate(): void
    {
        $this->dispatch('dashboard-refresh', graphs: $this->donnees()['graphs']);
    }

    /** @return array{Carbon, Carbon} début/fin de période (inclus) */
    private function intervalle(): array
    {
        $fin = Carbon::parse($this->date)->endOfDay();

        return match ($this->periode) {
            '7j' => [$fin->copy()->subDays(6)->startOfDay(), $fin],
            '30j' => [$fin->copy()->subDays(29)->startOfDay(), $fin],
            'mois' => [$fin->copy()->startOfMonth()->startOfDay(), $fin->copy()->endOfMonth()->endOfDay()],
            default => [$fin->copy()->startOfDay(), $fin],
        };
    }

    private function intervallePrecedent(array $intervalle): array
    {
        [$debut, $fin] = $intervalle;
        $duree = $debut->diffInDays($fin) + 1;

        return [$debut->copy()->subDays($duree)->startOfDay(), $debut->copy()->subSecond()->endOfDay()];
    }

    private function pct(float $actuel, float $precedent): ?string
    {
        if ($precedent == 0) {
            return null;
        }
        $v = round(($actuel - $precedent) / $precedent * 100);

        return ($v > 0 ? '+' : '').$v.'%';
    }

    public function donnees(): array
    {
        [$debut, $fin] = $this->intervalle();
        [$pDebut, $pFin] = $this->intervallePrecedent([$debut, $fin]);
        // whereDate >= / <= plutôt que whereBetween : robuste quel que soit
        // le stockage (DATE Postgres vs DATETIME texte sous SQLite).
        $entre = fn ($q, $col, $a, $b) => $q->whereDate($col, '>=', $a->toDateString())
            ->whereDate($col, '<=', $b->toDateString());
        $d = fn ($q, $a, $b) => $entre($q, 'date_vente', $a, $b);
        $dp = fn ($q, $a, $b) => $entre($q, 'date_production', $a, $b);
        $ventesEntre = fn ($a, $b) => $entre(Vente::query(), 'date_vente', $a, $b);

        $produite = ProductionLigne::whereHas('production', fn ($q) => $dp($q, $debut, $fin))->sum('quantite');
        $produitePrev = ProductionLigne::whereHas('production', fn ($q) => $dp($q, $pDebut, $pFin))->sum('quantite');
        $vendue = VenteLigne::whereHas('vente', fn ($q) => $d($q, $debut, $fin))->sum('quantite');
        $venduePrev = VenteLigne::whereHas('vente', fn ($q) => $d($q, $pDebut, $pFin))->sum('quantite');

        $restante = Produit::whereDoesntHave('categories')->sum('stock') + Categorie::sum('stock');

        $ca = (float) $ventesEntre($debut, $fin)->sum('total');
        $caPrev = (float) $ventesEntre($pDebut, $pFin)->sum('total');
        $commissions = (float) $ventesEntre($debut, $fin)->sum('commission_membre');
        $commissionsPrev = (float) $ventesEntre($pDebut, $pFin)->sum('commission_membre');
        $nbVentes = $ventesEntre($debut, $fin)->count();

        // Séries journalières pour les graphiques
        $jours = collect(CarbonPeriod::create($debut->copy()->startOfDay(), $fin->copy()->startOfDay()));
        $labels = $jours->map(fn ($j) => $j->format('d/m'))->all();
        $caParJour = $jours->map(fn ($j) => (float) Vente::whereDate('date_vente', $j->toDateString())->sum('total'))->all();
        $prodParJour = $jours->map(fn ($j) => (int) ProductionLigne::whereHas('production',
            fn ($q) => $q->whereDate('date_production', $j->toDateString()))->sum('quantite'))->all();
        $ventesParJour = $jours->map(fn ($j) => (int) VenteLigne::whereHas('vente',
            fn ($q) => $q->whereDate('date_vente', $j->toDateString()))->sum('quantite'))->all();

        $repartition = [
            'particulier' => (float) $ventesEntre($debut, $fin)
                ->where('type_client', 'particulier')->sum('total'),
            'grossiste' => (float) $ventesEntre($debut, $fin)
                ->where('type_client', 'grossiste')->sum('total'),
        ];

        // Top articles
        $lignes = VenteLigne::with(['produit', 'categorie.produit'])
            ->whereHas('vente', fn ($q) => $d($q, $debut, $fin))->get();
        $topArticles = $lignes->groupBy(fn ($l) => $l->categorie_id ? 'c:'.$l->categorie_id : 'p:'.$l->produit_id)
            ->map(fn ($g) => [
                'libelle' => $g->first()->libelleArticle(),
                'qte' => (int) $g->sum('quantite'),
                'ca' => (float) $g->sum('sous_total'),
            ])->sortByDesc('qte')->take(5)->values();
        $maxTop = max(1, (int) $topArticles->max('qte'));

        // Alertes avec criticité
        $alertes = Categorie::with('produit')->whereColumn('stock', '<=', 'seuil_alerte')
            ->orderBy('stock')->limit(12)->get()
            ->map(fn ($c) => ['libelle' => $c->libelleComplet(), 'stock' => $c->stock, 'critique' => $c->stock <= 0]);
        $alertesProduits = Produit::whereDoesntHave('categories')->whereColumn('stock', '<=', 'seuil_alerte')
            ->orderBy('stock')->limit(12)->get()
            ->map(fn ($p) => ['libelle' => $p->nom, 'stock' => $p->stock, 'critique' => $p->stock <= 0]);
        $nbAlertes = $alertes->count() + $alertesProduits->count();
        $toutesAlertes = $alertes->concat($alertesProduits)->sortBy('stock')->values();

        $dernieresVentes = Vente::with('client')->latest('date_vente')->latest('id')->limit(5)->get();
        $dernieresProductions = Production::latest('date_production')->latest('id')->limit(5)->get();

        $classementMembres = $entre(Vente::with('membre'), 'date_vente', $debut, $fin)
            ->whereNotNull('membre_id')
            ->get()->groupBy('membre_id')
            ->map(fn ($ventes) => [
                'membre' => $ventes->first()->membre,
                'ca' => (float) $ventes->sum('total'),
                'commission' => (float) $ventes->sum('commission_membre'),
            ])->sortByDesc('ca')->values()->take(5);
        $maxMembre = max(1, (float) $classementMembres->max('ca'));

        return [
            // Variables aplaties (les accès imbriqués cassent le parseur d'attributs Blade)
            'produiteVal' => (int) $produite,
            'produiteTrend' => $this->pct((float) $produite, (float) $produitePrev),
            'vendueVal' => (int) $vendue,
            'vendueTrend' => $this->pct((float) $vendue, (float) $venduePrev),
            'restanteVal' => (int) $restante,
            'caVal' => $ca,
            'caTrend' => $this->pct($ca, $caPrev),
            'commissionsVal' => $commissions,
            'commissionsTrend' => $this->pct($commissions, $commissionsPrev),
            'nbAlertes' => $nbAlertes,
            'kpi' => [
                'produite' => ['val' => (int) $produite, 'trend' => $this->pct((float) $produite, (float) $produitePrev)],
                'vendue' => ['val' => (int) $vendue, 'trend' => $this->pct((float) $vendue, (float) $venduePrev)],
                'restante' => ['val' => (int) $restante, 'trend' => null],
                'ca' => ['val' => $ca, 'trend' => $this->pct($ca, $caPrev)],
                'commissions' => ['val' => $commissions, 'trend' => $this->pct($commissions, $commissionsPrev)],
                'alertes' => ['val' => $nbAlertes, 'trend' => null],
            ],
            'nbVentes' => $nbVentes,
            'graphs' => [
                'labels' => $labels,
                'ca' => $caParJour,
                'prod' => $prodParJour,
                'ventes' => $ventesParJour,
                'repartition' => [$repartition['particulier'], $repartition['grossiste']],
            ],
            'topArticles' => $topArticles,
            'maxTop' => $maxTop,
            'alertes' => $alertes,
            'alertesProduits' => $alertesProduits,
            'toutesAlertes' => $toutesAlertes,
            'dernieresVentes' => $dernieresVentes,
            'dernieresProductions' => $dernieresProductions,
            'classementMembres' => $classementMembres,
            'maxMembre' => $maxMembre,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-stats', $this->donnees());
    }
}
