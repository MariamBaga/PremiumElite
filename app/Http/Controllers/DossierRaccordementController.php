<?php

namespace App\Http\Controllers;

use App\Models\{DossierRaccordement, Client, TentativeContact, Intervention};
use App\Http\Requests\{StoreDossierRequest, UpdateDossierRequest, UpdateStatutRequest, StoreTentativeRequest, StoreInterventionRequest};
use App\Enums\StatutDossier;
use Illuminate\Http\Request;


class DossierRaccordementController extends Controller
{


    public function index(Request $request)
    {
        $this->authorize('viewAny', DossierRaccordement::class);

        $query = DossierRaccordement::with(['client','technicien'])
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('type_service'), fn($q)=>$q->where('type_service',$request->type_service))
            ->latest();

        return view('dossiers.index', [
            'dossiers' => $query->paginate(15),
            'statuts'  => StatutDossier::labels(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', DossierRaccordement::class);
        return view('dossiers.create', [
            'clients' => Client::orderBy('id','desc')->limit(100)->get()
        ]);
    }

    public function store(StoreDossierRequest $request)
    {
        $this->authorize('create', DossierRaccordement::class);
        $dossier = DossierRaccordement::create($request->validated());
        return redirect()->route('dossiers.show', $dossier)->with('success','Dossier créé');
    }

    public function show(DossierRaccordement $dossier)
    {
        $this->authorize('view', $dossier);
        $dossier->load(['client','technicien','tentatives.user','interventions.technicien','statuts.user']);
        return view('dossiers.show', compact('dossier'));
    }

    public function edit(DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        return view('dossiers.edit', [
            'dossier'=>$dossier->load('client'),
            'clients'=>Client::orderBy('id','desc')->limit(100)->get()
        ]);
    }

    public function update(UpdateDossierRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        $dossier->update($request->validated());
        return back()->with('success','Dossier mis à jour');
    }

    public function destroy(DossierRaccordement $dossier)
    {
        $this->authorize('delete', $dossier);
        $dossier->delete();
        return redirect()->route('dossiers.index')->with('success','Dossier supprimé');
    }

    public function assign(Request $request, DossierRaccordement $dossier)
    {
        $this->authorize('assign', DossierRaccordement::class);
        $request->validate(['assigned_to'=>'required|exists:users,id','date_planifiee'=>'nullable|date']);
        $dossier->update($request->only('assigned_to','date_planifiee'));
        return back()->with('success','Affectation mise à jour');
    }

    public function updateStatus(UpdateStatutRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('updateStatus', $dossier);
        $dossier->update($request->validated());
        return back()->with('success','Statut mis à jour');
    }

    public function storeTentative(StoreTentativeRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        $dossier->tentatives()->create($request->validated()+['user_id'=>auth()->id()]);
        return back()->with('success','Tentative enregistrée');
    }

    public function storeIntervention(StoreInterventionRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        $dossier->interventions()->create($request->validated()+['technicien_id'=>auth()->id()]);
        return back()->with('success','Intervention enregistrée');
    }
}
