<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DossierRaccordement;
use App\Models\Intervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\StatutDossier;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\TeamDossier;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Fenêtre temporelle
        $from = $request->date_from ? date('Y-m-d', strtotime($request->date_from)) : now()->subDays(30)->toDateString();
        $to = $request->date_to ? date('Y-m-d', strtotime($request->date_to)) : now()->toDateString();

        // Base queries
        $clientQuery = Client::query();
        $dossierQuery = DossierRaccordement::query();

        // 🔒 Filtre chef d’équipe
        if ($user->hasRole('chef_equipe')) {
            $clientQuery->whereHas('dossiers', fn($q) => $q->where('assigned_team_id', $user->team_id));

            $dossierQuery->where('assigned_team_id', $user->team_id);
        }
        $teams = \App\Models\Team::all();
        // Récupérer les performances par équipe (7 derniers jours par exemple)
        $teamsKpi = $teams->map(function($team) use ($from, $to) {
            $stats = \App\Models\DossierRaccordement::where('assigned_team_id', $team->id)
                ->whereBetween('date_fin_travaux', [$from, $to])
                ->selectRaw("
                    SUM(CASE WHEN statut = '".StatutDossier::REALISE->value."' THEN 1 ELSE 0 END) as realises,
                    SUM(CASE WHEN statut = '".StatutDossier::ACTIVE->value."' THEN 1 ELSE 0 END) as actives,
                    SUM(CASE WHEN statut = '".StatutDossier::PBO_SATURE->value."' THEN 1 ELSE 0 END) as pbo_satures
                ")
                ->first();

            return [
                'team_id'     => $team->id,
                'team_name'   => $team->name,
                'realises'    => (int) ($stats->realises ?? 0),
                'actives'     => (int) ($stats->actives ?? 0),
                'pbo_satures' => (int) ($stats->pbo_satures ?? 0),
            ];
        });


        // KPI équipes pour aujourd'hui (exemple : même structure que $teamsKpi mais filtré sur la date du jour)

        $teamsKpiToday = DossierRaccordement::select(
            'assigned_team_id',
            DB::raw("SUM(CASE WHEN statut = '".StatutDossier::REALISE->value."' THEN 1 ELSE 0 END) as realises"),
            DB::raw("SUM(CASE WHEN statut = '".StatutDossier::ACTIVE->value."' THEN 1 ELSE 0 END) as actives"),
            DB::raw("SUM(CASE WHEN statut = '".StatutDossier::PBO_SATURE->value."' THEN 1 ELSE 0 END) as pbo_satures")
        )
        ->where(function($q) {
            $q->whereDate('date_fin_travaux', now()->toDateString())   // date aujourd’hui
              ->orWhere('statut', StatutDossier::REALISE->value)       // déjà réalisé
              ->orWhere('statut', StatutDossier::ACTIVE->value);       // déjà actif
        })
        ->groupBy('assigned_team_id')
        ->with('assignedTeam')
        ->get()
        ->map(function($item) {
            return [
                'team_id'     => $item->assigned_team_id,
                'team_name'   => $item->assignedTeam?->name ?? 'Équipe inconnue',
                'realises'    => (int) $item->realises,
                'actives'     => (int) $item->actives,
                'pbo_satures' => (int) $item->pbo_satures,
            ];
        });






        // Clients distincts
        $totalClients = $clientQuery->distinct()->count('clients.id');

        // ========= Expressions SGBD =========
        $driver = DB::getDriverName();
        $dateExpr = fn(string $col) => match ($driver) {
            'mysql' => "DATE($col)",
            'pgsql' => "$col::date",
            'sqlite' => "date($col)",
            default => "date($col)",
        };
        $diffDaysExpr = match ($driver) {
            'mysql' => 'DATEDIFF(date_realisation, created_at)',
            'pgsql' => "DATE_PART('day', date_realisation - created_at)",
            'sqlite' => 'julianday(date_realisation) - julianday(created_at)',
            default => 'julianday(date_realisation) - julianday(created_at)',
        };
        $diffMinutesExpr = match ($driver) {
            'mysql' => 'TIMESTAMPDIFF(MINUTE, debut, fin)',
            'pgsql' => 'EXTRACT(EPOCH FROM (fin - debut)) / 60',
            'sqlite' => '(julianday(fin) - julianday(debut)) * 1440',
            default => '(julianday(fin) - julianday(debut)) * 1440',
        };

        // ========= Statuts =========
        $STATUTS_OUVERTS = [StatutDossier::EN_APPEL->value, StatutDossier::EN_EQUIPE->value, StatutDossier::INJOIGNABLE->value, StatutDossier::PBO_SATURE->value, StatutDossier::ZONE_DEPOURVUE->value, StatutDossier::ACTIVE->value];
        $STATUT_REA = StatutDossier::REALISE->value;
        $annules = 0;

        // ========= KPIs =========
        $totalDossiers = (clone $dossierQuery)->count();
        $ouverts = (clone $dossierQuery)->whereIn('statut', $STATUTS_OUVERTS)->count();
        $realises = (clone $dossierQuery)->where('statut', $STATUT_REA)->count();
        $pboSature = (clone $dossierQuery)->where('statut', StatutDossier::PBO_SATURE->value)->count();
        $tauxReussite = $totalDossiers > 0 ? round((100 * $realises) / $totalDossiers, 1) : 0.0;

        // ========= Délai moyen =========
        $avgDelayDays = (clone $dossierQuery)
            ->where('statut', $STATUT_REA)
            ->whereNotNull('date_realisation')
            ->selectRaw("AVG($diffDaysExpr) AS d")
            ->value('d');
        $avgDelayDays = $avgDelayDays ? round((float) $avgDelayDays, 1) : 0.0;

        // ========= Séries temporelles =========
        $createdSeries = (clone $dossierQuery)
            ->whereBetween(DB::raw($dateExpr('created_at')), [$from, $to])
            ->selectRaw($dateExpr('created_at') . ' AS d, COUNT(*) AS c')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $realisedSeries = (clone $dossierQuery)
            ->where('statut', $STATUT_REA)
            ->whereBetween(DB::raw($dateExpr('date_realisation')), [$from, $to])
            ->selectRaw($dateExpr('date_realisation') . ' AS d, COUNT(*) AS c')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        // ========= Répartitions =========
        $byStatut = (clone $dossierQuery)->select('statut', DB::raw('COUNT(*) as c'))->groupBy('statut')->pluck('c', 'statut');

        $byTypeService = (clone $dossierQuery)->select('type_service', DB::raw('COUNT(*) as c'))->groupBy('type_service')->pluck('c', 'type_service');

        $byZone = (clone $dossierQuery)->join('clients', 'clients.id', '=', 'dossiers_raccordement.client_id')->select('clients.zone', DB::raw('COUNT(*) as c'))->groupBy('clients.zone')->orderByDesc('c')->limit(8)->get();

        // ========= Top techniciens =========
        $topTechs = (clone $dossierQuery)->leftJoin('users', 'users.id', '=', 'dossiers_raccordement.assigned_to')->where('dossiers_raccordement.statut', $STATUT_REA)->select('users.name', DB::raw('COUNT(*) as done'))->groupBy('users.name')->orderByDesc('done')->limit(5)->get();

        // ========= Interventions =========
        $intervCount = Intervention::whereBetween(DB::raw($dateExpr('created_at')), [$from, $to])->count();
        $intervAvgDuration = Intervention::whereNotNull('debut')
            ->whereNotNull('fin')
            ->selectRaw("AVG($diffMinutesExpr) AS m")
            ->value('m');
        $intervAvgDuration = $intervAvgDuration ? (int) round((float) $intervAvgDuration) : 0;

        // ========= Dernières activités =========
        $lastDossiers = (clone $dossierQuery)
            ->with(['client', 'technicien'])
            ->latest()
            ->limit(8)
            ->get();

        $lastInterventions = Intervention::with(['dossier.client', 'technicien'])
            ->latest()
            ->limit(8)
            ->get();

        // ========= Séries cumulées =========
        $labels = $created = $realised = [];
        for ($d = strtotime($from); $d <= strtotime($to); $d = strtotime('+1 day', $d)) {
            $key = date('Y-m-d', $d);
            $labels[] = $key;
            $created[] = (int) ($createdSeries->firstWhere('d', $key)->c ?? 0);
            $realised[] = (int) ($realisedSeries->firstWhere('d', $key)->c ?? 0);
        }

        $createdCum = $realisedCum = [];
        $sumC = $sumR = 0;
        foreach ($created as $i => $v) {
            $sumC += (int) $v;
            $sumR += (int) ($realised[$i] ?? 0);
            $createdCum[] = $sumC;
            $realisedCum[] = $sumR;
        }




        // Dossiers corbeille : statut en attente / reporté / contrainte / etc.

        // Comptage par équipe uniquement si assigned_team_id n'est pas null
        $corbeilleCount = DossierRaccordement::select('assigned_team_id', \DB::raw('count(*) as total'))
            ->whereNotNull('assigned_team_id') // uniquement dossiers avec équipe
            ->whereNotIn('statut', [$STATUT_REA])
            ->groupBy('assigned_team_id')
            ->pluck('total', 'assigned_team_id');

        // Remplacer ID → nom d'équipe
        $teams = \App\Models\Team::whereIn('id', $corbeilleCount->keys())->pluck('name', 'id');

        $corbeilleCount = $corbeilleCount->mapWithKeys(function ($count, $teamId) use ($teams) {
            return [$teams[$teamId] ?? 'Équipe inconnue' => $count];
        });

        // Somme totale
        $totalCorbeille = $corbeilleCount->sum();

        // Dossiers actifs
        $activeCount = (clone $dossierQuery)
    ->where('statut', StatutDossier::ACTIVE->value)
    ->count();

        // Dossiers avec RDV
        $rdvCount = (clone $dossierQuery)->where('statut', 'nouveau_rendez_vous')->count();
        $rdvDossiers = (clone $dossierQuery)->where('statut', StatutDossier::NOUVEAU_RENDEZ_VOUS->value);

        if ($user->hasRole('chef_equipe')) {
            $rdvDossiers->where('assigned_team_id', $user->team_id);
        }

        $rdvDossiers = $rdvDossiers->get();

       // Dossiers avec RDV manqués (exemple : statut "nouveau_rendez_vous" mais date dépassée)
$rdvManques = (clone $dossierQuery)
->where('statut', StatutDossier::NOUVEAU_RENDEZ_VOUS->value)
->whereDate('date_planifiee', '<', now()) // RDV planifié avant aujourd'hui
->count();

// Dossiers avec RDV réussis ou réalisés
$rdvReussis = (clone $dossierQuery)
->where(function($q) {
    $q->where('statut', StatutDossier::REALISE->value)
      ->orWhere('statut', StatutDossier::ACTIVE->value);
})
->count();

// Passer les données à la vue
return view('dashboard.index', compact(
'from', 'to', 'totalClients', 'totalDossiers', 'ouverts', 'realises',
'annules', 'tauxReussite', 'pboSature', 'avgDelayDays', 'byStatut',
'byTypeService', 'byZone', 'topTechs', 'intervCount', 'intervAvgDuration',
'lastDossiers', 'lastInterventions', 'labels', 'created', 'realised',
'createdCum', 'realisedCum', 'corbeilleCount', 'activeCount', 'rdvCount',
'rdvDossiers', 'totalCorbeille', 'teamsKpi','teamsKpiToday',
'rdvManques', 'rdvReussis'
));

    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        // On reprend les mêmes données que dans index()
        $data = $this->index($request)->getData();

        // Organiser les données à exporter
        $rows = [['Période', $data['from'] . ' → ' . $data['to']], ['Total clients', $data['totalClients']], ['Total dossiers', $data['totalDossiers']], ['Dossiers ouverts', $data['ouverts']], ['Dossiers réalisés', $data['realises']], ['Taux de réussite', $data['tauxReussite'] . ' %'], ['PBO saturés', $data['pboSature']], ['Délai moyen (jours)', $data['avgDelayDays']], [], ['Répartition par statut']];

        foreach ($data['byStatut'] as $statut => $count) {
            $rows[] = [$statut, $count];
        }

        $rows[] = [];
        $rows[] = ['Répartition par type de service'];
        foreach ($data['byTypeService'] as $type => $count) {
            $rows[] = [$type, $count];
        }

        $rows[] = [];
        $rows[] = ['Top techniciens'];
        foreach ($data['topTechs'] as $tech) {
            $rows[] = [$tech->name, $tech->done];
        }

        // Créer un export rapide à partir d’un array
        return Excel::download(
            new class ($rows) implements \Maatwebsite\Excel\Concerns\FromCollection {
                private $rows;
                public function __construct($rows)
                {
                    $this->rows = $rows;
                }
                public function collection()
                {
                    return collect($this->rows);
                }
            },
            'dashboard_recap.xlsx',
        );
    }
}
