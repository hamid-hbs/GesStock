<?php

namespace Tests\Feature;

use App\Livewire\DashboardStats;
use App\Models\Client;
use App\Models\Membre;
use App\Models\Produit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_jour_et_graphiques(): void
    {
        $admin = User::factory()->create();
        $produit = Produit::create(['nom' => 'Bidon', 'prix_particulier' => 500, 'prix_grossiste' => 400]);
        $service = new StockService;
        $service->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 100]],
            now()->toDateString(), null, $admin
        );
        $membre = Membre::create(['nom' => 'Ali', 'taux_commission' => 10]);
        $client = Client::create(['nom' => 'Boutique', 'type' => 'grossiste', 'membre_id' => $membre->id]);
        $service->enregistrerVente(
            $client,
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 5]],
            now()->toDateString(), $admin
        );

        $comp = Livewire::test(DashboardStats::class);
        $kpi = $comp->viewData('kpi');

        $this->assertEquals(100, $kpi['produite']['val']);
        $this->assertEquals(5, $kpi['vendue']['val']);
        $this->assertEquals(95, $kpi['restante']['val']);
        $this->assertEquals(2000, $kpi['ca']['val']);
        $this->assertEquals(200, $kpi['commissions']['val']);

        $graphs = $comp->viewData('graphs');
        $this->assertCount(1, $graphs['labels']); // période jour = 1 point
        $this->assertEquals([2000], $graphs['ca']);

        $comp->assertSee('2 000')
            ->assertSee('Top articles vendus')
            ->assertSee('Bidon')
            ->assertSee('Ali');
    }

    public function test_periode_7j_et_tendances(): void
    {
        $admin = User::factory()->create();
        $produit = Produit::create(['nom' => 'Bidon', 'prix_particulier' => 500, 'prix_grossiste' => 400]);
        $service = new StockService;
        // Période précédente : petite vente (pour une tendance calculable)
        $service->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 50]],
            now()->subDays(10)->toDateString(), null, $admin
        );
        $client = Client::create(['nom' => 'C', 'type' => 'particulier']);
        $service->enregistrerVente(
            $client,
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 2]],
            now()->subDays(10)->toDateString(), $admin
        );
        // Période courante : plus grosse
        $service->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 60]],
            now()->toDateString(), null, $admin
        );
        $service->enregistrerVente(
            $client,
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 4]],
            now()->toDateString(), $admin
        );

        $comp = Livewire::test(DashboardStats::class)
            ->set('periode', '7j');

        $kpi = $comp->viewData('kpi');
        $this->assertEquals(60, $kpi['produite']['val']);
        $this->assertEquals(4, $kpi['vendue']['val']);
        $this->assertNotNull($kpi['ca']['trend']); // tendance calculable
        $this->assertCount(7, $comp->viewData('graphs')['labels']);
    }

    public function test_alertes_avec_criticite(): void
    {
        Produit::create(['nom' => 'Vide', 'prix_particulier' => 100, 'prix_grossiste' => 90, 'stock' => 0, 'seuil_alerte' => 5]);
        Produit::create(['nom' => 'Bas', 'prix_particulier' => 100, 'prix_grossiste' => 90, 'stock' => 3, 'seuil_alerte' => 5]);

        Livewire::test(DashboardStats::class)
            ->assertSee('Rupture')
            ->assertSee('Vide')
            ->assertSee('Bas');
    }
}
