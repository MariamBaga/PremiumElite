<?php

namespace App\Http\Controllers;

use App\Models\{DossierRaccordement, Client, TentativeContact, Intervention};
use App\Http\Requests\{StoreDossierRequest, UpdateDossierRequest, UpdateStatutRequest, StoreTentativeRequest, StoreInterventionRequest};
use App\Enums\StatutDossier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\TeamDossier;


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
            'dossiers' => $query->paginate(10),
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

        // 1) Valider les champs (tech + équipe sont optionnels mais au moins un requis)
        $data = $request->validate([
            'assigned_to'      => 'nullable|exists:users,id',
            'assigned_team_id' => 'nullable|exists:teams,id',
            'date_planifiee'   => 'nullable|date',
        ]);

        if (empty($data['assigned_to']) && empty($data['assigned_team_id'])) {
            return back()->withErrors(['assigned_to' => 'Affecter un technicien ou une équipe est requis.'])->withInput();
        }

        // 2) Garder l'ancienne équipe pour détecter un changement
        $oldTeamId = $dossier->assigned_team_id ?? null;

        // 3) Mettre à jour le dossier
        $dossier->update([
            'assigned_to'      => $data['assigned_to']      ?? $dossier->assigned_to,
            'assigned_team_id' => $data['assigned_team_id'] ?? $dossier->assigned_team_id,
            'date_planifiee'   => $data['date_planifiee']   ?? $dossier->date_planifiee,
        ]);

        // 4) Synchroniser la “corbeille d’équipe” (team_dossiers)
        if (!empty($data['assigned_team_id'])) {
            // Si l’équipe a changé, on clôt l’ancienne entrée (pour l’hygiène)
            if ($oldTeamId && $oldTeamId != $data['assigned_team_id']) {
                TeamDossier::where('team_id', $oldTeamId)
                    ->where('dossier_id', $dossier->id)
                    ->whereIn('etat', ['en_cours','contrainte','reporte'])
                    ->update([
                        'etat'       => 'cloture',
                        'motif'      => 'Réaffecté vers une autre équipe',
                        'updated_by' => auth()->id(),
                    ]);
            }

            // Créer si absent (le dossier apparaît dans la corbeille de la nouvelle équipe)
            TeamDossier::firstOrCreate(
                ['team_id' => $dossier->assigned_team_id, 'dossier_id' => $dossier->id],
                ['etat' => 'en_cours']
            );
        }

        return back()->with('success', 'Affectation mise à jour');
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



public function assignTeam(Request $request, DossierRaccordement $dossier)
{
    $this->authorize('assign', DossierRaccordement::class); // ou middleware can:dossiers.assign

    $data = $request->validate([
        'assigned_team_id' => ['nullable', Rule::exists('teams','id')],
    ]);

    $dossier->update([
        'assigned_team_id' => $data['assigned_team_id'] ?? null,
    ]);

    return back()->with('success', $data['assigned_team_id']
        ? 'Équipe assignée au dossier.'
        : 'Équipe retirée du dossier.');
}
}
