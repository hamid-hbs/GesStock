<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->string('nom'); // ex : 1L, 2L, 5L
            $table->string('sku')->unique();
            $table->decimal('prix_particulier', 12, 2);
            $table->decimal('prix_grossiste', 12, 2);
            $table->integer('stock')->default(0);
            $table->integer('seuil_alerte')->default(5);
            $table->timestamps();

            $table->unique(['produit_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
