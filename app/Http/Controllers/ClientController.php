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
        // Valider les filtres optionnels
        $data = $request->validate([
            'type'                 => 'nullable|in:residentiel,professionnel',
            'search'               => 'nullable|string|max:200',
            'numero_ligne'         => 'nullable|string|max:50',
            'numero_point_focal'   => 'nullable|string|max:50',
            'localisation'         => 'nullable|string|max:100',
            'date_paiement_from'   => 'nullable|date',
            'date_paiement_to'     => 'nullable|date',
            'date_affect_from'     => 'nullable|date',
            'date_affect_to'       => 'nullable|date',
            'sort'                 => 'nullable|in:created_at,nom,prenom,raison_sociale,numero_ligne,numero_point_focal,localisation,date_paiement,date_affectation',
            'dir'                  => 'nullable|in:asc,desc',
            'per_page'             => 'nullable|integer|min:5|max:100',
        ]);

        $sort = $data['sort'] ?? 'created_at';
        $dir  = $data['dir']  ?? 'desc';
        $per  = $data['per_page'] ?? 10;

        $q = Client::query()
             // Si ce n’est pas un superadmin, on ne montre que ses clients
    ->when(!auth()->user()->hasRole('superadmin'), fn($qry) => $qry->where('created_by', auth()->id()))

            // Filtres simples
            ->when(!empty($data['type']), fn($qry) => $qry->where('type', $data['type']))
            ->when(!empty($data['numero_ligne']), fn($qry) => $qry->where('numero_ligne', 'like', '%'.$data['numero_ligne'].'%'))
            ->when(!empty($data['numero_point_focal']), fn($qry) => $qry->where('numero_point_focal', 'like', '%'.$data['numero_point_focal'].'%'))
            ->when(!empty($data['localisation']), fn($qry) => $qry->where('localisation', 'like', '%'.$data['localisation'].'%'))

            // Filtres par date (intervalle)
            ->when(!empty($data['date_paiement_from']), fn($qry) => $qry->whereDate('date_paiement', '>=', $data['date_paiement_from']))
            ->when(!empty($data['date_paiement_to']),   fn($qry) => $qry->whereDate('date_paiement', '<=', $data['date_paiement_to']))
            ->when(!empty($data['date_affect_from']),   fn($qry) => $qry->whereDate('date_affectation', '>=', $data['date_affect_from']))
            ->when(!empty($data['date_affect_to']),     fn($qry) => $qry->whereDate('date_affectation', '<=', $data['date_affect_to']))

            // Recherche globale (on étend ta recherche existante)
            ->when(!empty($data['search']), function($qry) use ($data) {
                $s = '%'.$data['search'].'%';
                $qry->where(function($sub) use ($s){
                    $sub->where('nom','like',$s)
                        ->orWhere('prenom','like',$s)
                        ->orWhere('raison_sociale','like',$s)
                        ->orWhere('telephone','like',$s)
                        ->orWhere('email','like',$s)
                        ->orWhere('adresse_ligne1','like',$s)
                        ->orWhere('localisation','like',$s)
                        ->orWhere('numero_ligne','like',$s)
                        ->orWhere('numero_point_focal','like',$s);
                });
            })

            // Tri
            ->orderBy($sort, $dir);

        return view('clients.index', [
            'clients'  => $q->paginate($per)->withQueryString(),
            'sort'     => $sort,
            'dir'      => $dir,
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
         $data['created_by'] = auth()->id(); // ← ici
        $client = Client::create($request->validated());

        return redirect()->route('clients.show', $client)->with('success','Dossier d\'abonner créé avec succès.');
    }

    public function show(Client $client)
    {
        if ($client->created_by !== auth()->id() && !auth()->user()->hasRole('superadmin')) {
            abort(403, 'Accès refusé');
        }
        $client->loadCount('dossiers');
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        if ($client->created_by !== auth()->id() && !auth()->user()->hasRole('superadmin')) {
            abort(403, 'Accès refusé');
        }
        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());
        return redirect()->route('clients.show', $client)->with('success','Dossier d\'abonner mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('ftth.index')->with('success','Dossier d\'abonner supprimé.');
    }



public function exportToDossiers(Request $request)
{
    $data = $request->validate([
        'client_ids'       => 'required|array|min:1',
        'client_ids.*'     => 'exists:clients,id',
        'nature'           => 'required|in:raccordement,maintenance',
        'assigned_team_id' => 'nullable|exists:teams,id',
    ]);

    $created = 0;
    foreach ($data['client_ids'] as $cid) {
        DossierRaccordement::create([
            'client_id'        => $cid,
            'reference'        => 'DR-'.date('Y').'-'.str_pad(DossierRaccordement::max('id')+1, 5, '0', STR_PAD_LEFT),
            'type_service'     => 'residentiel',  // ou détecter depuis client->type
            'nature'           => $data['nature'],
            'statut'           => 'en_equipe',    // direct dans corbeille équipe / boîte d’équipe
            'assigned_team_id' => $data['assigned_team_id'] ?? null,
        ]);
        $created++;
    }

    return back()->with('success', "$created dossiers créés.");
}


    public function deleteAll()
    {
        DB::transaction(function () {
            // 1) si tu veux AUSSI vider les dossiers liés aux clients :
            DossierRaccordement::query()->delete(); // (ou truncate si pas de FK entrante)

            // 2) ensuite, vider les clients
            Client::query()->delete(); // <- PAS truncate
        });

        return redirect()->route('clients.index')
            ->with('success', 'Tous les clients (et dossiers liés) ont été supprimés.');
    }

}
