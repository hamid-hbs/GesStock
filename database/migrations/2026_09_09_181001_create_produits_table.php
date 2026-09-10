<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->nullable()->constrained('types')->nullOnDelete();
            $table->string('nom');
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            // PU utilisés uniquement quand le produit n'a pas de catégories
            $table->decimal('prix_particulier', 12, 2)->nullable();
            $table->decimal('prix_grossiste', 12, 2)->nullable();
            // Stock utilisé uniquement quand le produit n'a pas de catégories
            $table->integer('stock')->default(0);
            $table->integer('seuil_alerte')->default(5);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
