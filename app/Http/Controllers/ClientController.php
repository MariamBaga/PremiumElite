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

    // Récupération équipe si chef d'équipe
    $teamId = null;
    if ($user->hasRole('chef_equipe')) {
        $teamId = \App\Models\Team::where('lead_id', $user->id)->value('id');
    }

    // Validation des filtres
    $data = $request->validate([
        'type' => 'nullable|in:residentiel,professionnel',
        'search' => 'nullable|string|max:200',
        'numero_ligne' => 'nullable|string|max:50',
        'numero_point_focal' => 'nullable|string|max:50',
        'localisation' => 'nullable|string|max:100',
        'date_paiement_from' => 'nullable|date',
        'date_paiement_to' => 'nullable|date',
        'date_affect_from' => 'nullable|date',
        'date_affect_to' => 'nullable|date',
        'sort' => 'nullable|in:created_at,nom,prenom,raison_sociale,numero_ligne,numero_point_focal,localisation,date_paiement,date_affectation',
        'dir' => 'nullable|in:asc,desc',
        'per_page' => 'nullable|integer|min:5|max:100',
    ]);

    $sort = $data['sort'] ?? 'created_at';
    $dir = $data['dir'] ?? 'desc';
    $per = $data['per_page'] ?? 10;

    $q = Client::with(['lastDossier', 'lastDossier.team'])
        ->when($user->hasRole('chef_equipe') && $teamId, function ($qry) use ($teamId) {
            // Filtrer uniquement les clients dont le dernier dossier est affecté à l'équipe du chef
            $qry->whereHas('lastDossier', function ($sub) use ($teamId) {
                $sub->where('assigned_team_id', $teamId);
            });
        })
        ->when($user->hasRole('chef_equipe') && !$teamId, function ($qry) {
            // Si chef sans équipe assignée → afficher rien
            $qry->whereRaw('0 = 1');
        })

        // Filtres simples
        ->when(!empty($data['type']), fn($qry) => $qry->where('type', $data['type']))
        ->when(!empty($data['numero_ligne']), fn($qry) => $qry->where('numero_ligne', 'like', '%' . $data['numero_ligne'] . '%'))
        ->when(!empty($data['numero_point_focal']), fn($qry) => $qry->where('numero_point_focal', 'like', '%' . $data['numero_point_focal'] . '%'))
        ->when(!empty($data['localisation']), fn($qry) => $qry->where('localisation', 'like', '%' . $data['localisation'] . '%'))

        // Filtres par date
        ->when(!empty($data['date_paiement_from']), fn($qry) => $qry->whereDate('date_paiement', '>=', $data['date_paiement_from']))
        ->when(!empty($data['date_paiement_to']), fn($qry) => $qry->whereDate('date_paiement', '<=', $data['date_paiement_to']))
        ->when(!empty($data['date_affect_from']), fn($qry) => $qry->whereDate('date_affectation', '>=', $data['date_affect_from']))
        ->when(!empty($data['date_affect_to']), fn($qry) => $qry->whereDate('date_affectation', '<=', $data['date_affect_to']))

        // Recherche globale
        ->when(!empty($data['search']), function ($qry) use ($data) {
            $s = '%' . $data['search'] . '%';
            $qry->where(function ($sub) use ($s) {
                $sub->where('nom', 'like', $s)
                    ->orWhere('prenom', 'like', $s)
                    ->orWhere('raison_sociale', 'like', $s)
                    ->orWhere('telephone', 'like', $s)
                    ->orWhere('email', 'like', $s)
                    ->orWhere('adresse_ligne1', 'like', $s)
                    ->orWhere('localisation', 'like', $s)
                    ->orWhere('numero_ligne', 'like', $s)
                    ->orWhere('numero_point_focal', 'like', $s);
            });
        })

        ->orderBy($sort, $dir);

    return view('clients.index', [
        'clients' => $q->paginate($per)->withQueryString(),
        'sort' => $sort,
        'dir' => $dir,
        'per_page' => $per,
    ]);
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
            $dossierData = $request->only(['type_service', 'pbo', 'pm', 'statut', 'description', 'assigned_to', 'assigned_team_id', 'date_planifiee', 'date_realisation', 'zone']);

            $dossier = DossierRaccordement::firstOrNew(['client_id' => $client->id]);
            $dossier->fill($dossierData);

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
}
