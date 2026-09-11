<?php

namespace App\Livewire;

use App\Models\Categorie;
use App\Models\Produit;
use App\Services\StockService;
use Livewire\Component;

class SaisieProduction extends Component
{
    public string $date;
    public string $notes = '';
    /** @var array<int, array{key:string, quantite:int}> */
    public array $lignes = [['key' => '', 'quantite' => 1]];
    public string $message = '';
    public string $erreur = '';

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function ajouterLigne(): void
    {
        $this->lignes[] = ['key' => '', 'quantite' => 1];
    }

    public function retirerLigne(int $index): void
    {
        unset($this->lignes[$index]);
        $this->lignes = array_values($this->lignes);
    }

    public function enregistrer(StockService $service): void
    {
        $this->message = '';
        $this->erreur = '';
        $this->validate([
            'date' => 'required|date',
            'lignes' => 'required|array|min:1',
            'lignes.*.key' => 'required|string',
            'lignes.*.quantite' => 'required|integer|min:1',
        ]);

        try {
            $production = $service->enregistrerProduction(
                array_map(fn ($l) => [...$this->cleVersIds($l['key']), 'quantite' => $l['quantite']], $this->lignes),
                $this->date, $this->notes ?: null, auth()->user()
            );
            $this->message = "Production {$production->reference} enregistrée (+{$production->quantiteTotale()} unités).";
            $this->reset(['notes']);
            $this->lignes = [['key' => '', 'quantite' => 1]];
        } catch (\Throwable $e) {
            $this->erreur = $e->getMessage();
        }
    }

    /** @return array{produit_id:?int, categorie_id:?int} */
    private function cleVersIds(string $key): array
    {
        [$type, $id] = explode(':', $key);

        return $type === 'c'
            ? ['produit_id' => null, 'categorie_id' => (int) $id]
            : ['produit_id' => (int) $id, 'categorie_id' => null];
    }

    /** @return array<int, array{key:string, libelle:string, stock:int}> */
    public function articles(): array
    {
        $articles = [];
        foreach (Categorie::with('produit')->orderBy('id')->get() as $c) {
            $articles[] = ['key' => "c:{$c->id}", 'libelle' => $c->libelleComplet(), 'stock' => $c->stock];
        }
        foreach (Produit::whereDoesntHave('categories')->orderBy('nom')->get() as $p) {
            $articles[] = ['key' => "p:{$p->id}", 'libelle' => $p->nom, 'stock' => $p->stock];
        }

        return $articles;
    }

    /** @var array<int, true> ids des productions dépliées */
    public array $ouvertes = [];

    public function basculerProduction(int $id): void
    {
        if (isset($this->ouvertes[$id])) {
            unset($this->ouvertes[$id]);
        } else {
            $this->ouvertes[$id] = true;
        }
    }

    public function render()
    {
        return view('livewire.saisie-production', [
            'articles' => $this->articles(),
            'productions' => \App\Models\Production::with(['lignes.produit', 'lignes.categorie.produit'])
                ->latest('id')->limit(10)->get(),
        ]);
    }
}
