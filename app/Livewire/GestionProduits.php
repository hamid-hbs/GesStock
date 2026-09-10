<?php

namespace App\Livewire;

use App\Models\Categorie;
use App\Models\MouvementStock;
use App\Models\ProductionLigne;
use App\Models\Produit;
use App\Models\Type;
use App\Models\VenteLigne;
use Illuminate\Validation\Rule;
use Livewire\Component;

class GestionProduits extends Component
{
    // Champs modale produit
    public ?int $type_id = null;
    public string $nom = '';
    public ?float $prix_particulier = null;
    public ?float $prix_grossiste = null;
    public int $seuil_alerte = 5;
    public ?int $editingId = null;
    public bool $modalProduit = false;

    // Champs modale catégorie (+ contexte produit parent)
    public ?int $produitSelectionne = null;
    public string $cat_nom = '';
    public ?float $cat_pp = null;
    public ?float $cat_pg = null;
    public ?int $cat_editingId = null;
    public bool $modalCategorie = false;

    // Accordéon : ids des produits dépliés
    public array $ouverts = [];

    // Modale types
    public bool $modalType = false;
    public string $type_nom = '';
    public string $type_description = '';
    public ?int $type_editingId = null;

    public string $message = '';
    public string $erreur = '';

    // ---------- Modale produit ----------

    public function ouvrirModalProduit(): void
    {
        $this->reset(['type_id', 'nom', 'prix_particulier', 'prix_grossiste', 'editingId']);
        $this->seuil_alerte = 5;
        $this->modalProduit = true;
    }

    public function fermerModalProduit(): void
    {
        $this->modalProduit = false;
    }

    public function edit(int $id): void
    {
        $p = Produit::findOrFail($id);
        $this->editingId = $p->id;
        $this->type_id = $p->type_id;
        $this->nom = $p->nom;
        $this->prix_particulier = $p->prix_particulier !== null ? (float) $p->prix_particulier : null;
        $this->prix_grossiste = $p->prix_grossiste !== null ? (float) $p->prix_grossiste : null;
        $this->seuil_alerte = $p->seuil_alerte;
        $this->modalProduit = true;
    }

    public function sauverProduit(): void
    {
        $this->message = '';
        $this->erreur = '';
        $this->validate([
            'type_id' => 'nullable|exists:types,id',
            'nom' => 'required|string|max:150',
            'prix_particulier' => 'nullable|numeric|min:0',
            'prix_grossiste' => 'nullable|numeric|min:0',
            'seuil_alerte' => 'required|integer|min:0',
        ]);

        Produit::updateOrCreate(['id' => $this->editingId], [
            'type_id' => $this->type_id,
            'nom' => $this->nom,
            'prix_particulier' => $this->prix_particulier,
            'prix_grossiste' => $this->prix_grossiste,
            'seuil_alerte' => $this->seuil_alerte,
        ]);
        $this->message = $this->editingId ? 'Produit modifié.' : 'Produit créé.';
        $this->modalProduit = false;
    }

    public function supprimerProduit(int $id): void
    {
        $this->message = '';
        $this->erreur = '';
        $p = Produit::with('categories')->findOrFail($id);

        $raisons = $this->raisonsBlocageProduit($p);
        if ($raisons !== []) {
            $this->erreur = 'Suppression impossible de « '.$p->nom.' » : '.implode(', ', $raisons).'.';
            return;
        }

        $p->delete(); // catégories suivent via FK cascade
        unset($this->ouverts[$id]);
        $this->message = 'Produit supprimé.';
    }

    /** @return list<string> */
    private function raisonsBlocageProduit(Produit $p): array
    {
        $raisons = [];
        $catIds = $p->categories->pluck('id')->all();

        $stock = $p->stock + (int) $p->categories->sum('stock');
        if ($stock > 0) {
            $raisons[] = "stock restant ({$stock})";
        }

        $nbProd = ProductionLigne::where('produit_id', $p->id)->orWhereIn('categorie_id', $catIds)->count();
        $nbVentes = VenteLigne::where('produit_id', $p->id)->orWhereIn('categorie_id', $catIds)->count();
        $nbMouv = MouvementStock::where('produit_id', $p->id)->orWhereIn('categorie_id', $catIds)->count();
        $total = $nbProd + $nbVentes + $nbMouv;
        if ($total > 0) {
            $raisons[] = "{$total} ligne(s) d'historique";
        }

        return $raisons;
    }

    // ---------- Accordéon ----------

    public function basculerProduit(int $id): void
    {
        if (isset($this->ouverts[$id])) {
            unset($this->ouverts[$id]);
        } else {
            $this->ouverts[$id] = true;
        }
    }

    // ---------- Modale catégorie ----------

    public function ouvrirModalCategorie(int $produitId, ?int $catId = null): void
    {
        $this->produitSelectionne = $produitId;
        if ($catId) {
            $c = Categorie::findOrFail($catId);
            $this->cat_editingId = $c->id;
            $this->cat_nom = $c->nom;
            $this->cat_pp = (float) $c->prix_particulier;
            $this->cat_pg = (float) $c->prix_grossiste;
        } else {
            $this->reset(['cat_nom', 'cat_pp', 'cat_pg', 'cat_editingId']);
        }
        $this->modalCategorie = true;
    }

    public function fermerModalCategorie(): void
    {
        $this->modalCategorie = false;
    }

    public function sauverCategorie(): void
    {
        $this->message = '';
        $this->erreur = '';
        $this->validate([
            'produitSelectionne' => 'required|exists:produits,id',
            'cat_nom' => 'required|string|max:50',
            'cat_pp' => 'required|numeric|min:0',
            'cat_pg' => 'required|numeric|min:0',
        ]);

        $existe = Categorie::where('produit_id', $this->produitSelectionne)
            ->where('nom', $this->cat_nom)
            ->where('id', '!=', $this->cat_editingId ?? 0)
            ->exists();
        if ($existe) {
            $this->erreur = 'Ce produit a déjà une catégorie « '.$this->cat_nom.' ».';
            return;
        }

        Categorie::updateOrCreate(['id' => $this->cat_editingId], [
            'produit_id' => $this->produitSelectionne,
            'nom' => $this->cat_nom,
            'prix_particulier' => $this->cat_pp,
            'prix_grossiste' => $this->cat_pg,
        ]);
        $this->message = $this->cat_editingId ? 'Catégorie modifiée.' : 'Catégorie ajoutée.';
        $this->modalCategorie = false;
    }

    public function supprimerCategorie(int $id): void
    {
        $this->message = '';
        $this->erreur = '';
        $c = Categorie::findOrFail($id);

        $raisons = [];
        if ($c->stock > 0) {
            $raisons[] = "stock restant ({$c->stock})";
        }
        $total = ProductionLigne::where('categorie_id', $c->id)->count()
            + VenteLigne::where('categorie_id', $c->id)->count()
            + MouvementStock::where('categorie_id', $c->id)->count();
        if ($total > 0) {
            $raisons[] = "{$total} ligne(s) d'historique";
        }
        if ($raisons !== []) {
            $this->erreur = 'Suppression impossible de « '.$c->nom.' » : '.implode(', ', $raisons).'.';
            return;
        }

        $c->delete();
        $this->message = 'Catégorie supprimée.';
    }

    // ---------- Modale Types ----------

    public function ouvrirModalType(): void
    {
        $this->reset(['type_nom', 'type_description', 'type_editingId']);
        $this->modalType = true;
    }

    public function fermerModalType(): void
    {
        $this->modalType = false;
    }

    public function editType(int $id): void
    {
        $t = Type::findOrFail($id);
        $this->type_editingId = $t->id;
        $this->type_nom = $t->nom;
        $this->type_description = (string) $t->description;
        $this->modalType = true;
    }

    public function sauverType(): void
    {
        $this->validate([
            'type_nom' => ['required', 'string', 'max:100', Rule::unique('types', 'nom')->ignore($this->type_editingId)],
            'type_description' => 'nullable|string|max:500',
        ]);

        $type = Type::updateOrCreate(['id' => $this->type_editingId], [
            'nom' => $this->type_nom,
            'description' => $this->type_description ?: null,
        ]);
        $this->type_id = $type->id; // auto-sélection dans le formulaire produit
        $this->modalType = false;
        $this->message = 'Type enregistré et sélectionné.';
    }

    public function supprimerType(int $id): void
    {
        Type::findOrFail($id)->delete(); // produits détachés (type_id nullable)
        $this->message = 'Type supprimé (produits détachés).';
    }

    public function render()
    {
        return view('livewire.gestion-produits', [
            'produits' => Produit::with(['type', 'categories'])->orderBy('nom')->get(),
            'types' => Type::withCount('produits')->orderBy('nom')->get(),
        ]);
    }
}
