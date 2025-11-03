<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DossierRaccordement;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DossiersExport;

class ExportDossierController extends Controller
{



    public function viewClientsActives()
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $dossiers = DossierRaccordement::with('client')
            ->where('statut', 'active')
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds)
                    ? $qry->whereIn('assigned_team_id', $teamIds)
                    : $qry->whereRaw('0 = 1');
            })
            ->paginate(10);

        return view('exports.clients_actives', compact('dossiers'));
    }


    /**
     * 🟦 Vue : Dossiers par équipe et statut
     */
    public function viewEquipeStatut(Request $request)
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            // Récupère uniquement les équipes dont il est le lead
            $teamIds = Team::where('lead_id', $user->id)->pluck('id')->toArray();
            $equipes = Team::whereIn('id', $teamIds)->orderBy('name')->get();
        } else {
            $equipes = Team::orderBy('name')->get();
        }

        $teamId = $request->team_id ?? null;
        $statut = $request->statut ?? null;

        $dossiers = DossierRaccordement::with('client')
            ->when($teamId, fn($q) => $q->where('assigned_team_id', $teamId))
            ->when($statut, fn($q) => $q->where('statut', $statut))
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds)
                    ? $qry->whereIn('assigned_team_id', $teamIds)
                    : $qry->whereRaw('0 = 1');
            })
            ->paginate(10);

        return view('exports.equipe_statut', compact('dossiers', 'teamId', 'statut', 'equipes'));
    }



    // 🔹 Export PDF des clients activés
    public function exportClientsActivesPdf()
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        $dossiers = DossierRaccordement::with('client')
            ->where('statut', 'active')
            ->when($user->hasRole('chef_equipe'), function ($qry) use ($teamIds) {
                return !empty($teamIds)
                    ? $qry->whereIn('assigned_team_id', $teamIds)
                    : $qry->whereRaw('0 = 1'); // Aucun dossier si pas d’équipe
            })
            ->get();

        $pdf = Pdf::loadView('exports.dossiers_pdf', compact('dossiers'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('clients_activés.pdf');
    }


    // 🔹 Export Excel des clients activés
    public function exportClientsActivesExcel()
    {
        $user = auth()->user();
        $teamIds = [];

        if ($user->hasRole('chef_equipe')) {
            $teamIds = Team::where('lead_id', $user->id)->pluck('id')->toArray();
        }

        return Excel::download(
            new DossiersExport('active', $teamIds),
            'clients_activés.xlsx'
        );
    }


    // 🔹 Export PDF des dossiers traités par équipe + statut
    public function exportByTeamAndStatutPdf($teamId, $statut)
    {
        $user = auth()->user();
        $teamIds = $user->hasRole('chef_equipe')
            ? Team::where('lead_id', $user->id)->pluck('id')->toArray()
            : [];

        if ($user->hasRole('chef_equipe') && !in_array($teamId, $teamIds)) {
            abort(403, 'Non autorisé à accéder à cette équipe.');
        }

        $team = Team::findOrFail($teamId);
        $dossiers = DossierRaccordement::with('client')
            ->where('assigned_team_id', $teamId)
            ->where('statut', $statut)
            ->get();

        $pdf = Pdf::loadView('exports.dossiers_pdf', compact('dossiers', 'team', 'statut'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("dossiers_{$team->name}_{$statut}.pdf");
    }

    // 🔹 Export Excel des dossiers traités par équipe + statut
// 🔹 Export Excel des dossiers traités par équipe + statut
public function exportByTeamAndStatutExcel($teamId, $statut)
{
    $user = auth()->user();
    $teamIds = $user->hasRole('chef_equipe')
        ? Team::where('lead_id', $user->id)->pluck('id')->toArray()
        : [];

    if ($user->hasRole('chef_equipe') && !in_array($teamId, $teamIds)) {
        abort(403, 'Non autorisé à accéder à cette équipe.');
    }

    $statutValue = $statut instanceof \App\Enums\StatutDossier ? $statut->value : (string) $statut;

    return Excel::download(
        new DossiersExport($statutValue, $teamId),
        "dossiers_{$teamId}_{$statutValue}.xlsx"
    );
}


}
