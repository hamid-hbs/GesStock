<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vente extends Model
{
    protected $fillable = [
        'reference', 'client_id', 'membre_id', 'type_client',
        'total', 'commission_membre', 'date_vente', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_vente' => 'date',
            'total' => 'decimal:2',
            'commission_membre' => 'decimal:2',
        ];
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(VenteLigne::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class);
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
