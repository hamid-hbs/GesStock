<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    public const TYPE_PARTICULIER = 'particulier';
    public const TYPE_GROSSISTE = 'grossiste';

    protected $fillable = ['membre_id', 'type', 'nom', 'telephone', 'adresse'];

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class);
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class);
    }

    public function estGrossiste(): bool
    {
        return $this->type === self::TYPE_GROSSISTE;
    }
}
