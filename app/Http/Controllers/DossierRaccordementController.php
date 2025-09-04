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
        $user = auth()->user();

        $this->authorize('viewAny', DossierRaccordement::class);

        // On démarre la requête
        $query = DossierRaccordement::with(['client','technicien'])
            ->when($user->hasRole('chef_equipe'), fn($q) =>
                $q->where('assigned_team_id', $user->team_id) // filtre chef d’équipe
            )
            ->when($request->filled('statut'), fn($q) =>
                $q->where('statut', $request->statut)
            )
            ->when($request->filled('type_service'), fn($q) =>
                $q->where('type_service', $request->type_service)
            )
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

        $user = auth()->user(); // ← ajouter ceci
        if ($user->hasRole('chef_equipe') && $dossier->assigned_team_id !== $user->team_id) {
            abort(403, "Vous n'êtes pas autorisé à voir ce dossier.");
        }
        $this->authorize('view', $dossier);
        $dossier->load(['client','technicien','tentatives.user','interventions.technicien','statuts.user']);
        return view('dossiers.show', compact('dossier'));
    }

    public function edit(DossierRaccordement $dossier)
    {
$this->authorize('update', $dossier);
        if (!$dossier->isModifiable()) {
            return back()->withErrors('Ce dossier est activé et ne peut plus être modifié.');
        }


        return view('dossiers.edit', [
            'dossier'=>$dossier->load('client'),
            'clients'=>Client::orderBy('id','desc')->limit(100)->get()
        ]);
    }

    public function update(UpdateDossierRequest $request, DossierRaccordement $dossier)
    {
$this->authorize('update', $dossier);
        if (!$dossier->isModifiable()) {
            return back()->withErrors('Ce dossier est activé et ne peut plus être modifié.');
        }

        $dossier->update($request->validated());
        return back()->with('success','Dossier mis à jour');
    }

    public function destroy(DossierRaccordement $dossier)
    {
        $this->authorize('delete', $dossier);
        if (!$dossier->isModifiable()) {
            return back()->withErrors('Ce dossier est activé et ne peut pas être supprimé.');
        }
        $dossier->delete();
        return redirect()->route('dossiers.index')->with('success','Dossier supprimé');
    }




    public function assign(Request $request, DossierRaccordement $dossier)
    {
        $this->authorize('assign', DossierRaccordement::class);

        if (!$dossier->isModifiable()) {
            return back()->withErrors('Impossible de modifier l’affectation : dossier activé.');
        }

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




    public function cloturer(DossierRaccordement $dossier)
    {
        $dossier->statut = 'cloture'; // ou StatutDossier::Cloture si tu utilises enum
        $dossier->save();

        return redirect()->back()->with('success', 'Dossier clôturé avec succès.');
    }



    public function notifierContrainte(DossierRaccordement $dossier, Request $request)
{
    // Exemple : ajouter une contrainte au dossier
    $request->validate([
        'contrainte' => 'required|string|max:255',
    ]);

    $dossier->contrainte = $request->contrainte;
    $dossier->save();

    return redirect()->back()->with('success', 'Contrainte notifiée avec succès.');
}



    public function updateStatus(UpdateStatutRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('updateStatus', $dossier);

        if (!$dossier->isModifiable()) {
            return back()->withErrors('Impossible de changer le statut : dossier activé.');
        }

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


    public function storeRapport(Request $request)
    {
        $request->validate([
            'dossier_id' => 'required|exists:dossiers_raccordement,id',
            'rapport_file' => 'required|mimes:pdf,doc,docx,txt|max:5120',
            'rapport_intervention' => 'required|string',
        ], [
            'rapport_file.mimes' => 'Le fichier doit être un PDF, Word ou Texte.',
            'rapport_file.max'   => 'Le fichier ne doit pas dépasser 5 Mo.',

        ]);

        $dossier = DossierRaccordement::findOrFail($request->dossier_id);

        // Upload fichier PDF
        if ($request->hasFile('rapport_file')) {
            $file = $request->file('rapport_file');
            $filename = 'rapport_'.$dossier->id.'_'.time().'.pdf';
            $path = $file->storeAs('rapports', $filename, 'public');
            $dossier->rapport_satisfaction = $path;
        }

        // Sauvegarde du texte
        $dossier->rapport_intervention = $request->rapport_intervention;

        // Mettre le statut à "active"
        $dossier->statut = 'active';

        $dossier->save();


        return redirect()->back()->with('success', 'Rapport enregistré et statut mis à jour.');
    }




// DossierRaccordementController.php
public function storeNouveauRdv(Request $request)
{
    $request->validate([
        'dossier_id' => 'required|exists:dossiers_raccordement,id',
        'date_rdv' => 'required|date',
        'commentaire_rdv' => 'nullable|string',
    ]);

    $dossier = DossierRaccordement::findOrFail($request->dossier_id);
    $this->authorize('updateStatus', $dossier);

    // Mettre à jour le statut et la nouvelle date
    $dossier->update([
        'statut' => 'nouveau_rendez_vous',
        'date_planifiee' => $request->date_rdv,
        'description' => $request->commentaire_rdv,
    ]);



    return back()->with('success', 'Nouveau rendez-vous enregistré.');
}

// DossierRaccordementController.php
// DossierRaccordementController.php
public function listRapportsRdv()
{
    $user = auth()->user();

    $query = DossierRaccordement::query();

    // Regrouper rapport ou RDV
    $query->where(function($q) {
        $q->whereNotNull('rapport_intervention')
          ->orWhere('statut', 'nouveau_rendez_vous');
    });

    // Filtrer selon le rôle
    if ($user->hasRole('chef_equipe')) {
        // seulement les dossiers de son équipe
        $teamId = \App\Models\Team::where('lead_id', $user->id)->value('id');
        $query->where('assigned_team_id', $teamId);
    } elseif ($user->hasRole('client')) {
        // seulement les dossiers du client
        $query->where('client_id', $user->client_id);
    }
    // Les autres rôles (admin, superviseur, coordinateur, superadmin) voient tout

    $dossiers = $query->orderBy('updated_at','desc')->get();

    return view('dossiers.showrapport', compact('dossiers'));
}




public function assignTeam(Request $request, DossierRaccordement $dossier)
{
    $this->authorize('assign', DossierRaccordement::class); // ou middleware can:dossiers.assign

    if (!$dossier->isModifiable()) {
        return back()->withErrors('Impossible de modifier l’équipe : dossier activé.');
    }
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
