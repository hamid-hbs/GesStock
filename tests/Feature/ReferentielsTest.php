<?php

namespace Tests\Feature;

use App\Livewire\GestionClients;
use App\Livewire\GestionMembres;
use App\Livewire\GestionTypes;
use App\Livewire\HistoriqueMouvements;
use App\Livewire\SaisieProduction;
use App\Models\Client;
use App\Models\Membre;
use App\Models\Produit;
use App\Models\Type;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReferentielsTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_modale_creation_et_recherche(): void
    {
        $membre = Membre::create(['nom' => 'Ali', 'taux_commission' => 10]);
        Client::create(['nom' => 'Zzz Autre', 'type' => 'particulier']);

        Livewire::test(GestionClients::class)
            ->call('ouvrirModalClient')
            ->assertSet('modalClient', true)
            ->set('nom', 'Boutique X')
            ->set('type', 'grossiste')
            ->set('membre_id', $membre->id)
            ->call('save')
            ->assertSet('modalClient', false)
            ->assertSee('Client créé.');

        $this->assertDatabaseHas('clients', ['nom' => 'Boutique X', 'type' => 'grossiste']);

        Livewire::test(GestionClients::class)
            ->set('recherche', 'boutique')
            ->assertSee('Boutique X')
            ->assertDontSee('Zzz Autre');
    }

    public function test_membre_modale_creation_et_recherche(): void
    {
        Membre::create(['nom' => 'Zzz Autre', 'taux_commission' => 5]);

        Livewire::test(GestionMembres::class)
            ->call('ouvrirModalMembre')
            ->assertSet('modalMembre', true)
            ->set('nom', 'Fatou')
            ->set('taux_commission', 7)
            ->call('save')
            ->assertSet('modalMembre', false)
            ->assertSee('Membre créé.');

        $this->assertDatabaseHas('membres', ['nom' => 'Fatou']);

        Livewire::test(GestionMembres::class)
            ->set('recherche', 'fatou')
            ->assertSee('Fatou')
            ->assertDontSee('Zzz Autre');
    }

    public function test_type_modale_creation(): void
    {
        Livewire::test(GestionTypes::class)
            ->call('ouvrirModalType')
            ->assertSet('modalType', true)
            ->set('nom', 'Jus')
            ->call('save')
            ->assertSet('modalType', false)
            ->assertSee('Type créé.');

        $this->assertDatabaseHas('types', ['nom' => 'Jus']);
    }

    public function test_mouvements_filtre_date(): void
    {
        $admin = User::factory()->create();
        $produit = Produit::create(['nom' => 'Bidon', 'prix_particulier' => 500, 'prix_grossiste' => 400]);
        $service = new StockService;
        $service->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 10]],
            now()->subDays(10)->toDateString(), null, $admin
        );
        $service->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 20]],
            now()->toDateString(), null, $admin
        );

        $comp = Livewire::test(HistoriqueMouvements::class);
        $this->assertEquals(2, $comp->viewData('mouvements')->total());

        // Le filtre porte sur la date de saisie : on antidate le 1er mouvement
        $premierId = \App\Models\MouvementStock::oldest('id')->first()->id;
        \App\Models\MouvementStock::whereKey($premierId)
            ->update(['created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)]);

        $comp->set('dateDe', now()->toDateString());
        $this->assertEquals(1, $comp->viewData('mouvements')->total());

        $comp->set('dateDe', '')->set('type', 'vente');
        $this->assertEquals(0, $comp->viewData('mouvements')->total());
    }

    public function test_production_accordeon_detail_lignes(): void
    {
        $admin = User::factory()->create();
        $produit = Produit::create(['nom' => 'Bidon', 'prix_particulier' => 500, 'prix_grossiste' => 400]);
        (new StockService)->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 15]],
            now()->toDateString(), null, $admin
        );

        Livewire::actingAs($admin)->test(SaisieProduction::class)
            ->assertSee('PROD-')
            ->assertDontSee('+15') // détail replié : la quantité n'apparaît pas
            ->call('basculerProduction', \App\Models\Production::first()->id)
            ->assertSee('Bidon')
            ->assertSee('+15');
    }
}
