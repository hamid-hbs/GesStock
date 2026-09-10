<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produit extends Model
{
    protected $fillable = [
        'type_id', 'nom', 'description',
        'prix_particulier', 'prix_grossiste',
        'stock', 'seuil_alerte', 'actif',
    ];

    protected function casts(): array
    {
        return [
            'prix_particulier' => 'decimal:2',
            'prix_grossiste' => 'decimal:2',
            'actif' => 'boolean',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Categorie::class);
    }

    public function aDesCategories(): bool
    {
        return $this->categories()->exists();
    }

    /** Prix applicable selon le type de client. */
    public function prixPour(string $typeClient): ?float
    {
        $prix = $typeClient === 'grossiste' ? $this->prix_grossiste : $this->prix_particulier;

        return $prix === null ? null : (float) $prix;
    }

    public function enAlerte(): bool
    {
        return ! $this->aDesCategories() && $this->stock <= $this->seuil_alerte;
    }
}
