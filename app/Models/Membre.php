<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membre extends Model
{
    protected $fillable = ['nom', 'telephone', 'adresse', 'taux_commission', 'actif'];

    protected function casts(): array
    {
        return [
            'taux_commission' => 'decimal:2',
            'actif' => 'boolean',
        ];
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class);
    }
}
