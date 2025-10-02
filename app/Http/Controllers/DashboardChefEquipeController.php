<?php

namespace App\Http\Controllers;

use App\Models\DossierRaccordement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\StatutDossier;
use App\Models\Team;

class DashboardChefEquipeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('chef_equipe')) {
            abort(403, 'Accès refusé');
        }

        // Fenêtre temporelle
        $from = $request->date_from ? date('Y-m-d', strtotime($request->date_from)) : now()->subDays(30)->toDateString();
        $to = $request->date_to ? date('Y-m-d', strtotime($request->date_to)) : now()->toDateString();

        // Récupérer tous les IDs d'équipes dont l'utilisateur est lead
        $teamIds = Team::where('lead_id', $user->id)->pluck('id')->toArray();

        if (empty($teamIds)) {
            abort(403, 'Aucune équipe assignée à ce chef');
        }

        // Base query : dossiers affectés aux équipes du chef
        $dossierQuery = DossierRaccordement::whereIn('assigned_team_id', $teamIds);

        // Statuts
        $STATUTS_OUVERTS = [
            StatutDossier::EN_APPEL->value,
            StatutDossier::EN_EQUIPE->value,
            StatutDossier::INJOIGNABLE->value,
            StatutDossier::PBO_SATURE->value,
            StatutDossier::ZONE_DEPOURVUE->value,
            StatutDossier::ACTIVE->value
        ];
        $STATUT_REA = StatutDossier::REALISE->value;

        // KPIs simples
        $totalDossiers = $dossierQuery->count();
        $ouverts = (clone $dossierQuery)->whereIn('statut', $STATUTS_OUVERTS)->count();
        $realises = (clone $dossierQuery)->where('statut', $STATUT_REA)->count();

        // Corbeille : dossiers non finalisés
        $teamInbox = (clone $dossierQuery)
            ->whereNotIn('statut', [$STATUT_REA])
            ->with(['client', 'technicien', 'assignedTeam'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'ref' => $d->reference,
                    'client' => $d->client?->displayName,
                    'team' => $d->assignedTeam?->name,
                    'statut' => $d->statut,
                    'team_id' => $d->assigned_team_id,
                    'date' => $d->date_planifiee,
                    'contrainte' => $d->contrainte_installation,
                    'report' => $d->date_report,
                ];
            });

        // Derniers dossiers réalisés
        $lastDossiers = (clone $dossierQuery)
            ->where('statut', $STATUT_REA)
            ->with(['client', 'technicien', 'assignedTeam'])
            ->latest()
            ->limit(8)
            ->get();

        $corbeilleCount = (clone $dossierQuery)
            ->whereNotIn('statut', [$STATUT_REA])
            ->count();

        // Dossiers actifs
        $activeCount = (clone $dossierQuery)
            ->where('statut', 'ACTIVE')
            ->count();

        // Dossiers avec RDV
        $rdvCount = (clone $dossierQuery)
            ->where('statut', StatutDossier::NOUVEAU_RENDEZ_VOUS->value)
            ->count();

        $rdvDossiers = (clone $dossierQuery)
            ->where('statut', StatutDossier::NOUVEAU_RENDEZ_VOUS->value)
            ->get();

        return view('dashboard.chef', compact(
            'from',
            'to',
            'totalDossiers',
            'ouverts',
            'realises',
            'teamInbox',
            'lastDossiers',
            'corbeilleCount',
            'activeCount',
            'rdvCount',
            'rdvDossiers'
        ));
    }
}
