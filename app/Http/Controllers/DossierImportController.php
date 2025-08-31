<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DossiersImport;

class DossierImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new DossiersImport, $request->file('file'));

        return redirect()->route('dossiers.index')
                         ->with('success','Import terminé avec succès.');
    }

}
