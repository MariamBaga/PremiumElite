<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DossierRaccordement;
use App\Models\Intervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\StatutDossier;
use App\Models\TeamDossier;



class DashboardController extends Controller
{
    public function index(Request $request)
    {




        // Par équipe
$teamKpis = TeamDossier::selectRaw('team_id, etat, COUNT(*) c')
->actifs()
->groupBy('team_id','etat')
->get()
->groupBy('team_id');

// Global (actifs dans la corbeille)
$corbeilleActifs = TeamDossier::actifs()->count();

// Pour affichage : nombre de “contraintes” actives
$contraintesActives = TeamDossier::where('etat','contrainte')->count();

// Reports planifiés à venir
$reportsAVenir = TeamDossier::where('etat','reporte')
->whereNotNull('date_report')
->where('date_report','>=',now())->count();




        $totalClients = Client::count();

        // Fenêtre de temps filtrable (par défaut : 30 derniers jours)
        $from = $request->date_from ? date('Y-m-d', strtotime($request->date_from)) : now()->subDays(30)->toDateString();
        $to   = $request->date_to   ? date('Y-m-d', strtotime($request->date_to))   : now()->toDateString();

       // ========= 1) Expressions portables selon le SGBD =========
    $driver = DB::getDriverName();

    // fonction pour extraire la date (YYYY-MM-DD) d'une colonne datetime
    $dateExpr = function (string $col) use ($driver) {
        return match ($driver) {
            'mysql'  => "DATE($col)",          // MySQL
            'pgsql'  => "$col::date",          // PostgreSQL
            'sqlite' => "date($col)",          // SQLite (si format 'YYYY-MM-DD HH:MM:SS')
            default  => "date($col)",
        };
    };

    // différence en JOURS entre deux datetimes
    $diffDaysExpr = match ($driver) {
        'mysql'  => 'DATEDIFF(date_realisation, created_at)',
        'pgsql'  => "DATE_PART('day', date_realisation - created_at)",
        'sqlite' => 'julianday(date_realisation) - julianday(created_at)',
        default  => 'julianday(date_realisation) - julianday(created_at)',
    };

    // différence en MINUTES entre deux datetimes (pour Intervention)
    $diffMinutesExpr = match ($driver) {
        'mysql'  => 'TIMESTAMPDIFF(MINUTE, debut, fin)',
        'pgsql'  => "EXTRACT(EPOCH FROM (fin - debut)) / 60",
        'sqlite' => '(julianday(fin) - julianday(debut)) * 1440',
        default  => '(julianday(fin) - julianday(debut)) * 1440',
    };

    // ========= 2) Tes KPIs (inchangés) =========
    // ========= 2) Tes KPIs adaptés à tes statuts =========
// Statuts "ouverts" avec ce que tu as actuellement
$STATUTS_OUVERTS = [
    StatutDossier::EN_APPEL->value,
    StatutDossier::EN_EQUIPE->value,
    StatutDossier::INJOIGNABLE->value,
    StatutDossier::PBO_SATURE->value,
    StatutDossier::ZONE_DEPOURVUE->value,
    StatutDossier::ON->value, // tu l'utilises, donc on l'inclut
];

$STATUT_REA    = StatutDossier::REALISE->value;

// Pas de statut "annule" chez toi → on met 0 pour garder la compatibilité avec la vue
$annules       = 0;

$totalDossiers = DossierRaccordement::count();
$ouverts       = DossierRaccordement::whereIn('statut', $STATUTS_OUVERTS)->count();
$realises      = DossierRaccordement::where('statut', $STATUT_REA)->count();

// Taux de réussite : avec tes statuts actuels, on définit "réussite" = réalisés / total
$tauxReussite  = $totalDossiers > 0 ? round(100 * $realises / $totalDossiers, 1) : 0.0;

$pboSature     = DossierRaccordement::where('statut', StatutDossier::PBO_SATURE->value)->count();


    // ========= 3) Délai moyen (portable) =========
    $avgDelayQ = DossierRaccordement::where('statut', $STATUT_REA)
        ->whereNotNull('date_realisation');

    $avgDelayDays = $avgDelayQ
        ->selectRaw("AVG($diffDaysExpr) AS d")
        ->value('d');

    $avgDelayDays = $avgDelayDays ? round((float)$avgDelayDays, 1) : 0.0;

    // ========= 4) Séries temporelles (portable) =========
    $from = $request->date_from ? date('Y-m-d', strtotime($request->date_from)) : now()->subDays(30)->toDateString();
    $to   = $request->date_to   ? date('Y-m-d', strtotime($request->date_to))   : now()->toDateString();

    $createdSeries = DossierRaccordement::whereBetween(DB::raw($dateExpr('created_at')), [$from, $to])
        ->selectRaw($dateExpr('created_at') . ' AS d, COUNT(*) AS c')
        ->groupBy('d')
        ->orderBy('d')
        ->get();

    $realisedSeries = DossierRaccordement::where('statut', $STATUT_REA)
        ->whereBetween(DB::raw($dateExpr('date_realisation')), [$from, $to])
        ->selectRaw($dateExpr('date_realisation') . ' AS d, COUNT(*) AS c')
        ->groupBy('d')
        ->orderBy('d')
        ->get();

    // ========= 5) Répartitions (inchangées) =========
    $byStatut = DossierRaccordement::select('statut', DB::raw('COUNT(*) as c'))
        ->groupBy('statut')->pluck('c','statut');

    $byTypeService = DossierRaccordement::select('type_service', DB::raw('COUNT(*) as c'))
        ->groupBy('type_service')->pluck('c','type_service');

    $byZone = DossierRaccordement::query()
        ->join('clients','clients.id','=','dossiers_raccordement.client_id')
        ->select('clients.zone', DB::raw('COUNT(*) as c'))
        ->groupBy('clients.zone')
        ->orderByDesc('c')
        ->limit(8)->get();

    $topTechs = DossierRaccordement::query()
        ->leftJoin('users','users.id','=','dossiers_raccordement.assigned_to')
        ->where('dossiers_raccordement.statut',$STATUT_REA)
        ->select('users.name', DB::raw('COUNT(*) as done'))
        ->groupBy('users.name')
        ->orderByDesc('done')->limit(5)->get();

    // ========= 6) Interventions : minutes moyennes (portable) =========
    $intervCount = Intervention::whereBetween(DB::raw($dateExpr('created_at')), [$from, $to])->count();

    $intervAvgDuration = Intervention::whereNotNull('debut')->whereNotNull('fin')
        ->selectRaw("AVG($diffMinutesExpr) AS m")
        ->value('m');

    $intervAvgDuration = $intervAvgDuration ? (int) round((float)$intervAvgDuration) : 0;

    // ========= 7) Dernières activités + séries cumulées (inchangées) =========
    $lastDossiers      = DossierRaccordement::with(['client','technicien'])->latest()->limit(8)->get();
    $lastInterventions = Intervention::with(['dossier.client','technicien'])->latest()->limit(8)->get();

    $labels = $created = $realised = [];
    for ($d = strtotime($from); $d <= strtotime($to); $d = strtotime('+1 day', $d)) {
        $key = date('Y-m-d', $d);
        $labels[]  = $key;
        $created[] = (int)($createdSeries->firstWhere('d', $key)->c ?? 0);
        $realised[]= (int)($realisedSeries->firstWhere('d', $key)->c ?? 0);
    }

    $createdCum = $realisedCum = [];
    $sumC = $sumR = 0;
    foreach ($created as $i => $v) {
        $sumC += (int)$v;
        $sumR += (int)($realised[$i] ?? 0);
        $createdCum[]  = $sumC;
        $realisedCum[] = $sumR;
    }

    return view('dashboard.index', compact(
        'from','to',
        'totalDossiers','ouverts','realises','annules','tauxReussite','pboSature','avgDelayDays',
        'byStatut','byTypeService','byZone','topTechs',
        'intervCount','intervAvgDuration',
        'lastDossiers','lastInterventions',
        'labels','created','realised','createdCum','realisedCum',
        'totalClients'
    ));
}
}
