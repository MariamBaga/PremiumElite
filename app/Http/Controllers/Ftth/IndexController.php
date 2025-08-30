<?php

namespace App\Http\Controllers\Ftth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\DossierRaccordement;

class IndexController extends Controller
{
    /**
     * Page "Index unique" : onglets Clients / Dossiers.
     * Vue : resources/views/ftth/index.blade.php
     */
    public function __invoke(Request $request)
    {
        $tab = $request->get('tab', 'clients');

        // ======== CLIENTS ========
        $clientsQuery = Client::query();

        // Filtres Clients (mêmes noms que dans ton formulaire)
        $dataC = $request->validate([
            'type'               => 'nullable|in:residentiel,professionnel',
            'search'             => 'nullable|string|max:200',
            'numero_ligne'       => 'nullable|string|max:50',
            'numero_point_focal' => 'nullable|string|max:50',
            'localisation'       => 'nullable|string|max:100',
            'date_paiement_from' => 'nullable|date',
            'date_paiement_to'   => 'nullable|date',
            'date_affect_from'   => 'nullable|date',
            'date_affect_to'     => 'nullable|date',
            'sort'               => 'nullable|in:created_at,nom,prenom,raison_sociale,numero_ligne,numero_point_focal,localisation,date_paiement,date_affectation',
            'dir'                => 'nullable|in:asc,desc',
            'per_page'           => 'nullable|integer|min:5|max:100',
        ]);

        $sort = $dataC['sort'] ?? 'created_at';
        $dir  = $dataC['dir']  ?? 'desc';
        $perC = $dataC['per_page'] ?? 15;

        $clientsQuery
            ->when(!empty($dataC['type']),               fn($q) => $q->where('type', $dataC['type']))
            ->when(!empty($dataC['numero_ligne']),       fn($q) => $q->where('numero_ligne', 'like', '%'.$dataC['numero_ligne'].'%'))
            ->when(!empty($dataC['numero_point_focal']), fn($q) => $q->where('numero_point_focal', 'like', '%'.$dataC['numero_point_focal'].'%'))
            ->when(!empty($dataC['localisation']),       fn($q) => $q->where('localisation', 'like', '%'.$dataC['localisation'].'%'))
            ->when(!empty($dataC['date_paiement_from']), fn($q) => $q->whereDate('date_paiement', '>=', $dataC['date_paiement_from']))
            ->when(!empty($dataC['date_paiement_to']),   fn($q) => $q->whereDate('date_paiement', '<=', $dataC['date_paiement_to']))
            ->when(!empty($dataC['date_affect_from']),   fn($q) => $q->whereDate('date_affectation', '>=', $dataC['date_affect_from']))
            ->when(!empty($dataC['date_affect_to']),     fn($q) => $q->whereDate('date_affectation', '<=', $dataC['date_affect_to']))
            ->when(!empty($dataC['search']), function($q) use ($dataC){
                $s = '%'.$dataC['search'].'%';
                $q->where(function($sub) use ($s){
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
            ->orderBy($sort, $dir);

        $clients = $clientsQuery->paginate($perC)->withQueryString();

        // ======== DOSSIERS ========
        $dossiersQuery = DossierRaccordement::query()->with(['client','technicien','team']);
        $dataD = $request->validate([
            'statut'        => 'nullable|string|max:50',
            'type_service'  => 'nullable|in:residentiel,professionnel',
            'per_page_d'    => 'nullable|integer|min:5|max:100',
        ]);

        $perD = $dataD['per_page_d'] ?? 15;

        $dossiersQuery
            ->when(!empty($dataD['statut']),       fn($q) => $q->where('statut', $dataD['statut']))
            ->when(!empty($dataD['type_service']), fn($q) => $q->where('type_service', $dataD['type_service']))
            ->latest();

        $dossiers = $dossiersQuery->paginate($perD)->withQueryString();

        $statuts = \App\Enums\StatutDossier::labels();

        return view('ftth.index', compact('tab','clients','dossiers','statuts'));
    }
}
