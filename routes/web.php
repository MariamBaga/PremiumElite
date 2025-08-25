<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



use App\Http\Controllers\DossierRaccordementController;

Route::middleware(['auth'])->group(function(){

    Route::get('dossiers', [DossierRaccordementController::class,'index'])->name('dossiers.index');
    Route::get('dossiers/create', [DossierRaccordementController::class,'create'])->name('dossiers.create');
    Route::post('dossiers', [DossierRaccordementController::class,'store'])->name('dossiers.store');

    Route::get('dossiers/{dossier}', [DossierRaccordementController::class,'show'])->name('dossiers.show');
    Route::get('dossiers/{dossier}/edit', [DossierRaccordementController::class,'edit'])->name('dossiers.edit');
    Route::put('dossiers/{dossier}', [DossierRaccordementController::class,'update'])->name('dossiers.update');
    Route::delete('dossiers/{dossier}', [DossierRaccordementController::class,'destroy'])->name('dossiers.destroy');

    Route::post('dossiers/{dossier}/assign', [DossierRaccordementController::class,'assign'])->name('dossiers.assign');
    Route::post('dossiers/{dossier}/status', [DossierRaccordementController::class,'updateStatus'])->name('dossiers.status');

    Route::post('dossiers/{dossier}/tentatives', [DossierRaccordementController::class,'storeTentative'])->name('dossiers.tentatives.store');
    Route::post('dossiers/{dossier}/interventions', [DossierRaccordementController::class,'storeIntervention'])->name('dossiers.interventions.store');

});






use App\Http\Controllers\ClientController;

Route::middleware(['auth'])->group(function () {

    // Clients CRUD
    Route::get('clients',            [ClientController::class,'index'])->name('clients.index');
    Route::get('clients/create',     [ClientController::class,'create'])->name('clients.create');
    Route::post('clients',           [ClientController::class,'store'])->name('clients.store');
    Route::get('clients/{client}',   [ClientController::class,'show'])->name('clients.show');
    Route::get('clients/{client}/edit', [ClientController::class,'edit'])->name('clients.edit');
    Route::put('clients/{client}',   [ClientController::class,'update'])->name('clients.update');
    Route::delete('clients/{client}',[ClientController::class,'destroy'])->name('clients.destroy');

});


use App\Http\Controllers\ClientImportController;

Route::post('/clients/import', [ClientImportController::class, 'store'])
    ->name('clients.import')
    ->middleware('auth'); // si besoin



Route::get('/', function () {
    return view('welcome');
});

// routes/web.php
use App\Http\Controllers\DashboardController;

// Route::middleware(['auth'])->get('/dashboard', [DashboardController::class,'index'])->middleware(['auth', 'verified'])
//     ->name('dashboard');

                Route::get('/dashboard', function () {
                    //return view('dashboard');
                    return redirect()->route('dossiers.index');
                })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::delete('/clients/delete-all', [App\Http\Controllers\ClientController::class, 'deleteAll'])
    ->name('clients.deleteAll');


require __DIR__.'/auth.php';
