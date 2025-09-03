<?php

namespace App\Http\Controllers;

use App\Models\DossierRaccordement;
use App\Models\Intervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\StatutDossier;
use Illuminate\Support\Facades\Auth;


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
        $to   = $request->date_to ? date('Y-m-d', strtotime($request->date_to)) : now()->toDateString();

     // On récupère l'équipe dontteams.inbox.close le user est le chef
$teamId = \App\Models\Team::where('lead_id', $user->id)->value('id');

if (!$teamId) {
    abort(403, 'Aucune équipe assignée à ce chef');
}

// Base query : dossiers affectés à l’équipe du chef
$dossierQuery = DossierRaccordement::where('assigned_team_id', $teamId);

        // Statuts
        $STATUTS_OUVERTS = [
            StatutDossier::EN_APPEL->value,
            StatutDossier::EN_EQUIPE->value,
            StatutDossier::INJOIGNABLE->value,
            StatutDossier::PBO_SATURE->value,
            StatutDossier::ZONE_DEPOURVUE->value,
            StatutDossier::ACTIVE->value,
        ];
        $STATUT_REA = StatutDossier::REALISE->value;

        // KPIs simples
        $totalDossiers = $dossierQuery->count();
        $ouverts       = (clone $dossierQuery)->whereIn('statut', $STATUTS_OUVERTS)->count();
        $realises      = (clone $dossierQuery)->where('statut', $STATUT_REA)->count();

        // Corbeille : dossiers non finalisés
        $teamInbox = (clone $dossierQuery)
            ->whereNotIn('statut', [$STATUT_REA])
            ->with(['client', 'technicien'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($d){
                return [
                    'id'        => $d->id,
                    'ref'       => $d->reference,
                    'client'    => $d->client?->displayName,
                    'team'      => $d->assignedTeam?->name,
                    'statut'    => $d->statut,
                    'team_id'   => $d->assigned_team_id, // <-- ici
                    'date'      => $d->date_planifiee,
                    'contrainte'=> $d->contrainte_installation, // si dossier a une contrainte
                    'report'    => $d->date_report, // si client a reporté
                ];
            });

        // Derniers dossiers réalisés
        $lastDossiers = (clone $dossierQuery)
            ->where('statut', $STATUT_REA)
            ->with(['client', 'technicien'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard.chef', compact(
            'from', 'to', 'totalDossiers', 'ouverts', 'realises', 'teamInbox', 'lastDossiers'
        ));
    }
}
