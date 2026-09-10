<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('clients');
            // Snapshots au moment de la vente (historique intact si membre/taux change)
            $table->foreignId('membre_id')->nullable()->constrained('membres')->nullOnDelete();
            $table->string('type_client'); // particulier|grossiste
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('commission_membre', 12, 2)->default(0);
            $table->date('date_vente');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('vente_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained('ventes')->cascadeOnDelete();
            // Une ligne vise soit un produit seul, soit une catégorie (jamais les deux)
            $table->foreignId('produit_id')->nullable()->constrained('produits');
            $table->foreignId('categorie_id')->nullable()->constrained('categories');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 2); // snapshot selon type client
            $table->decimal('sous_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vente_lignes');
        Schema::dropIfExists('ventes');
    }
};
