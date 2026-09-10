<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\Vente;
use App\Exports\StockExport;
use App\Exports\VentesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function bonVente(Vente $vente)
    {
        $vente->load(['client.membre', 'lignes.produit', 'lignes.categorie.produit']);

        return Pdf::loadView('pdf.bon-vente', ['vente' => $vente])
            ->download($vente->reference.'.pdf');
    }

    public function bonProduction(Production $production)
    {
        $production->load(['lignes.produit', 'lignes.categorie.produit']);

        return Pdf::loadView('pdf.bon-production', ['production' => $production])
            ->download($production->reference.'.pdf');
    }

    public function stockExcel(): BinaryFileResponse
    {
        return Excel::download(new StockExport, 'stock-lelabel.xlsx');
    }

    public function ventesExcel(): BinaryFileResponse
    {
        return Excel::download(new VentesExport, 'ventes-lelabel.xlsx');
    }
}
