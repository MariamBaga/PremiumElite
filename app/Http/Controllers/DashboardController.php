<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DossierRaccordement;
use App\Models\Intervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Fenêtre de temps filtrable (par défaut : 30 derniers jours)
        $from = $request->date_from ? date('Y-m-d', strtotime($request->date_from)) : now()->subDays(30)->toDateString();
        $to   = $request->date_to   ? date('Y-m-d', strtotime($request->date_to))   : now()->toDateString();

        // Statuts (en base tu stockes des strings ; adapte si besoin)
        $STATUTS_OUVERTS = ['a_traiter','injoignable','pbo_sature','zone_depourvue','en_attente_materiel','replanifie'];
        $STATUT_REA      = 'realise';
        $STATUT_ANNULE   = 'annule';

        // KPIs globaux
        $totalDossiers   = DossierRaccordement::count();
        $ouverts         = DossierRaccordement::whereIn('statut', $STATUTS_OUVERTS)->count();
        $realises        = DossierRaccordement::where('statut', $STATUT_REA)->count();
        $annules         = DossierRaccordement::where('statut', $STATUT_ANNULE)->count();

        $tauxReussite = $realises + $annules > 0
            ? round(100 * $realises / ($realises + $annules), 1)
            : 0.0;

        $pboSature      = DossierRaccordement::where('statut', 'pbo_sature')->count();

        // Délai moyen (création -> date_realisation) sur la fenêtre / global fallback
        $avgDelayQ = DossierRaccordement::where('statut', $STATUT_REA)
            ->whereNotNull('date_realisation');
        $avgDelayDays = (clone $avgDelayQ)
            ->select(DB::raw('AVG(DATEDIFF(date_realisation, created_at)) as d'))->value('d');
        $avgDelayDays = $avgDelayDays ? round($avgDelayDays, 1) : 0.0;

        // Séries temporelles (créés / réalisés) sur la fenêtre
        $createdSeries = DossierRaccordement::whereBetween(DB::raw('DATE(created_at)'), [$from,$to])
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->orderBy('d')->get();

        $realisedSeries = DossierRaccordement::where('statut',$STATUT_REA)
            ->whereBetween(DB::raw('DATE(date_realisation)'), [$from,$to])
            ->selectRaw('DATE(date_realisation) as d, COUNT(*) as c')
            ->groupBy('d')->orderBy('d')->get();

        // Répartition par statut (pour un donut)
        $byStatut = DossierRaccordement::select('statut', DB::raw('COUNT(*) as c'))
            ->groupBy('statut')->pluck('c','statut');

        // Par type de service
        $byTypeService = DossierRaccordement::select('type_service', DB::raw('COUNT(*) as c'))
            ->groupBy('type_service')->pluck('c','type_service');

        // Par zone (via clients)
        $byZone = DossierRaccordement::query()
            ->join('clients','clients.id','=','dossiers_raccordement.client_id')
            ->select('clients.zone', DB::raw('COUNT(*) as c'))
            ->groupBy('clients.zone')
            ->orderByDesc('c')
            ->limit(8)->get();

        // Top techniciens (dossiers réalisés)
        $topTechs = DossierRaccordement::query()
            ->leftJoin('users','users.id','=','dossiers_raccordement.assigned_to')
            ->where('dossiers_raccordement.statut',$STATUT_REA)
            ->select('users.name', DB::raw('COUNT(*) as done'))
            ->groupBy('users.name')
            ->orderByDesc('done')->limit(5)->get();

        // Interventions : volume + durée moyenne
        $intervCount = Intervention::whereBetween(DB::raw('DATE(created_at)'), [$from,$to])->count();
        $intervAvgDuration = Intervention::whereNotNull('debut')->whereNotNull('fin')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE,debut,fin)) as m'))
            ->value('m');
        $intervAvgDuration = $intervAvgDuration ? round($intervAvgDuration) : 0;

        // Dernières activités
        $lastDossiers      = DossierRaccordement::with(['client','technicien'])->latest()->limit(8)->get();
        $lastInterventions = Intervention::with(['dossier.client','technicien'])->latest()->limit(8)->get();

        // Préparer données pour Chart.js (labels + datasets)
        $labels   = [];
        $created  = [];
        $realised = [];
        // construire toutes les dates du range
        for ($d = strtotime($from); $d <= strtotime($to); $d = strtotime('+1 day', $d)) {
            $key = date('Y-m-d', $d);
            $labels[]  = $key;
            $created[] = (int)($createdSeries->firstWhere('d', $key)->c ?? 0);
            $realised[]= (int)($realisedSeries->firstWhere('d', $key)->c ?? 0);
        }

        return view('dashboard.index', compact(
            'from','to',
            'totalDossiers','ouverts','realises','annules','tauxReussite','pboSature','avgDelayDays',
            'byStatut','byTypeService','byZone','topTechs',
            'intervCount','intervAvgDuration',
            'lastDossiers','lastInterventions',
            'labels','created','realised'
        ));
    }
}
