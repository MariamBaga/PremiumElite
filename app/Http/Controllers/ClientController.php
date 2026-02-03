<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use App\Models\DossierRaccordement;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\Request;
use App\Enums\StatutDossier; // ✅ ajoute ceci
use Illuminate\Support\Str;
use App\Models\Team; // <-- ajouter en haut du contrôleur

class ClientController extends Controller
{
    // Pas de __construct middleware ici ; on protège via routes

  public function index(Request $request)
{
    set_time_limit(300);
    ini_set('memory_limit', '1G');

    $user = auth()->user();
    $teamIds = [];

    if ($user->hasRole('chef_equipe')) {
        $teamIds = Team::where('lead_id', $user->id)->pluck('id')->toArray();
    }

    // Validation simplifiée
    $data = $request->validate([
        'search' => 'nullable|string|max:200',
        'type' => 'nullable|in:residentiel,professionnel',
        'statut' => 'nullable|string',
        'service_acces' => 'nullable|string',
        'categorie' => 'nullable|string',
        'localite' => 'nullable|string',
        'date_fin_from' => 'nullable|date',
        'date_fin_to' => 'nullable|date',
    ]);

    // ⚡ CONSTRUIRE LA REQUÊTE AVEC JOINTURE (BEAUCOUP PLUS RAPIDE)
    $query = Client::query()
        ->select([
            'clients.*',
            'dossiers_raccordement.id as dossier_id',
            'dossiers_raccordement.statut',
            'dossiers_raccordement.date_fin_travaux',
            'dossiers_raccordement.service_acces',
            'dossiers_raccordement.categorie',
            'dossiers_raccordement.localite',
            'dossiers_raccordement.assigned_team_id',
            'dossiers_raccordement.ligne',
            'dossiers_raccordement.contact',
            'dossiers_raccordement.port',
        ])
        // ⚡ Jointure au lieu de whereHas (beaucoup plus rapide)
        ->leftJoin('dossiers_raccordement', function($join) {
            $join->on('clients.id', '=', 'dossiers_raccordement.client_id')
                 ->whereRaw('dossiers_raccordement.id = (
                     SELECT id FROM dossiers_raccordement
                     WHERE client_id = clients.id
                     ORDER BY created_at DESC
                     LIMIT 1
                 )');
        })
        ->leftJoin('teams', 'dossiers_raccordement.assigned_team_id', '=', 'teams.id');

    // ⚡ FILTRES SIMPLES SUR CLIENTS
    if (!empty($data['search'])) {
        $search = $data['search'];
        $query->where(function($q) use ($search) {
            $q->where('clients.nom', 'LIKE', "%{$search}%")
              ->orWhere('clients.prenom', 'LIKE', "%{$search}%")
              ->orWhere('clients.telephone', 'LIKE', "%{$search}%")
              ->orWhere('clients.numero_ligne', 'LIKE', "%{$search}%");
        });
    }

    if (!empty($data['type'])) {
        $query->where('clients.type', $data['type']);
    }

    // ⚡ FILTRES SUR LE DOSSIER (avec la jointure, c'est plus rapide)
    if (!empty($data['statut'])) {
        $query->where('dossiers_raccordement.statut', $data['statut']);
    }

    if (!empty($data['service_acces'])) {
        $query->where('dossiers_raccordement.service_acces', $data['service_acces']);
    }

    if (!empty($data['categorie'])) {
        $query->where('dossiers_raccordement.categorie', $data['categorie']);
    }

    if (!empty($data['localite'])) {
        $query->where('dossiers_raccordement.localite', 'LIKE', "%{$data['localite']}%");
    }

    // ⚡ FILTRE DATE_FIN_TRAVAUX optimisé
    if (!empty($data['date_fin_from'])) {
        $query->whereDate('dossiers_raccordement.date_fin_travaux', '>=', $data['date_fin_from']);
    }

    if (!empty($data['date_fin_to'])) {
        $query->whereDate('dossiers_raccordement.date_fin_travaux', '<=', $data['date_fin_to']);
    }

    // ⚡ FILTRE CHEF D'ÉQUIPE optimisé
    if ($user->hasRole('chef_equipe') && !empty($teamIds)) {
        $query->whereIn('dossiers_raccordement.assigned_team_id', $teamIds);
    }

    // ⚡ ORDRE ET PAGINATION
    $query->orderBy('clients.created_at', 'desc');

    // ⚡ PAGINATION LÉGÈRE (10 résultats)
    $clients = $query->paginate(10);

    return view('clients.index', compact('clients'));
}

    public function data(Request $request)
    {
        $query = Client::with('lastDossier');

        return DataTables::of($query)
            ->addColumn('ligne', function ($client) {
                return $client->lastDossier?->ligne ?? $client->numero_ligne;
            })
            ->addColumn('actions', function ($client) {
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

    public function active(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', function ($q) {
            $q->where('statut', 'active');
        })
            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.active', compact('clients'));
    }

    public function realise(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }
        $clients = Client::whereHas('dossiers', function ($q) {
            $q->where('statut', 'realise');
        })

            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10); // ✅ paginate au lieu de get()

        return view('clients.dossiers.realise', compact('clients'));
    }

    public function nouveauRdv(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', function ($q) {
            $q->where('statut', 'nouveau_rendez_vous');
        })
            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10); // ✅ pagination 10 par page

        return view('clients.dossiers.nouveau_rdv', compact('clients'));
    }

    public function enAppel(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', function ($q) {
            $q->where('statut', 'en_appel');
        })
            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.en_appel', compact('clients'));
    }

    public function injoignables(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', function ($q) {
            $q->where('statut', 'injoignable');
        })
            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.injoignables', compact('clients'));
    }

    public function indisponible(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', fn($q) => $q->where('statut', StatutDossier::INDISPONIBLE->value))
            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.indisponible', compact('clients'));
    }

    public function pboSature(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', fn($q) => $q->where('statut', StatutDossier::PBO_SATURE->value))

            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.pbo_sature', compact('clients'));
    }

    public function zoneDepourvue(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', fn($q) => $q->where('statut', StatutDossier::ZONE_DEPOURVUE->value))

            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.zone_depourvue', compact('clients'));
    }

    public function enEquipe(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', fn($q) => $q->where('statut', StatutDossier::EN_EQUIPE->value))
            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.en_equipe', compact('clients'));
    }

    public function depassementLineaire(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', fn($q) => $q->where('statut', StatutDossier::DEPASSEMENT_LINEAIRE->value))
            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.depassement_lineaire', compact('clients'));
    }

    public function implantationPoteau(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', fn($q) => $q->where('statut', StatutDossier::IMPLANTATION_POTEAU->value))
            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.implantation_poteau', compact('clients'));
    }

    public function abandon(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = \App\Models\Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $clients = Client::whereHas('dossiers', fn($q) => $q->where('statut', StatutDossier::ABANDON->value))
            // ➕ restriction chef d’équipe
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds) ? $qry->whereHas('dossiers', fn($dq) => $dq->whereIn('assigned_team_id', $teamIds)) : $qry->whereRaw('0 = 1');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%")
                        ->orWhere('numero_ligne', 'like', "%$search%")
                        ->orWhere('telephone', 'like', "%$search%");
                });
            })
            ->paginate(10);

        return view('clients.dossiers.abandon', compact('clients'));
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

    public function purgeAll()
    {
        DB::transaction(function () {
            // 🔴 Supprimer tous les dossiers liés
            DossierRaccordement::query()->delete();

            // 🔴 Supprimer tous les clients
            Client::query()->delete();
        });

        return redirect()->route('clients.index')->with('success', 'Tous les clients et leurs dossiers ont été supprimés avec succès.');
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
