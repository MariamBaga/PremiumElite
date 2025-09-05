<?php
// app/Http/Controllers/ClientImportController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ClientsImport;
use Maatwebsite\Excel\Facades\Excel;

class ClientImportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:2248', // max 2MB
        ]);

        try {
            Excel::import(new ClientsImport, $data['file']);
            return back()->with('success', 'Import terminé avec succès.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Échec import: '.$e->getMessage());
        }
    }
}
