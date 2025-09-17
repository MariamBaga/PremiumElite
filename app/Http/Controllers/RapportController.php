<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DossierRaccordement;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RapportActiviteExport;

class RapportController extends Controller
{
    public function index()
    {
        // Juste afficher le formulaire avec filtres
        return view('rapports.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'format'    => 'required|in:excel,csv,pdf',
        ]);

        // Export avec Laravel Excel
        $fileName = "rapport_activite_" . now()->format('Ymd_His');

        switch ($request->format) {
            case 'excel':
                return Excel::download(new RapportActiviteExport($request), $fileName.'.xlsx');
            case 'csv':
                return Excel::download(new RapportActiviteExport($request), $fileName.'.csv');
            case 'pdf':
                return Excel::download(new RapportActiviteExport($request), $fileName.'.pdf');
        }
    }
}
