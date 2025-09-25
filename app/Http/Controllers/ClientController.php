<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use App\Models\DossierRaccordement;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class ClientController extends Controller
{
    // Pas de __construct middleware ici ; on protège via routes

    public function index(Request $request)
    {
        $user = auth()->user();

        // Chef d'équipe → restreindre à son équipe
        $teamId = null;
        if ($user->hasRole('chef_equipe')) {
            $teamId = \App\Models\Team::where('lead_id', $user->id)->value('id');
        }

        $data = $request->validate([
            // Filtres CLIENT (existants)
            'type' => 'nullable|in:residentiel,professionnel',
            'search' => 'nullable|string|max:200',
            'numero_ligne' => 'nullable|string|max:50',
            'numero_point_focal' => 'nullable|string|max:50',
            'localisation' => 'nullable|string|max:100',
            'date_paiement_from' => 'nullable|date',
            'date_paiement_to' => 'nullable|date',
            'date_affect_from' => 'nullable|date',
            'date_affect_to' => 'nullable|date',
            'statut' => 'nullable|string|in:' . implode(',', array_keys(\App\Enums\StatutDossier::labels())),

            // 🆕 Filtres Dossier (nouvelle structure)
            'service_acces' => 'nullable|in:FTTH,Cuivre',
            'categorie' => 'nullable|in:B2B,B2C',
            'active' => 'nullable|in:0,1',
            'localite' => 'nullable|string|max:100',
            'date_recep_from' => 'nullable|date',
            'date_recep_to' => 'nullable|date',
            'date_fin_from' => 'nullable|date',
            'date_fin_to' => 'nullable|date',
        ]);

        $q = Client::with(['lastDossier', 'lastDossier.team'])
            // Visibilité chef d’équipe
            ->when($user->hasRole('chef_equipe') && $teamId, fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->where('assigned_team_id', $teamId)))
            ->when($user->hasRole('chef_equipe') && !$teamId, fn($qry) => $qry->whereRaw('0=1'))

            // ---- Filtres CLIENT
            ->when(!empty($data['type']), fn($qry) => $qry->where('type', $data['type']))
            ->when(!empty($data['numero_ligne']), fn($qry) => $qry->where('numero_ligne', 'like', '%' . $data['numero_ligne'] . '%'))
            ->when(!empty($data['numero_point_focal']), fn($qry) => $qry->where('numero_point_focal', 'like', '%' . $data['numero_point_focal'] . '%'))
            ->when(!empty($data['localisation']), fn($qry) => $qry->where('localisation', 'like', '%' . $data['localisation'] . '%'))
            ->when(!empty($data['date_paiement_from']), fn($qry) => $qry->whereDate('date_paiement', '>=', $data['date_paiement_from']))
            ->when(!empty($data['date_paiement_to']), fn($qry) => $qry->whereDate('date_paiement', '<=', $data['date_paiement_to']))
            ->when(!empty($data['date_affect_from']), fn($qry) => $qry->whereDate('date_affectation', '>=', $data['date_affect_from']))
            ->when(!empty($data['date_affect_to']), fn($qry) => $qry->whereDate('date_affectation', '<=', $data['date_affect_to']))

            // ---- Filtres via le DERNIER DOSSIER (nouvelle structure + statut)
            ->when(!empty($data['statut']), fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->where('statut', $data['statut'])))
            ->when(!empty($data['service_acces']), fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->where('service_acces', $data['service_acces'])))
            ->when(!empty($data['categorie']), fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->where('categorie', $data['categorie'])))
            ->when($request->has('active'), fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->where('is_active', $request->input('active') === '1')))

            ->when(!empty($data['localite']), fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->where('localite', 'like', '%' . $data['localite'] . '%')))
            ->when(!empty($data['date_recep_from']), fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->whereDate('date_reception_raccordement', '>=', $data['date_recep_from'])))
            ->when(!empty($data['date_recep_to']), fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->whereDate('date_reception_raccordement', '<=', $data['date_recep_to'])))
            ->when(!empty($data['date_fin_from']), fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->whereDate('date_fin_travaux', '>=', $data['date_fin_from'])))
            ->when(!empty($data['date_fin_to']), fn($qry) => $qry->whereHas('lastDossier', fn($dq) => $dq->whereDate('date_fin_travaux', '<=', $data['date_fin_to'])));

        // 👉 Pas de paginate : on renvoie la collection entière et DataTables gère le tri/recherche côté front

// après
$clients = $q->paginate(10); // 10 par page

        return view('clients.index', compact('clients'));
    }


    public function data(Request $request)
    {
        $query = Client::with('lastDossier');

        return DataTables::of($query)
            ->addColumn('ligne', function($client){
                return $client->lastDossier?->ligne ?? $client->numero_ligne;
            })
            ->addColumn('actions', function($client){
                return view('clients.partials.actions', compact('client'))->render();
            })
            ->make(true);
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();

        // 1. Création du client
        $client = Client::create($data);

        // 2. Création directe du dossier FTTH associé
        $dossier = \App\Models\DossierRaccordement::create([
            'client_id' => $client->id,
            'reference' => 'DR-' . now()->year . '-' . str_pad(\App\Models\DossierRaccordement::count() + 1, 6, '0', STR_PAD_LEFT),
            'type_service' => $request->input('type_service', 'residentiel'),
            'pbo' => $request->input('pbo'),
            'pm' => $request->input('pm'),
            'statut' => $request->input('statut', 'en_appel'), // valeur par défaut si rien n’est envoyé
            'zone' => $request->input('zone'),
            'assigned_to' => $request->input('assigned_to'), // technicien si fourni
            'assigned_team_id' => $request->input('assigned_team_id'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('clients.show', $client)->with('success', "Abonner et dossier d'abonné créés avec succès.");
    }

    public function show(Client $client)
    {
        // Vérification d'accès

        // Charger le nombre de dossiers et les dossiers eux-mêmes
        $client->loadCount('dossiers');
        $client->load([
            'dossiers' => function ($q) {
                $q->orderBy('created_at', 'desc')->with(['statuts', 'tentatives', 'interventions', 'technicien', 'team', 'client']);
            },
        ]);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        // Charger le dossier existant s’il y en a un
        $dossier = DossierRaccordement::where('client_id', $client->id)->first();

        if (!$dossier->isModifiable()) {
            return back()->withErrors('Ce dossier est activé ou realisé et ne peut plus être modifié.');
        }

        // Charger les techniciens / équipes (si tu veux proposer dans le formulaire)
        $teams = \App\Models\Team::pluck('name', 'id');
        $users = \App\Models\User::role('technicien')->pluck('name', 'id');

        return view('clients.edit', compact('client', 'dossier', 'teams', 'users'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        DB::transaction(function () use ($request, $client) {
            // 1) Mise à jour des infos client
            $client->update($request->validated());

            // 2) Mise à jour ou création du dossier lié
            $dossierData = $request->only([
                'type_service',
                'pbo',
                'pm',
                'statut',
                'description',
                'assigned_to',
                'assigned_team_id',
                'date_planifiee',
                'date_realisation',
                'zone',

                // 🔹 Nouveaux champs
                'ligne',
                'contact',
                'service_acces',
                'localite',
                'categorie',
                'date_reception_raccordement',
                'date_fin_travaux',
                'port',
                'pbo_lineaire_utilise',
                'nb_poteaux_implantes',
                'nb_armements_poteaux',
                'taux_reporting_j1',
                'is_active',
                'observation',
                'pilote_raccordement',
            ]);

            $dossier = DossierRaccordement::firstOrNew(['client_id' => $client->id]);
            $dossier->fill($dossierData);

            if (!$dossier->isModifiable()) {
                return back()->withErrors('Ce dossier est activé ou realisé et ne peut plus être modifié.');
            }
            // Si c'est une création → générer une référence unique
            if (!$dossier->exists) {
                $dossier->reference = 'DR-' . date('Y') . '-' . str_pad(DossierRaccordement::max('id') + 1, 5, '0', STR_PAD_LEFT);
                $dossier->created_by = auth()->id();
            }

            $dossier->save();
        });

        return redirect()->route('clients.show', $client)->with('success', 'Abonner et dossier mis à jour avec succès.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Dossier d\'abonner supprimé.');
    }

    public function deleteAll()
    {
        DB::transaction(function () {
            // 1) si tu veux AUSSI vider les dossiers liés aux clients :
            DossierRaccordement::query()->delete(); // (ou truncate si pas de FK entrante)

            // 2) ensuite, vider les clients
            Client::query()->delete(); // <- PAS truncate
        });

        return redirect()->route('clients.index')->with('success', 'Tous les clients (et dossiers liés) ont été supprimés.');
    }

    public function active()
    {
        $clients = Client::whereHas('dossiers', function ($q) {
            $q->where('statut', 'active');
        })->paginate(10);

        return view('clients.dossiers.active', compact('clients'));
    }

    public function realise()
{
    $clients = Client::whereHas('dossiers', function ($q) {
        $q->where('statut', 'realise');
    })->paginate(10); // ✅ paginate au lieu de get()

    return view('clients.dossiers.realise', compact('clients'));
}


public function nouveauRdv()
{
    $clients = Client::whereHas('dossiers', function ($q) {
        $q->where('statut', 'nouveau_rendez_vous');
    })->paginate(10); // ✅ pagination 10 par page

    return view('clients.dossiers.nouveau_rdv', compact('clients'));
}

public function enAppel()
{
    $clients = Client::whereHas('dossiers', function ($q) {
        $q->where('statut', 'en_appel');
    })->paginate(10);

    return view('clients.dossiers.en_appel', compact('clients'));
}

public function injoignables()
{
    $clients = Client::whereHas('dossiers', function ($q) {
        $q->where('statut', 'injoignable');
    })->paginate(10);

    return view('clients.dossiers.injoignables', compact('clients'));
}


public function deleteMultiple(Request $request)
{
    $request->validate([
        'clients' => 'required|array',
        'clients.*' => 'exists:clients,id',
    ]);

    DB::transaction(function () use ($request) {
        $clientIds = $request->input('clients');

        // Supprimer les dossiers liés
        DossierRaccordement::whereIn('client_id', $clientIds)->delete();

        // Supprimer les clients
        Client::whereIn('id', $clientIds)->delete();
    });

    return redirect()->route('clients.index')->with('success', 'Clients sélectionnés et leurs dossiers supprimés avec succès.');
}

public function bulkDelete(Request $request)
{
    $clientIds = $request->input('clients', []);

    if (empty($clientIds)) {
        return redirect()->back()->with('error', 'Aucun client sélectionné.');
    }

    DB::transaction(function () use ($clientIds) {
        DossierRaccordement::whereIn('client_id', $clientIds)->delete();
        Client::whereIn('id', $clientIds)->delete();
    });

    return redirect()->route('clients.index')->with('success', 'Clients et dossiers supprimés avec succès.');
}

}
