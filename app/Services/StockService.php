<?php

namespace App\Services;

use App\Models\Categorie;
use App\Models\Client;
use App\Models\MouvementStock;
use App\Models\Production;
use App\Models\Produit;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Point d'entrée unique pour tout changement de stock.
 * Chaque opération est transactionnelle et journalisée dans mouvements_stock.
 *
 * Ligne d'article : ['produit_id' => ?int, 'categorie_id' => ?int, 'quantite' => int]
 * Règle : exactement un des deux IDs doit être renseigné.
 */
class StockService
{
    /**
     * Enregistre une production interne → augmente le stock.
     *
     * @param  array<int, array{produit_id:?int, categorie_id:?int, quantite:int}>  $lignes
     */
    public function enregistrerProduction(array $lignes, string $date, ?string $notes, User $user): Production
    {
        return DB::transaction(function () use ($lignes, $date, $notes, $user) {
            $production = Production::create([
                'reference' => $this->prochaineReference('PROD', Production::class),
                'date_production' => $date,
                'notes' => $notes,
                'user_id' => $user->id,
            ]);

            foreach ($lignes as $ligne) {
                $this->validerLigne($ligne);
                $cible = $this->cibleVerrouillee($ligne);

                $avant = $cible->stock;
                $cible->increment('stock', $ligne['quantite']);

                $production->lignes()->create($ligne);

                MouvementStock::create([
                    'produit_id' => $ligne['produit_id'] ?? null,
                    'categorie_id' => $ligne['categorie_id'] ?? null,
                    'type' => MouvementStock::TYPE_PRODUCTION,
                    'quantite' => $ligne['quantite'],
                    'stock_avant' => $avant,
                    'stock_apres' => $avant + $ligne['quantite'],
                    'reference_doc' => $production->reference,
                    'user_id' => $user->id,
                ]);
            }

            return $production->load('lignes');
        });
    }

    /**
     * Enregistre une vente → prix auto selon type client, commission auto, diminue le stock.
     *
     * @param  array<int, array{produit_id:?int, categorie_id:?int, quantite:int}>  $lignes
     */
    public function enregistrerVente(Client $client, array $lignes, string $date, User $user): Vente
    {
        return DB::transaction(function () use ($client, $lignes, $date, $user) {
            $client->loadMissing('membre');
            $typeClient = $client->type;
            $total = 0;
            $lignesPreparees = [];

            // 1er passage : validation stock + calcul prix (sans écrire, pour échouer vite)
            foreach ($lignes as $ligne) {
                $this->validerLigne($ligne);
                $cible = $this->cibleVerrouillee($ligne);

                if ($cible->stock < $ligne['quantite']) {
                    $libelle = $cible instanceof Categorie ? $cible->libelleComplet() : $cible->nom;
                    throw new InvalidArgumentException(
                        "Stock insuffisant pour « {$libelle} » (dispo : {$cible->stock}, demandé : {$ligne['quantite']})."
                    );
                }

                $prix = $cible->prixPour($typeClient);
                if ($prix === null) {
                    throw new InvalidArgumentException('Prix non renseigné pour cet article.');
                }

                $sousTotal = $prix * $ligne['quantite'];
                $total += $sousTotal;
                $lignesPreparees[] = [...$ligne, 'prix_unitaire' => $prix, 'sous_total' => $sousTotal, 'cible' => $cible];
            }

            $membre = $client->membre;
            $commission = $membre ? round($total * ((float) $membre->taux_commission) / 100, 2) : 0;

            $vente = Vente::create([
                'reference' => $this->prochaineReference('VTE', Vente::class),
                'client_id' => $client->id,
                'membre_id' => $membre?->id,
                'type_client' => $typeClient,
                'total' => $total,
                'commission_membre' => $commission,
                'date_vente' => $date,
                'user_id' => $user->id,
            ]);

            // 2e passage : écriture stock + lignes + mouvements
            foreach ($lignesPreparees as $ligne) {
                /** @var Produit|Categorie $cible */
                $cible = $ligne['cible'];
                $avant = $cible->stock;
                $cible->decrement('stock', $ligne['quantite']);

                $vente->lignes()->create([
                    'produit_id' => $ligne['produit_id'] ?? null,
                    'categorie_id' => $ligne['categorie_id'] ?? null,
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'sous_total' => $ligne['sous_total'],
                ]);

                MouvementStock::create([
                    'produit_id' => $ligne['produit_id'] ?? null,
                    'categorie_id' => $ligne['categorie_id'] ?? null,
                    'type' => MouvementStock::TYPE_VENTE,
                    'quantite' => $ligne['quantite'],
                    'stock_avant' => $avant,
                    'stock_apres' => $avant - $ligne['quantite'],
                    'reference_doc' => $vente->reference,
                    'user_id' => $user->id,
                ]);
            }

            return $vente->load('lignes');
        });
    }

    /**
     * Ajustement manuel de stock (correction d'inventaire) vers une quantité cible.
     */
    public function ajuster(?int $produitId, ?int $categorieId, int $nouveauStock, string $motif, User $user): MouvementStock
    {
        return DB::transaction(function () use ($produitId, $categorieId, $nouveauStock, $motif, $user) {
            $ligne = ['produit_id' => $produitId, 'categorie_id' => $categorieId, 'quantite' => 1];
            $this->validerLigne($ligne);
            $cible = $this->cibleVerrouillee($ligne);

            $avant = $cible->stock;
            $cible->stock = $nouveauStock;
            $cible->save();

            return MouvementStock::create([
                'produit_id' => $produitId,
                'categorie_id' => $categorieId,
                'type' => MouvementStock::TYPE_AJUSTEMENT,
                'quantite' => abs($nouveauStock - $avant),
                'stock_avant' => $avant,
                'stock_apres' => $nouveauStock,
                'reference_doc' => $motif,
                'user_id' => $user->id,
            ]);
        });
    }

    /** @param  array{produit_id:?int, categorie_id:?int, quantite:int}  $ligne */
    private function validerLigne(array $ligne): void
    {
        $aProduit = ! empty($ligne['produit_id']);
        $aCategorie = ! empty($ligne['categorie_id']);

        if ($aProduit === $aCategorie) {
            throw new InvalidArgumentException('Chaque ligne doit viser soit un produit, soit une catégorie.');
        }

        if (($ligne['quantite'] ?? 0) < 1) {
            throw new InvalidArgumentException('La quantité doit être au moins 1.');
        }
    }

    /** Retourne le modèle Produit ou Categorie verrouillé en écriture. */
    private function cibleVerrouillee(array $ligne): Produit|Categorie
    {
        if (! empty($ligne['categorie_id'])) {
            return Categorie::whereKey($ligne['categorie_id'])->lockForUpdate()->firstOrFail();
        }

        $produit = Produit::whereKey($ligne['produit_id'])->lockForUpdate()->firstOrFail();

        if ($produit->categories()->exists()) {
            throw new InvalidArgumentException(
                "« {$produit->nom} » a des catégories : vendez/produisez une catégorie, pas le produit."
            );
        }

        return $produit;
    }

    private function prochaineReference(string $prefixe, string $modele): string
    {
        $annee = now()->format('Y');
        $compteur = $modele::where('reference', 'like', "{$prefixe}-{$annee}-%")->count() + 1;

        return sprintf('%s-%s-%04d', $prefixe, $annee, $compteur);
    }
}
