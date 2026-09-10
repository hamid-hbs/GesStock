<?php

use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login'); 
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::view('/types', 'types')->name('types.index');
    Route::view('/produits', 'produits')->name('produits.index');
    Route::view('/membres', 'membres')->name('membres.index');
    Route::view('/clients', 'clients')->name('clients.index');
    Route::view('/productions', 'productions')->name('productions.index');
    Route::view('/ventes', 'ventes')->name('ventes.index');
    Route::view('/mouvements', 'mouvements')->name('mouvements.index');

    Route::get('/ventes/{vente}/pdf', [ExportController::class, 'bonVente'])->name('ventes.pdf');
    Route::get('/productions/{production}/pdf', [ExportController::class, 'bonProduction'])->name('productions.pdf');
    Route::get('/exports/stock.xlsx', [ExportController::class, 'stockExcel'])->name('exports.stock');
    Route::get('/exports/ventes.xlsx', [ExportController::class, 'ventesExcel'])->name('exports.ventes');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
