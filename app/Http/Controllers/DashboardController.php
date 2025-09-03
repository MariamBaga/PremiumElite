<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DossierRaccordement;
use App\Models\Intervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\StatutDossier;
use Illuminate\Support\Facades\Auth;

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
            $clientQuery->whereHas('dossiers', fn($q) =>
                $q->where('assigned_team_id', $user->team_id)
            );

            $dossierQuery->where('assigned_team_id', $user->team_id);
        }

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
        $STATUTS_OUVERTS = [
            StatutDossier::EN_APPEL->value,
            StatutDossier::EN_EQUIPE->value,
            StatutDossier::INJOIGNABLE->value,
            StatutDossier::PBO_SATURE->value,
            StatutDossier::ZONE_DEPOURVUE->value,
            StatutDossier::ACTIVE->value,
        ];
        $STATUT_REA = StatutDossier::REALISE->value;
        $annules = 0;

        // ========= KPIs =========
        $totalDossiers = (clone $dossierQuery)->count();
        $ouverts       = (clone $dossierQuery)->whereIn('statut', $STATUTS_OUVERTS)->count();
        $realises      = (clone $dossierQuery)->where('statut', $STATUT_REA)->count();
        $pboSature     = (clone $dossierQuery)->where('statut', StatutDossier::PBO_SATURE->value)->count();
        $tauxReussite  = $totalDossiers > 0 ? round((100 * $realises) / $totalDossiers, 1) : 0.0;

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
        $byStatut = (clone $dossierQuery)
            ->select('statut', DB::raw('COUNT(*) as c'))
            ->groupBy('statut')
            ->pluck('c', 'statut');

        $byTypeService = (clone $dossierQuery)
            ->select('type_service', DB::raw('COUNT(*) as c'))
            ->groupBy('type_service')
            ->pluck('c', 'type_service');

        $byZone = (clone $dossierQuery)
            ->join('clients', 'clients.id', '=', 'dossiers_raccordement.client_id')
            ->select('clients.zone', DB::raw('COUNT(*) as c'))
            ->groupBy('clients.zone')
            ->orderByDesc('c')
            ->limit(8)
            ->get();

        // ========= Top techniciens =========
        $topTechs = (clone $dossierQuery)
            ->leftJoin('users', 'users.id', '=', 'dossiers_raccordement.assigned_to')
            ->where('dossiers_raccordement.statut', $STATUT_REA)
            ->select('users.name', DB::raw('COUNT(*) as done'))
            ->groupBy('users.name')
            ->orderByDesc('done')
            ->limit(5)
            ->get();

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
            $labels[]   = $key;
            $created[]  = (int) ($createdSeries->firstWhere('d', $key)->c ?? 0);
            $realised[] = (int) ($realisedSeries->firstWhere('d', $key)->c ?? 0);
        }

        $createdCum = $realisedCum = [];
        $sumC = $sumR = 0;
        foreach ($created as $i => $v) {
            $sumC += (int) $v;
            $sumR += (int) ($realised[$i] ?? 0);
            $createdCum[]   = $sumC;
            $realisedCum[]  = $sumR;
        }

        // ========= Retour =========
        return view('dashboard.index', compact(
            'from', 'to',
            'totalClients', 'totalDossiers',
            'ouverts', 'realises', 'annules', 'tauxReussite', 'pboSature',
            'avgDelayDays',
            'byStatut', 'byTypeService', 'byZone',
            'topTechs',
            'intervCount', 'intervAvgDuration',
            'lastDossiers', 'lastInterventions',
            'labels', 'created', 'realised', 'createdCum', 'realisedCum'
        ));
    }
}
