<?php

// app/Http/Controllers/TeamInboxController.php
namespace App\Http\Controllers;

use App\Models\{Team, TeamDossier, DossierRaccordement};
use Illuminate\Http\Request;

class TeamInboxController extends Controller
{
    public function index(Team $team, Request $r)
    {
        $user = auth()->user();



        $items = TeamDossier::with(['dossier.client'])
            ->where('team_id', $team->id)
            ->actifs()
            ->latest()->paginate(15);

        return view('teams.inbox', compact('team', 'items'));
    }



    // 1) Clôturer : installation OK et client activé → on met etat=cloture + on peut aussi basculer le statut dossier si tu le souhaites
    public function close(Team $team, DossierRaccordement $dossier, Request $r)
    {

        $user = auth()->user();
        
        $item = TeamDossier::where('team_id',$team->id)->where('dossier_id',$dossier->id)->firstOrFail();
        $item->update([
            'etat' => 'cloture',
            'motif' => $r->input('motif'),      // commentaire libre optionnel
            'updated_by' => auth()->id(),
            'date_report' => null,
        ]);

        // Optionnel : mettre le dossier en “realise”
        // $dossier->update(['statut'=>'realise','date_realisation'=>now()]);

        return back()->with('success','Dossier clôturé par l’équipe.');
    }

    // 2) Contrainte : préciser la raison
    public function constraint(Team $team, DossierRaccordement $dossier, Request $r)
    {

        $user = auth()->user();
        
        $data = $r->validate(['motif'=>'required|string|max:500']);
        $item = TeamDossier::where('team_id',$team->id)->where('dossier_id',$dossier->id)->firstOrFail();

        $item->update([
            'etat' => 'contrainte',
            'motif'=> $data['motif'],
            'date_report'=> null,
            'updated_by'=> auth()->id(),
        ]);

        // Optionnel : toucher le statut métier du dossier :
        // $dossier->update(['statut'=>'pbo_sature' /* ou autre selon le motif */]);

        return back()->with('success','Contrainte enregistrée.');
    }

    // 3) Report : nouvelle date
    public function reschedule(Team $team, DossierRaccordement $dossier, Request $r)
    {

        $user = auth()->user();
        
        $data = $r->validate([
            'date_report' => 'required|date',
            'motif'       => 'nullable|string|max:500',
        ]);

        $item = TeamDossier::where('team_id',$team->id)->where('dossier_id',$dossier->id)->firstOrFail();

        $item->update([
            'etat' => 'reporte',
            'date_report' => $data['date_report'],
            'motif' => $data['motif'],
            'updated_by' => auth()->id(),
        ]);

        // Optionnel : mettre aussi date_planifiee du dossier
        // $dossier->update(['date_planifiee'=>$data['date_report']]);

        return back()->with('success','Dossier replanifié.');
    }
}
