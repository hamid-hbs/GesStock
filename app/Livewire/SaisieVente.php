<?php

namespace App\Livewire;

use App\Models\Categorie;
use App\Models\Client;
use App\Models\Produit;
use App\Services\StockService;
use Livewire\Component;

class SaisieVente extends Component
{
    public ?int $client_id = null;
    public string $date;
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

    /** @var array<int, true> ids des ventes dépliées */
    public array $ventesOuvertes = [];

    public function basculerVente(int $id): void
    {
        if (isset($this->ventesOuvertes[$id])) {
            unset($this->ventesOuvertes[$id]);
        } else {
            $this->ventesOuvertes[$id] = true;
        }
    }
    public function enregistrer(StockService $service): void
    {
        $this->message = '';
        $this->erreur = '';
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'lignes' => 'required|array|min:1',
            'lignes.*.key' => 'required|string',
            'lignes.*.quantite' => 'required|integer|min:1',
        ]);

        try {
            $vente = $service->enregistrerVente(
                Client::with('membre')->findOrFail($this->client_id),
                array_map(fn ($l) => [...$this->cleVersIds($l['key']), 'quantite' => $l['quantite']], $this->lignes),
                $this->date, auth()->user()
            );
            $this->message = "Vente {$vente->reference} enregistrée : ".number_format($vente->total, 0, ',', ' ').
                " (commission membre : ".number_format($vente->commission_membre, 0, ',', ' ').").";
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

    private function articleParCle(string $key): Produit|Categorie|null
    {
        if (! str_contains($key, ':')) {
            return null;
        }
        [$type, $id] = explode(':', $key);

        return $type === 'c'
            ? Categorie::with('produit')->find($id)
            : Produit::find($id);
    }

    public function render()
    {
        $articles = [];
        foreach (Categorie::with('produit')->orderBy('id')->get() as $c) {
            $articles[] = ['key' => "c:{$c->id}", 'libelle' => $c->libelleComplet(), 'stock' => $c->stock, 'pp' => (float) $c->prix_particulier, 'pg' => (float) $c->prix_grossiste];
        }
        foreach (Produit::whereDoesntHave('categories')->orderBy('nom')->get() as $p) {
            $articles[] = ['key' => "p:{$p->id}", 'libelle' => $p->nom, 'stock' => $p->stock, 'pp' => (float) $p->prix_particulier, 'pg' => (float) $p->prix_grossiste];
        }

        $client = $this->client_id ? Client::with('membre')->find($this->client_id) : null;
        $total = 0;
        $apercu = [];
        foreach ($this->lignes as $l) {
            $article = $l['key'] ? $this->articleParCle($l['key']) : null;
            if ($article && $client) {
                $prix = $article->prixPour($client->type);
                $st = $prix * $l['quantite'];
                $total += $st;
                $apercu[] = ['libelle' => $article instanceof Categorie ? $article->libelleComplet() : $article->nom, 'prix' => $prix, 'sous_total' => $st];
            }
        }
        $commission = $client?->membre ? round($total * ((float) $client->membre->taux_commission) / 100, 2) : 0;

        return view('livewire.saisie-vente', [
            'articles' => $articles,
            'clients' => Client::with('membre')->orderBy('nom')->get(),
            'clientSel' => $client,
            'apercu' => $apercu,
            'total' => $total,
            'commission' => $commission,
            'ventes' => \App\Models\Vente::with(['client.membre', 'membre', 'lignes.produit', 'lignes.categorie.produit'])
                ->latest('id')->limit(10)->get(),
        ]);
    }
}
