<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    public const TYPE_PRODUCTION = 'production';
    public const TYPE_VENTE = 'vente';
    public const TYPE_AJUSTEMENT = 'ajustement';

    protected $table = 'mouvements_stock';

    protected $fillable = [
        'produit_id', 'categorie_id', 'type', 'quantite',
        'stock_avant', 'stock_apres', 'reference_doc', 'user_id',
    ];

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function libelleArticle(): string
    {
        return $this->categorie ? $this->categorie->libelleComplet() : (string) $this->produit?->nom;
    }
}
