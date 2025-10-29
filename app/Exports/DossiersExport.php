<?php

namespace App\Exports;

use App\Models\DossierRaccordement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DossiersExport implements FromCollection, WithHeadings
{
    protected $statut;
    protected $teamId;

    public function __construct($statut = null, $teamId = null)
    {
        $this->statut = $statut;
        $this->teamId = $teamId;
    }

    public function collection()
    {
        $query = DossierRaccordement::with('client')
            ->when($this->statut, fn($q) => $q->where('statut', $this->statut))
            ->when($this->teamId, fn($q) => $q->where('assigned_team_id', $this->teamId))
            ->get();

        return $query->map(function ($dossier) {
            return [
                'Client' => $dossier->client->displayName ?? '-',
                'Téléphone' => $dossier->client->telephone ?? '-',
                'Statut' => $dossier->statut instanceof \App\Enums\StatutDossier ? $dossier->statut->value : $dossier->statut,
                'Date RDV' => $dossier->date_planifiee,
                'Port' => $dossier->port,
                'Linéaire' => $dossier->lineaire_m,
                'Type de câble' => $dossier->type_cable,
                'Localité' => $dossier->localite ?? '-',
            ];
        });
    }


    public function headings(): array
    {
        return [
            'Client', 'Téléphone', 'Statut', 'Date RDV', 'Port', 'Linéaire', 'Type de câble', 'Localité'
        ];
    }
}
