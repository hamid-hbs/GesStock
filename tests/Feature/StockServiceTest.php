<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Client;
use App\Models\Membre;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Type;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockService;
        $this->admin = User::factory()->create();
    }

    public function test_production_augmente_le_stock_et_journalise(): void
    {
        $produit = $this->creeProduitSimple();

        $production = $this->service->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 200]],
            now()->toDateString(), null, $this->admin
        );

        $this->assertEquals(200, $produit->fresh()->stock);
        $this->assertStringStartsWith('PROD-', $production->reference);
        $this->assertDatabaseHas('mouvements_stock', [
            'type' => MouvementStock::TYPE_PRODUCTION,
            'produit_id' => $produit->id,
            'stock_avant' => 0,
            'stock_apres' => 200,
        ]);
    }

    public function test_vente_applique_prix_grossiste_et_commission_membre(): void
    {
        $produit = $this->creeProduitSimple(); // part: 500, gros: 400
        $this->service->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 100]],
            now()->toDateString(), null, $this->admin
        );

        $membre = Membre::create(['nom' => 'Ali', 'taux_commission' => 10]);
        $client = Client::create(['nom' => 'Boutique X', 'type' => Client::TYPE_GROSSISTE, 'membre_id' => $membre->id]);

        $vente = $this->service->enregistrerVente(
            $client,
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 5]],
            now()->toDateString(), $this->admin
        );

        // 5 x 400 = 2000, commission 10% = 200
        $this->assertEquals(2000, (float) $vente->total);
        $this->assertEquals(200, (float) $vente->commission_membre);
        $this->assertEquals('grossiste', $vente->type_client);
        $this->assertEquals($membre->id, $vente->membre_id);
        $this->assertEquals(95, $produit->fresh()->stock);
        $this->assertEquals(400, (float) $vente->lignes->first()->prix_unitaire);
    }

    public function test_vente_particulier_utilise_prix_particulier_sans_commission(): void
    {
        $produit = $this->creeProduitSimple();
        $this->service->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 50]],
            now()->toDateString(), null, $this->admin
        );

        $client = Client::create(['nom' => 'M. Diallo', 'type' => Client::TYPE_PARTICULIER]);

        $vente = $this->service->enregistrerVente(
            $client,
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 2]],
            now()->toDateString(), $this->admin
        );

        $this->assertEquals(1000, (float) $vente->total); // 2 x 500
        $this->assertEquals(0, (float) $vente->commission_membre);
        $this->assertNull($vente->membre_id);
    }

    public function test_vente_bloquee_si_stock_insuffisant(): void
    {
        $produit = $this->creeProduitSimple();
        $client = Client::create(['nom' => 'Boutique Y', 'type' => Client::TYPE_GROSSISTE]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Stock insuffisant/');

        $this->service->enregistrerVente(
            $client,
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 10]],
            now()->toDateString(), $this->admin
        );
    }

    public function test_vente_categorie_et_interdiction_produit_avec_categories(): void
    {
        $type = Type::create(['nom' => 'Eau']);
        $produit = Produit::create(['nom' => 'Eau alcaline', 'type_id' => $type->id]);
        $cat = Categorie::create([
            'produit_id' => $produit->id, 'nom' => '1L',
            'prix_particulier' => 500, 'prix_grossiste' => 400,
        ]);

        $this->service->enregistrerProduction(
            [['produit_id' => null, 'categorie_id' => $cat->id, 'quantite' => 60]],
            now()->toDateString(), null, $this->admin
        );
        $this->assertEquals(60, $cat->fresh()->stock);

        // Vendre le produit parent doit échouer
        $client = Client::create(['nom' => 'Z', 'type' => Client::TYPE_PARTICULIER]);
        try {
            $this->service->enregistrerVente(
                $client,
                [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 1]],
                now()->toDateString(), $this->admin
            );
            $this->fail('La vente du produit parent aurait dû échouer.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('catégories', $e->getMessage());
        }

        // Vendre la catégorie fonctionne
        $vente = $this->service->enregistrerVente(
            $client,
            [['produit_id' => null, 'categorie_id' => $cat->id, 'quantite' => 3]],
            now()->toDateString(), $this->admin
        );
        $this->assertEquals(1500, (float) $vente->total);
        $this->assertEquals(57, $cat->fresh()->stock);
    }

    private function creeProduitSimple(): Produit
    {
        return Produit::create([
            'nom' => 'Bidon test',
            'prix_particulier' => 500,
            'prix_grossiste' => 400,
        ]);
    }
}
