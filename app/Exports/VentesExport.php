<?php

namespace App\Exports;

use App\Models\Vente;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VentesExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return ['Référence', 'Date', 'Client', 'Type client', 'Membre apporteur', 'Total', 'Commission membre'];
    }

    public function collection(): Collection
    {
        return Vente::with(['client', 'membre'])->orderBy('id')->get()->map(fn ($v) => [
            $v->reference,
            $v->date_vente->format('d/m/Y'),
            $v->client->nom,
            $v->type_client,
            $v->membre->nom ?? '',
            $v->total,
            $v->commission_membre,
        ]);
    }
}
