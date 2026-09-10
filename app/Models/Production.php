<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Production extends Model
{
    protected $fillable = ['reference', 'date_production', 'notes', 'user_id'];

    protected function casts(): array
    {
        return ['date_production' => 'date'];
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(ProductionLigne::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quantiteTotale(): int
    {
        return (int) $this->lignes()->sum('quantite');
    }
}
