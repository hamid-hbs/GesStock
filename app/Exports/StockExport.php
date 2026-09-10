<?php

namespace App\Exports;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return ['Article', 'Prix particulier', 'Prix grossiste', 'Stock', 'Seuil', 'Alerte'];
    }

    public function collection(): Collection
    {
        $rows = collect();

        foreach (Produit::whereDoesntHave('categories')->orderBy('nom')->get() as $p) {
            $rows->push([
                $p->nom, $p->prix_particulier, $p->prix_grossiste,
                $p->stock, $p->seuil_alerte, $p->stock <= $p->seuil_alerte ? 'OUI' : 'non',
            ]);
        }

        foreach (Categorie::with('produit')->orderBy('id')->get() as $c) {
            $rows->push([
                $c->libelleComplet(), $c->prix_particulier, $c->prix_grossiste,
                $c->stock, $c->seuil_alerte, $c->stock <= $c->seuil_alerte ? 'OUI' : 'non',
            ]);
        }

        return $rows;
    }
}
