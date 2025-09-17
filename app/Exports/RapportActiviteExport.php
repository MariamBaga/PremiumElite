<?php

namespace App\Exports;

use App\Models\DossierRaccordement;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RapportActiviteExport implements FromView
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = DossierRaccordement::with('client')
            ->whereBetween('created_at', [$this->request->date_from, $this->request->date_to])
            ->orderBy('created_at', 'desc');

        if ($this->request->filled('statut')) {
            $query->where('statut', $this->request->statut);
        }

        $dossiers = $query->get();

        return view('rapports.export', compact('dossiers'));
    }
}
