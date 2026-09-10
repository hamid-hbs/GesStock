<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->date('date_production');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('production_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained('productions')->cascadeOnDelete();
            // Une ligne vise soit un produit seul, soit une catégorie (jamais les deux)
            $table->foreignId('produit_id')->nullable()->constrained('produits');
            $table->foreignId('categorie_id')->nullable()->constrained('categories');
            $table->integer('quantite');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_lignes');
        Schema::dropIfExists('productions');
    }
};
