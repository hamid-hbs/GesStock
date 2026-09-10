<?php

namespace Tests\Feature;

use App\Livewire\GestionMembres;
use App\Models\Client;
use App\Models\Membre;
use App\Models\Produit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionMembresTest extends TestCase
{
    use RefreshDatabase;

    public function test_sous_section_clients_avec_infos_et_totaux(): void
    {
        $admin = User::factory()->create();
        $membre = Membre::create(['nom' => 'Ali', 'taux_commission' => 10]);
        $gros = Client::create([
            'nom' => 'Boutique X', 'type' => Client::TYPE_GROSSISTE,
            'telephone' => '771111111', 'adresse' => 'Dakar', 'membre_id' => $membre->id,
        ]);
        $part = Client::create([
            'nom' => 'M. Diallo', 'type' => Client::TYPE_PARTICULIER,
            'telephone' => '772222222', 'membre_id' => $membre->id,
        ]);

        $produit = Produit::create(['nom' => 'Bidon', 'prix_particulier' => 500, 'prix_grossiste' => 400]);
        $service = new StockService;
        $service->enregistrerProduction(
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 50]],
            now()->toDateString(), null, $admin
        );
        $service->enregistrerVente(
            $gros,
            [['produit_id' => $produit->id, 'categorie_id' => null, 'quantite' => 5]],
            now()->toDateString(), $admin
        ); // 5 x 400 = 2000

        Livewire::test(GestionMembres::class)
            ->assertSee('2 client(s)')
            ->assertDontSee('771111111') // replié : pas de détail
            ->call('basculerMembre', $membre->id)
            ->assertSee('Clients amenés par Ali')
            ->assertSee('Boutique X')
            ->assertSee('grossiste')
            ->assertSee('771111111')
            ->assertSee('Dakar')
            ->assertSee('2 000') // total acheté
            ->assertSee('M. Diallo')
            ->assertSee('particulier');
    }

    public function test_membre_sans_client_affiche_message(): void
    {
        $membre = Membre::create(['nom' => 'Fatou', 'taux_commission' => 5]);

        Livewire::test(GestionMembres::class)
            ->call('basculerMembre', $membre->id)
            ->assertSee('Aucun client amené');
    }
}
