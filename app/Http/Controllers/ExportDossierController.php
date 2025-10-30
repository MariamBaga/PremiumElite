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
        $dossiers = DossierRaccordement::with('client')
            ->where('statut', 'active')
            ->paginate(10); // ✅ Pagination

        return view('exports.clients_actives', compact('dossiers'));
    }


    /**
     * 🟦 Vue : Dossiers par équipe et statut
     */
    public function viewEquipeStatut(Request $request)
    {
        $teamId = $request->team_id ?? null;
        $statut = $request->statut ?? null;

        // ✅ Récupérer toutes les équipes depuis la base
        $equipes = \App\Models\Team::orderBy('name')->get();

        // ✅ Charger les dossiers filtrés (si filtres appliqués)
        $dossiers = DossierRaccordement::with('client')
            ->when($teamId, fn($q) => $q->where('assigned_team_id', $teamId))
            ->when($statut, fn($q) => $q->where('statut', $statut))
            ->paginate(10); // ✅ Pagination

        // ✅ Passer aussi la liste des équipes à la vue
        return view('exports.equipe_statut', compact('dossiers', 'teamId', 'statut', 'equipes'));
    }

    // 🔹 Export PDF des clients activés
    public function exportClientsActivesPdf()
    {
        $dossiers = DossierRaccordement::with('client')
            ->where('statut', 'active')
            ->get();

        $pdf = Pdf::loadView('exports.dossiers_pdf', compact('dossiers'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('clients_activés.pdf');
    }

    // 🔹 Export Excel des clients activés
    public function exportClientsActivesExcel()
    {
        return Excel::download(new DossiersExport('active'), 'clients_activés.xlsx');
    }

    // 🔹 Export PDF des dossiers traités par équipe + statut
    public function exportByTeamAndStatutPdf($teamId, $statut)
    {
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
    // Forcer le statut en string (au cas où c'est un Enum)
    $statutValue = $statut instanceof \App\Enums\StatutDossier ? $statut->value : (string) $statut;

    return Excel::download(
        new DossiersExport($statutValue, $teamId),
        "dossiers_{$teamId}_{$statutValue}.xlsx"
    );
}

}
