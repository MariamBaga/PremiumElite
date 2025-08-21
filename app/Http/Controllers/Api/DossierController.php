<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{DossierRaccordement};
use App\Http\Requests\{UpdateStatutRequest, StoreTentativeRequest, StoreInterventionRequest};
use Illuminate\Http\Request;

class DossierController extends Controller
{
    public function __construct(){ $this->middleware('auth:sanctum'); }

    // liste des dossiers assignés au technicien connecté
    public function my(Request $request)
    {
        $dossiers = DossierRaccordement::with('client')
            ->where('assigned_to', $request->user()->id)
            ->when($request->filled('statut'), fn($q)=>$q->where('statut', $request->statut))
            ->orderByDesc('id')
            ->paginate(50);
        return response()->json($dossiers);
    }

    public function show(DossierRaccordement $dossier)
    {
        $this->authorize('view', $dossier);
        return response()->json($dossier->load(['client','tentatives','interventions']));
    }

    public function updateStatus(UpdateStatutRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('updateStatus', $dossier);
        $dossier->update($request->validated());
        return response()->json(['ok'=>true,'statut'=>$dossier->statut]);
    }

    public function addTentative(StoreTentativeRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        $dossier->tentatives()->create($request->validated()+['user_id'=>$request->user()->id]);
        return response()->json(['ok'=>true]);
    }

    public function addIntervention(StoreInterventionRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        $dossier->interventions()->create($request->validated()+['technicien_id'=>$request->user()->id]);
        return response()->json(['ok'=>true]);
    }
}
