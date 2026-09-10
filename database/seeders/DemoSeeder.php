<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Client;
use App\Models\Membre;
use App\Models\Produit;
use App\Models\Type;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@lelabel.local')->firstOrFail();
        $service = new StockService;

        $eau = Type::firstOrCreate(['nom' => 'Eau']);

        $alcaline = Produit::firstOrCreate(['nom' => 'Eau alcaline'], [
            'type_id' => $eau->id,
        ]);
        $unL = Categorie::firstOrCreate(['produit_id' => $alcaline->id, 'nom' => '1 litre'], [
            'prix_particulier' => 500, 'prix_grossiste' => 400,
        ]);
        $deuxL = Categorie::firstOrCreate(['produit_id' => $alcaline->id, 'nom' => '2 litres'], [
            'prix_particulier' => 900, 'prix_grossiste' => 750,
        ]);

        $table = Produit::firstOrCreate(['nom' => 'Eau de table'], [
            'type_id' => $eau->id,
        ]);
        $cinqL = Categorie::firstOrCreate(['produit_id' => $table->id, 'nom' => '5 litres'], [
            'prix_particulier' => 1500, 'prix_grossiste' => 1200,
        ]);

        $ali = Membre::firstOrCreate(['nom' => 'Ali'], [
            'telephone' => '770000001', 'taux_commission' => 10,
        ]);

        $boutique = Client::firstOrCreate(['nom' => 'Boutique Centrale'], [
            'type' => Client::TYPE_GROSSISTE, 'membre_id' => $ali->id,
        ]);
        $particulier = Client::firstOrCreate(['nom' => 'M. Diallo'], [
            'type' => Client::TYPE_PARTICULIER,
        ]);

        $service->enregistrerProduction([
            ['produit_id' => null, 'categorie_id' => $unL->id, 'quantite' => 200],
            ['produit_id' => null, 'categorie_id' => $deuxL->id, 'quantite' => 100],
            ['produit_id' => null, 'categorie_id' => $cinqL->id, 'quantite' => 50],
        ], now()->toDateString(), 'Production de démonstration', $admin);

        $service->enregistrerVente($boutique, [
            ['produit_id' => null, 'categorie_id' => $unL->id, 'quantite' => 20],
        ], now()->toDateString(), $admin);

        $service->enregistrerVente($particulier, [
            ['produit_id' => null, 'categorie_id' => $deuxL->id, 'quantite' => 3],
        ], now()->toDateString(), $admin);
    }
}
