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

        if ($user->hasRole('chef_equipe')) {
            $query->where('assigned_team_id', $user->team_id);
        }

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
        $data = $request->validated();
 // ← ajoute le créateur
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

        $data = $request->validate([
            'assigned_to'      => 'nullable|exists:users,id',
            'assigned_team_id' => 'nullable|exists:teams,id',
            'date_planifiee'   => 'nullable|date',
        ]);

        if (empty($data['assigned_to']) && empty($data['assigned_team_id'])) {
            return back()->withErrors(['assigned_to' => 'Affecter un technicien ou une équipe est requis.'])->withInput();
        }

        $dossier->update([
            'assigned_to'      => $data['assigned_to']      ?? $dossier->assigned_to,
            'assigned_team_id' => $data['assigned_team_id'] ?? $dossier->assigned_team_id,
            'date_planifiee'   => $data['date_planifiee']   ?? $dossier->date_planifiee,
        ]);

        return back()->with('success', 'Affectation mise à jour');
    }







    public function updateStatus(UpdateStatutRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('updateStatus', $dossier);

        $oldStatut = $dossier->statut;

        $dossier->update($request->validated());

        // Synchronisation corbeille uniquement si statut = active ou injoignable

    // 👉 Toujours garder le dossier dans la corbeille tant qu'il a une équipe
    if ($dossier->assigned_team_id) {
        TeamDossier::firstOrCreate(
            ['team_id' => $dossier->assigned_team_id, 'dossier_id' => $dossier->id],
            ['etat' => 'en_cours']
        );
    }



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



    public function saveRapport(Request $request, DossierRaccordement $dossier)
{
    $this->authorize('update', $dossier);

    $data = $request->validate([
      'etat'             => 'required|in:pon,contraintes,reporte,realise',
      'msan'             => 'nullable|string|max:50',
      'fat'              => 'nullable|string|max:50',
      'port'             => 'nullable|string|max:10',
      'port_disponible'  => 'nullable|string|max:10',
      'type_cable'       => 'nullable|string|max:30',
      'lineaire_m'       => 'nullable|integer|min:0',
      'puissance_fat_dbm'=> 'nullable|numeric',
      'puissance_pto_dbm'=> 'nullable|numeric',
      'rapport_installation' => 'nullable|array', // éléments divers (poteaux, accessoires, MAC, SN, etc.)
      'date_report'      => 'nullable|date',
      'contrainte'       => 'nullable|string|max:120',
    ]);

    // Mise à jour des champs clés
    $dossier->fill($data);

    // logique statut
    if ($data['etat']==='realise') {
      $dossier->statut = 'realise';
      $dossier->date_realisation = now();
    } elseif ($data['etat']==='reporte') {
      $dossier->statut = 'replanifie';
      $dossier->date_planifiee = $data['date_report'] ?? $dossier->date_planifiee;
    } elseif ($data['etat']==='contraintes') {
      $dossier->statut = 'pbo_sature'; // ou autre selon liste
    } else { // pon = en cours
      $dossier->statut = 'en_equipe';
    }

    $dossier->save();

    return back()->with('success','Rapport enregistré.');
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
