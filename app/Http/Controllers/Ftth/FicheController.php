<?php

namespace App\Http\Controllers\Ftth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\DossierRaccordement;

class FicheController extends Controller
{
    /**
     * Page "Fiche unique" : onglets Fiche Client / Fiche Dossier.
     * Vue : resources/views/ftth/fiche.blade.php
     *
     * Accès :
     *  - /ftth/fiche?client=ID&tab=client
     *  - /ftth/fiche?dossier=ID&tab=dossier
     */
    public function __invoke(Request $request)
    {
        $client  = null;
        $dossier = null;

        if ($request->filled('dossier')) {
            $dossier = DossierRaccordement::with([
                'client',
                'statuts.user',
                'tentatives',
                'interventions.technicien',
                'team',
            ])->findOrFail($request->integer('dossier'));
            $client = $dossier->client;

        } elseif ($request->filled('client')) {
            $client = Client::findOrFail($request->integer('client'));

        } else {
            abort(404, 'Aucun client ou dossier spécifié.');
        }

        // La vue détecte $client / $dossier et gère l’onglet via ?tab=
        return view('ftth.fiche', compact('client','dossier'));
    }
}
