<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionLigne extends Model
{
    protected $fillable = ['production_id', 'produit_id', 'categorie_id', 'quantite'];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function libelleArticle(): string
    {
        return $this->categorie ? $this->categorie->libelleComplet() : (string) $this->produit?->nom;
    }
}
