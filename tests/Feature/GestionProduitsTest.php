<?php

namespace Tests\Feature;

use App\Livewire\GestionProduits;
use App\Models\Categorie;
use App\Models\Client;
use App\Models\Produit;
use App\Models\Type;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionProduitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_produit_via_modale(): void
    {
        Livewire::test(GestionProduits::class)
            ->call('ouvrirModalProduit')
            ->assertSet('modalProduit', true)
            ->set('nom', 'Bidon')
            ->set('prix_particulier', 500)
            ->set('prix_grossiste', 400)
            ->call('sauverProduit')
            ->assertSet('modalProduit', false)
            ->assertSee('Produit créé.');

        $this->assertDatabaseHas('produits', ['nom' => 'Bidon']);
    }

    public function test_edition_produit_pre_remplit_modale(): void
    {
        $p = Produit::create(['nom' => 'A', 'prix_particulier' => 100, 'prix_grossiste' => 90]);

        Livewire::test(GestionProduits::class)
            ->call('edit', $p->id)
            ->assertSet('modalProduit', true)
            ->assertSet('nom', 'A')
            ->assertSet('prix_particulier', 100)
            ->set('nom', 'A modifié')
            ->call('sauverProduit')
            ->assertSee('Produit modifié.');

        $this->assertEquals('A modifié', $p->fresh()->nom);
    }

    public function test_accordeon_affiche_categories_produit_deplie(): void
    {
        $p = Produit::create(['nom' => 'A']);
        Categorie::create(['produit_id' => $p->id, 'nom' => '1L', 'prix_particulier' => 500, 'prix_grossiste' => 400]);

        $comp = Livewire::test(GestionProduits::class);
        $comp->assertDontSee('P. part'); // section repliée : pas de tableau catégories
        $comp->call('basculerProduit', $p->id)
            ->assertSee('1L')
            ->assertSee('P. part');
    }

    public function test_supprimer_produit_bloque_avec_stock(): void
    {
        $p = Produit::create(['nom' => 'A', 'prix_particulier' => 100, 'prix_grossiste' => 90, 'stock' => 5]);

        Livewire::test(GestionProduits::class)
            ->call('supprimerProduit', $p->id)
            ->assertSee('Suppression impossible')
            ->assertSee('stock restant (5)');

        $this->assertDatabaseHas('produits', ['id' => $p->id]);
    }

    public function test_supprimer_produit_bloque_avec_historique_meme_stock_zero(): void
    {
        $admin = User::factory()->create();
        $p = Produit::create(['nom' => 'A', 'prix_particulier' => 500, 'prix_grossiste' => 400]);
        $service = new StockService;
        $service->enregistrerProduction([['produit_id' => $p->id, 'categorie_id' => null, 'quantite' => 10]], now()->toDateString(), null, $admin);
        $client = Client::create(['nom' => 'C', 'type' => 'particulier']);
        $service->enregistrerVente($client, [['produit_id' => $p->id, 'categorie_id' => null, 'quantite' => 10]], now()->toDateString(), $admin);

        $this->assertEquals(0, $p->fresh()->stock);

        Livewire::test(GestionProduits::class)
            ->call('supprimerProduit', $p->id)
            ->assertSee('Suppression impossible')
            ->assertSee("ligne(s) d'historique");

        $this->assertDatabaseHas('produits', ['id' => $p->id]);
    }

    public function test_supprimer_produit_vierge_ok(): void
    {
        $p = Produit::create(['nom' => 'A', 'prix_particulier' => 100, 'prix_grossiste' => 90]);

        Livewire::test(GestionProduits::class)
            ->call('supprimerProduit', $p->id)
            ->assertSee('Produit supprimé.');

        $this->assertDatabaseMissing('produits', ['id' => $p->id]);
    }

    public function test_modale_type_cree_et_selectionne(): void
    {
        $comp = Livewire::test(GestionProduits::class)
            ->call('ouvrirModalType')
            ->assertSet('modalType', true)
            ->set('type_nom', 'Eau')
            ->call('sauverType')
            ->assertSet('modalType', false);

        $type = Type::where('nom', 'Eau')->firstOrFail();
        $this->assertEquals($type->id, $comp->get('type_id'));
    }

    public function test_modale_categorie_creation_et_edition(): void
    {
        $p = Produit::create(['nom' => 'A']);

        // Création via modale
        Livewire::test(GestionProduits::class)
            ->call('ouvrirModalCategorie', $p->id)
            ->assertSet('modalCategorie', true)
            ->set('cat_nom', '1L')
            ->set('cat_pp', 500)
            ->set('cat_pg', 400)
            ->call('sauverCategorie')
            ->assertSet('modalCategorie', false)
            ->assertSee('Catégorie ajoutée.');

        $c = Categorie::where('produit_id', $p->id)->where('nom', '1L')->firstOrFail();

        // Édition pré-remplie via modale
        Livewire::test(GestionProduits::class)
            ->call('ouvrirModalCategorie', $p->id, $c->id)
            ->assertSet('modalCategorie', true)
            ->assertSet('cat_nom', '1L')
            ->assertSet('cat_pp', 500)
            ->set('cat_pp', 550)
            ->call('sauverCategorie')
            ->assertSee('Catégorie modifiée.');

        $this->assertEquals(550, (float) $c->fresh()->prix_particulier);
    }

    public function test_supprimer_categorie_bloquee_avec_stock(): void
    {
        $p = Produit::create(['nom' => 'A']);
        $c = Categorie::create(['produit_id' => $p->id, 'nom' => '1L', 'prix_particulier' => 500, 'prix_grossiste' => 400, 'stock' => 7]);

        Livewire::test(GestionProduits::class)
            ->call('supprimerCategorie', $c->id)
            ->assertSee('Suppression impossible');

        $this->assertDatabaseHas('categories', ['id' => $c->id]);
    }
}
