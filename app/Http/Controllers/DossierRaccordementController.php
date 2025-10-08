<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\{DossierRaccordement, Client, TentativeContact, Intervention};
use App\Http\Requests\{StoreDossierRequest, UpdateDossierRequest, UpdateStatutRequest, StoreTentativeRequest, StoreInterventionRequest};
use App\Enums\StatutDossier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\TeamDossier;
// DossierRaccordementController.php
use App\Notifications\NouveauRdvNotification;
use App\Models\User;

class DossierRaccordementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $this->authorize('viewAny', DossierRaccordement::class);
        $status = request()->get('status');

        // On démarre la requête
        $query = DossierRaccordement::with(['client', 'technicien'])
            ->when(
                $user->hasRole('chef_equipe'),
                fn($q) => $q->where('assigned_team_id', $user->team_id), // filtre chef d’équipe
            )
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('type_service'), fn($q) => $q->where('type_service', $request->type_service))
            ->latest();

        return view('dossiers.index', [
            'dossiers' => $query->paginate(10),
            'statuts' => StatutDossier::labels(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', DossierRaccordement::class);
        return view('dossiers.create', [
            'clients' => Client::orderBy('id', 'desc')->limit(100)->get(),
        ]);
    }

    public function store(StoreDossierRequest $request)
    {
        $data = $request->validated();
        // ← ajoute le créateur
        $this->authorize('create', DossierRaccordement::class);
        $dossier = DossierRaccordement::create($request->validated());
        return redirect()->route('dossiers.show', $dossier)->with('success', 'Dossier créé');
    }

    public function show(DossierRaccordement $dossier)
    {
        $user = auth()->user(); // ← ajouter ceci
        if ($user->hasRole('chef_equipe') && $dossier->assigned_team_id !== $user->team_id) {
            abort(403, "Vous n'êtes pas autorisé à voir ce dossier.");
        }
        $this->authorize('view', $dossier);
        $dossier->load(['client', 'technicien', 'tentatives.user', 'interventions.technicien', 'statuts.user']);
        return view('dossiers.show', compact('dossier'));
    }

    public function edit(DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        if (!$dossier->isModifiable()) {
            return back()->withErrors('Ce dossier est activé ou realisé et ne peut plus être modifié.');
        }

        return view('dossiers.edit', [
            'dossier' => $dossier->load('client'),
            'clients' => Client::orderBy('id', 'desc')->limit(100)->get(),
        ]);
    }

    public function update(UpdateDossierRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        if (!$dossier->isModifiable()) {
            return back()->withErrors('Ce dossier est activé ou realisé et ne peut plus être modifié.');
        }

        $dossier->update($request->validated());
        return back()->with('success', 'Dossier mis à jour');
    }

    public function destroy(DossierRaccordement $dossier)
    {
        $this->authorize('delete', $dossier);
        if (!$dossier->isModifiable()) {
            return back()->withErrors('Ce dossier est activé ou realisé et ne peut pas être supprimé.');
        }
        $dossier->delete();
        return redirect()->route('dossiers.index')->with('success', 'Dossier supprimé');
    }

    public function assign(Request $request, DossierRaccordement $dossier)
    {
        $this->authorize('assign', DossierRaccordement::class);

        $data = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'assigned_team_id' => 'nullable|exists:teams,id',
            'date_planifiee' => 'nullable|date',
        ]);

        // 3) empêcher assignment d'une équipe si dossier EN_APPEL
        $statutValue = $dossier->statut instanceof \App\Enums\StatutDossier ? $dossier->statut->value : (string) ($dossier->statut ?? '');

        if ($statutValue === \App\Enums\StatutDossier::EN_APPEL->value && !empty($data['assigned_team_id'])) {
            return back()->withErrors(['assigned_team_id' => 'Un dossier en appel ne peut pas être assigné à une équipe.']);
        }
        if (!$dossier->isModifiable()) {
            return back()->withErrors('Impossible de modifier l’affectation : dossier activé ou réalisé.');
        }
        if (empty($data['assigned_to']) && empty($data['assigned_team_id'])) {
            return back()
                ->withErrors(['assigned_to' => 'Affecter une équipe est requis.'])
                ->withInput();
        }

        // ✅ On met à jour le dossier
        $dossier->update([
            'assigned_to' => $data['assigned_to'] ?? null,
            'assigned_team_id' => $data['assigned_team_id'] ?? null,
            'date_planifiee' => $data['date_planifiee'] ?? $dossier->date_planifiee,
        ]);

        // ✅ Synchroniser avec la corbeille d’équipe
        if (!empty($data['assigned_team_id'])) {
            TeamDossier::updateOrCreate(['team_id' => $data['assigned_team_id'], 'dossier_id' => $dossier->id], ['etat' => 'en_cours', 'updated_by' => auth()->id()]);
        } else {
            // Si on enlève l’équipe → on supprime l’entrée dans la corbeille
            TeamDossier::where('dossier_id', $dossier->id)->delete();
        }

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
            return back()->withErrors('Impossible de changer le statut : dossier activé ou realisé.');
        }

        $oldStatut = $dossier->statut;

        $dossier->update($request->validated());

        // Synchronisation corbeille uniquement si statut = active ou injoignable

        // 👉 Toujours garder le dossier dans la corbeille tant qu'il a une équipe
        if ($dossier->assigned_team_id) {
            TeamDossier::firstOrCreate(['team_id' => $dossier->assigned_team_id, 'dossier_id' => $dossier->id], ['etat' => 'en_cours']);
        }

        return back()->with('success', 'Statut mis à jour');
    }

    public function storeTentative(StoreTentativeRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        $dossier->tentatives()->create($request->validated() + ['user_id' => auth()->id()]);
        return back()->with('success', 'Tentative enregistrée');
    }

    public function storeIntervention(StoreInterventionRequest $request, DossierRaccordement $dossier)
    {
        $this->authorize('update', $dossier);
        $dossier->interventions()->create($request->validated() + ['technicien_id' => auth()->id()]);
        return back()->with('success', 'Intervention enregistrée');
    }

    public function storeRapport(Request $request)
{
    $request->validate([
        'dossier_id' => 'required|exists:dossiers_raccordement,id',
        // ✅ Utilisation de mimetypes (insensible à la casse)
        'rapport_file' => 'required|mimetypes:image/jpeg,image/png,image/gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain|max:5120',
        'rapport_intervention' => 'required|string',
        'port' => 'required|string|max:50',
        'lineaire_m' => 'required|integer|min:0',
        'type_cable' => 'required|string|max:100',
    ], [
        'rapport_file.mimetypes' => 'Le fichier doit être un PDF, Word, Texte ou une image (jpg, jpeg, png, gif).',
        'rapport_file.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
    ]);

    $dossier = DossierRaccordement::findOrFail($request->dossier_id);

    // ✅ Upload du fichier (compatible JPG, PNG, PDF, etc.)
    if ($request->hasFile('rapport_file')) {
        $file = $request->file('rapport_file');
        $extension = strtolower($file->getClientOriginalExtension()); // sécurisation
        $filename = 'rapport_' . $dossier->id . '_' . time() . '.' . $extension;
        $path = $file->storeAs('rapports', $filename, 'public');
        $dossier->rapport_satisfaction = $path;
    }

    // Informations supplémentaires
    $dossier->rapport_intervention = $request->rapport_intervention;
    $dossier->port = $request->port;
    $dossier->lineaire_m = $request->lineaire_m;
    $dossier->type_cable = $request->type_cable;

    // Mise à jour du statut
    $dossier->statut = 'active';
    $dossier->save();

    return redirect()->back()->with('success', 'Rapport enregistré et statut mis à jour.');
}

    public function storeNouveauRdv(Request $request)
    {
        $request->validate([
            'dossier_id' => 'required|exists:dossiers_raccordement,id',
            'date_rdv' => 'required|date',
            'commentaire_rdv' => 'nullable|string',
        ]);

        $dossier = DossierRaccordement::findOrFail($request->dossier_id);
        $this->authorize('updateStatus', $dossier);

        $dossier->update([
            'statut' => 'nouveau_rendez_vous',
            'date_planifiee' => $request->date_rdv,
            'description' => $request->commentaire_rdv,
        ]);

        // ✅ Notifier tous les rôles importants
        $rolesToNotify = ['admin', 'superadmin', 'coordinateur']; // ajouter d'autres si besoin
        $usersToNotify = User::role($rolesToNotify)->get();

        foreach ($usersToNotify as $user) {
            $user->notify(new NouveauRdvNotification($dossier));
        }

        return back()->with('success', 'Nouveau rendez-vous enregistré.');
    }

    // Injoignable (avec commentaire action prise)
    public function storeInjoignable(Request $request)
    {
        $request->validate([
            'dossier_id' => 'required|exists:dossiers_raccordement,id',
            'action_pris' => 'required|string|max:255',
            'capture_file' => 'nullable|image|mimes:jpeg,png,jpg,JPG,PNG,JPEG|max:5120',
        ]);

        $dossier = DossierRaccordement::findOrFail($request->dossier_id);

        // ✅ upload capture (optionnelle)
        $path = null;
        if ($request->hasFile('capture_file')) {
            $file = $request->file('capture_file');
            $filename = 'injoignable_' . $dossier->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('captures', $filename, 'public');
        }

        $dossier->update([
            'statut' => 'injoignable',
            'description' => $request->action_pris,
            'capture_message' => $path,
        ]);

        return back()->with('success', 'Dossier marqué comme injoignable (action précisée + capture).');
    }

    // PBO saturé (upload rapport)
    // DossierController.php

    public function storePboSature(Request $request)
    {
        try {
            Log::info('Début de storePboSature', ['request' => $request->all()]);

            $request->validate([
                'dossier_id' => 'required|exists:dossiers_raccordement,id',
                'rapport_intervention' => 'required|string|max:5000',
            ]);

            $dossier = DossierRaccordement::findOrFail($request->dossier_id);
            Log::info('Dossier trouvé', ['dossier_id' => $dossier->id]);

            $dossier->statut = \App\Enums\StatutDossier::PBO_SATURE;

            $dossier->rapport_satisfaction = $request->rapport;
            $dossier->save();

            Log::info('Dossier mis à jour avec succès', [
                'dossier_id' => $dossier->id,
                'statut' => $dossier->statut,
                'rapport_satisfaction' => $dossier->rapport_satisfaction,
            ]);

            return back()->with('success', 'Dossier mis en PBO saturé avec rapport saisi.');
        } catch (\Throwable $e) {
            Log::error('Erreur dans storePboSature', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()->with('error', 'Une erreur est survenue lors du traitement du dossier.');
        }
    }

    public function storeZoneDepourvue(Request $request)
    {
        try {
            Log::info('Début de storeZoneDepourvue', ['request' => $request->all()]);

            $request->validate([
                'dossier_id' => 'required|exists:dossiers_raccordement,id',
                'rapport_intervention' => 'required|string|max:5000',
            ]);

            $dossier = DossierRaccordement::findOrFail($request->dossier_id);
            Log::info('Dossier trouvé', ['dossier_id' => $dossier->id]);

            $dossier->statut = \App\Enums\StatutDossier::ZONE_DEPOURVUE;

            $dossier->rapport_satisfaction = $request->rapport;
            $dossier->save();

            Log::info('Dossier mis à jour avec succès', [
                'dossier_id' => $dossier->id,
                'statut' => $dossier->statut,
                'rapport_satisfaction' => $dossier->rapport_satisfaction,
            ]);

            return back()->with('success', 'Dossier marqué comme zone dépourvue avec rapport saisi.');
        } catch (\Throwable $e) {
            Log::error('Erreur dans storeZoneDepourvue', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()->with('error', 'Une erreur est survenue lors du traitement du dossier.');
        }
    }

    public function storeRealise(Request $request)
    {
        $request->validate([
            'dossier_id' => 'required|exists:dossiers_raccordement,id',
            // ✅ Validation insensible à la casse
            'rapport_file' => 'required|mimetypes:image/jpeg,image/png,image/gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain|max:5120',
            'rapport_intervention' => 'required|string',
            'raison_non_activation' => 'required|string',
        ], [
            'rapport_file.mimetypes' => 'Le fichier doit être un PDF, Word, Texte ou une image (jpg, jpeg, png, gif).',
            'rapport_file.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        $dossier = DossierRaccordement::findOrFail($request->dossier_id);

        // ✅ Upload compatible avec extensions majuscules
        if ($request->hasFile('rapport_file')) {
            $file = $request->file('rapport_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = 'rapport_realise_' . $dossier->id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('rapports', $filename, 'public');
            $dossier->rapport_satisfaction = $path;
        }

        // Texte intervention + raison non activation
        $dossier->rapport_intervention = $request->rapport_intervention;
        $dossier->raison_non_activation = $request->raison_non_activation;
        $dossier->statut = 'realise';
        $dossier->save();

        return back()->with('success', 'Dossier marqué comme réalisé avec rapport et raison de non activation.');
    }


    public function storeIndisponible(Request $request)
    {
        $request->validate([
            'dossier_id' => 'required|exists:dossiers_raccordement,id',
            'raison' => 'required|string|max:255',
            'capture_file' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $dossier = DossierRaccordement::findOrFail($request->dossier_id);

        // ✅ upload capture
        $path = null;
        if ($request->hasFile('capture_file')) {
            $file = $request->file('capture_file');
            $filename = 'indisponible_' . $dossier->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('captures', $filename, 'public');
        }

        $dossier->update([
            'statut' => 'indisponible',
            'description' => $request->raison,
            'capture_message' => $path, // 🔹 colonne à prévoir dans la table
        ]);

        return back()->with('success', 'Dossier marqué comme indisponible (raison + capture).');
    }

    public function storeDepassementLineaire(Request $request)
    {
        $request->validate([
            'dossier_id' => 'required|exists:dossiers_raccordement,id',
            'distance' => 'required|numeric|min:1',
            'gps_abonne' => 'required|string|max:255',
            'gps_pbo' => 'required|string|max:255',
            'nom_pbo' => 'required|string|max:255',
        ]);

        $dossier = DossierRaccordement::findOrFail($request->dossier_id);

        $dossier->update([
            'statut' => \App\Enums\StatutDossier::DEPASSEMENT_LINEAIRE->value,
            'depassement_distance' => $request->distance,
            'depassement_gps_abonne' => $request->gps_abonne,
            'depassement_gps_pbo' => $request->gps_pbo,
            'depassement_nom_pbo' => $request->nom_pbo,
            'description' => "Dépassement de {$request->distance}m - Abonné: {$request->gps_abonne}, PBO: {$request->gps_pbo}, Nom PBO: {$request->nom_pbo}",
        ]);

        return back()->with('success', 'Dossier marqué comme Dépassement Linéaire.');
    }

    public function storeImplantationPoteau(Request $request)
    {
        $request->validate([
            'dossier_id' => 'required|exists:dossiers_raccordement,id',
            'gps_abonne' => 'required|string|max:255',

            'gps_fat'    => 'required|string|max:255', // 🔹 nouveau champ obligatoire
            'date_rdv' => 'required|date|after_or_equal:today',
        ]);

        $dossier = DossierRaccordement::findOrFail($request->dossier_id);

        $dossier->update([
            'statut' => \App\Enums\StatutDossier::IMPLANTATION_POTEAU->value,
            'implantation_gps_abonne' => $request->gps_abonne,
            'date_planifiee' => $request->date_rdv, // ✅ on réutilise ton champ existant
            'implantation_gps_fat' => $request->gps_fat, // 🔹 nouveau champ
            'description' => "Implantation poteau - Abonné: {$request->gps_abonne}, FAT: {$request->gps_fat}, RDV prévu le {$request->date_rdv}",
        ]);

        return back()->with('success', 'Dossier marqué comme Implantation Poteau avec date de rendez-vous planifiée.');
    }

    // Activé (rapport + fiche client)
    public function deleteRapport(DossierRaccordement $dossier)
    {
        $dossier->delete(); // soft delete si tu as softDeletes
        return back()->with('success', 'Dossier supprimé.');
    }
    public function deleteAllRapports()
    {
        DossierRaccordement::whereNotNull('rapport_intervention')->orWhere('statut', 'nouveau_rendez_vous')->delete();
        return back()->with('success', 'Tous les dossiers avec rapport ou RDV ont été supprimés.');
    }

    // DossierRaccordementController.php
    // DossierRaccordementController.php
    public function listRapportsRdv()
    {
        $user = auth()->user();

        $query = DossierRaccordement::query();

        // Regrouper rapport ou RDV
        $query->where(function ($q) {
            $q->whereNotNull('rapport_intervention')->orWhere('statut', 'nouveau_rendez_vous');
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

        $dossiers = $query->orderBy('updated_at', 'desc')->paginate(10); // 10 par page

        return view('dossiers.showrapport', compact('dossiers'));
    }
    public function listRapportsSignes()
    {
        $user = auth()->user();

        $query = DossierRaccordement::query()->whereNotNull('rapport_satisfaction'); // ✅ uniquement les rapports signés

        // 🔒 Filtrage par rôle
        if ($user->hasRole('chef_equipe')) {
            $teamId = \App\Models\Team::where('lead_id', $user->id)->value('id');
            $query->where('assigned_team_id', $teamId);
        } elseif ($user->hasRole('client')) {
            $query->where('client_id', $user->client_id);
        }

        $dossiers = $query->with('client')->latest()->paginate(10);

        return view('dossiers.rapports_signes', compact('dossiers'));
    }

    public function assignTeam(Request $request, DossierRaccordement $dossier)
    {
        $this->authorize('assign', DossierRaccordement::class);

        $data = $request->validate([
            'assigned_team_id' => ['nullable', Rule::exists('teams', 'id')],
        ]);

        // 3) empêcher assignment d'une équipe si dossier EN_APPEL
        $statutValue = $dossier->statut instanceof \App\Enums\StatutDossier ? $dossier->statut->value : (string) ($dossier->statut ?? '');

        if ($statutValue === \App\Enums\StatutDossier::EN_APPEL->value && !empty($data['assigned_team_id'])) {
            return back()->withErrors(['assigned_team_id' => 'Un dossier en appel ne peut pas être assigné à une équipe.']);
        }
        if (!$dossier->isModifiable()) {
            return back()->withErrors('Impossible de modifier l’équipe : dossier activé.');
        }
        $dossier->update([
            'assigned_team_id' => $data['assigned_team_id'] ?? null,
        ]);

        // ✅ Synchroniser avec la corbeille de l’équipe
        if (!empty($data['assigned_team_id'])) {
            TeamDossier::updateOrCreate(['team_id' => $data['assigned_team_id'], 'dossier_id' => $dossier->id], ['etat' => 'en_cours', 'updated_by' => auth()->id()]);
        } else {
            // Si on retire l’équipe, on peut supprimer l’entrée
            TeamDossier::where('dossier_id', $dossier->id)->delete();
        }

        return back()->with('success', $data['assigned_team_id'] ? 'Équipe assignée au dossier.' : 'Équipe retirée du dossier.');
    }

    // Retourne les dossiers avec rendez-vous pour aujourd'hui ou demain
}
