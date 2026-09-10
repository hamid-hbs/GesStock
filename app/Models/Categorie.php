<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Categorie extends Model
{
    protected $fillable = [
        'produit_id', 'nom',
        'prix_particulier', 'prix_grossiste',
        'stock', 'seuil_alerte',
    ];

    protected function casts(): array
    {
        return [
            'prix_particulier' => 'decimal:2',
            'prix_grossiste' => 'decimal:2',
        ];
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    /** Prix applicable selon le type de client. */
    public function prixPour(string $typeClient): float
    {
        return (float) ($typeClient === 'grossiste' ? $this->prix_grossiste : $this->prix_particulier);
    }

    public function libelleComplet(): string
    {
        return $this->produit->nom.' — '.$this->nom;
    }

    public function enAlerte(): bool
    {
        return $this->stock <= $this->seuil_alerte;
    }
}
