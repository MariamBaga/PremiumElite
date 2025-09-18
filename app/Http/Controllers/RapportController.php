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
        return view('rapports.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'statut'    => 'nullable|array',
            'statut.*'  => 'string',
            'format'    => 'required|in:excel,csv,pdf',
        ]);

        $fileName = "rapport_activite_" . now()->format('Ymd_His');

        switch ($request->format) {
            case 'excel':
                return Excel::download(new RapportActiviteExport($request), $fileName.'.xlsx');
            case 'csv':
                return Excel::download(new RapportActiviteExport($request), $fileName.'.csv');
            case 'pdf':
                // PDF via DomPDF
                $dossiers = (new RapportActiviteExport($request))->view()->getData()['dossiers'];
                $pdf = \PDF::loadView('rapports.export', compact('dossiers'));
                return $pdf->download($fileName.'.pdf');
        }
    }
}
