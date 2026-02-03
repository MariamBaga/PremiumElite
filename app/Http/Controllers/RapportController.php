<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DossierRaccordement;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RapportActiviteExport;
use App\Models\Team; // Ajouter cette importation

class RapportController extends Controller
{
    public function index()
    {
        $teams = Team::all(); // Récupérer toutes les équipes
        return view('rapports.index', compact('teams'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'statut'    => 'nullable|array',
            'statut.*'  => 'string',
            'team_id'   => 'nullable|exists:teams,id',
            'format'    => 'required|in:excel,csv,pdf',
        ]);

        // ✅ Si "Exporter tous les statuts" est cliqué
        if ($request->has('all_statuses')) {
            $request->merge(['statut' => array_keys(\App\Enums\StatutDossier::labels())]);
        }

        $selectedStatuses = $request->input('statut', []);
        $fileName = "rapport_activite_" . now()->format('Ymd_His');

        switch ($request->format) {
            case 'excel':
                return Excel::download(new RapportActiviteExport($request), $fileName.'.xlsx');
            case 'csv':
                return Excel::download(new RapportActiviteExport($request), $fileName.'.csv');
            case 'pdf':
                $dossiers = (new RapportActiviteExport($request))->view()->getData()['dossiers'];
                $pdf = \PDF::loadView('rapports.export', compact('dossiers', 'selectedStatuses'));
                return $pdf->download($fileName.'.pdf');
        }
    }
}
