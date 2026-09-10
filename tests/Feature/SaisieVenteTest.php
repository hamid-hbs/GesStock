<?php

namespace Tests\Feature;

use App\Livewire\SaisieVente;
use App\Models\Client;
use App\Models\Membre;
use App\Models\Produit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SaisieVenteTest extends TestCase
{
    use RefreshDatabase;

    private function contexteVente(): array
    {
        $admin = User::factory()->create();
        $produit = Produit::create(['nom' => 'Bidon', 'prix_particulier' => 500, 'prix_grossiste' => 400]);
        (new StockService)->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 100]],
            now()->toDateString(), null, $admin
        );
        $membre = Membre::create(['nom' => 'Ali', 'telephone' => '770000001', 'taux_commission' => 10]);
        $client = Client::create([
            'nom' => 'Boutique X', 'type' => Client::TYPE_GROSSISTE,
            'telephone' => '771111111', 'adresse' => 'Dakar', 'membre_id' => $membre->id,
        ]);

        return [$admin, $produit, $membre, $client];
    }

    public function test_fiche_client_et_apporteur_affichees(): void
    {
        [$admin, $produit, $membre, $client] = $this->contexteVente();

        Livewire::actingAs($admin)->test(SaisieVente::class)
            ->set('client_id', $client->id)
            ->assertSee('Client :')
            ->assertSee('Boutique X')
            ->assertSee('grossiste')
            ->assertSee('771111111')
            ->assertSee('Dakar')
            ->assertSee('Apporteur :')
            ->assertSee('Ali')
            ->assertSee('10');
    }

    public function test_dernieres_ventes_accordeon_avec_pu_quantite_total_fcfa(): void
    {
        [$admin, $produit, $membre, $client] = $this->contexteVente();
        $vente = (new StockService)->enregistrerVente(
            $client,
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 5]],
            now()->toDateString(), $admin
        );

        $comp = Livewire::actingAs($admin)->test(SaisieVente::class);
        $comp->assertSee($vente->reference)
            ->assertSee('grossiste')
            ->assertSee('Ali')
            ->assertSee('2 000 FCFA') // 5 x 400
            ->assertSee('200 FCFA'); // commission 10%

        $comp->call('basculerVente', $vente->id)
            ->assertSee('Bidon')
            ->assertSee('400') // PU snapshot
            ->assertSee('5'); // quantité
    }

    public function test_vente_sans_membre_affiche_sans_apporteur(): void
    {
        [$admin, $produit, $membre, $client] = $this->contexteVente();
        $seul = Client::create(['nom' => 'M. Diallo', 'type' => Client::TYPE_PARTICULIER]);
        $vente = (new StockService)->enregistrerVente(
            $seul,
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 2]],
            now()->toDateString(), $admin
        );

        Livewire::actingAs($admin)->test(SaisieVente::class)
            ->assertSee('sans apporteur')
            ->call('basculerVente', $vente->id)
            ->assertSee('1 000 FCFA'); // 2 x 500
    }
}
