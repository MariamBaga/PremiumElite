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
        $query = DossierRaccordement::with(['client', 'team'])
            ->whereBetween('created_at', [$this->request->date_from, $this->request->date_to])
            ->orderBy('created_at', 'desc');

        // Support multi-statut
        $selectedStatuses = [];
        if ($this->request->filled('statut')) {
            $query->whereIn('statut', $this->request->statut);
            $selectedStatuses = $this->request->statut;
        }

        // Filtre par équipe
        if ($this->request->filled('team_id')) {
            $query->where('assigned_team_id', $this->request->team_id);
        }

        $dossiers = $query->get();

        return view('rapports.export', compact('dossiers', 'selectedStatuses'));
    }
}
