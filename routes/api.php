<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DossierController;

Route::middleware('auth:sanctum')->prefix('v1')->group(function(){
    Route::get('dossiers/my', [DossierController::class,'my']);
    Route::get('dossiers/{dossier}', [DossierController::class,'show']);
    Route::post('dossiers/{dossier}/status', [DossierController::class,'updateStatus']);
    Route::post('dossiers/{dossier}/tentatives', [DossierController::class,'addTentative']);
    Route::post('dossiers/{dossier}/interventions', [DossierController::class,'addIntervention']);
});
