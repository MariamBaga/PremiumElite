<?php

// app/Http/Controllers/TicketController.php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Client;
use App\Models\DossierRaccordement;
use App\Models\Team;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $r)
    {
        $q = Ticket::with(['client','dossier','team'])
            ->when($r->filled('statut'), fn($x)=>$x->where('statut',$r->statut))
            ->when($r->filled('priorite'), fn($x)=>$x->where('priorite',$r->priorite))
            ->latest();
        return view('tickets.index', ['tickets'=>$q->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('tickets.create', [
            'clients' => Client::orderBy('id','desc')->limit(200)->get(),
            'dossiers'=> DossierRaccordement::orderBy('id','desc')->limit(200)->get(),
            'teams'   => Team::orderBy('name')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'client_id'        =>'nullable|exists:clients,id',
            'dossier_id'       =>'nullable|exists:dossiers_raccordement,id',
            'assigned_team_id' =>'nullable|exists:teams,id',
            'type'             =>'required|in:panne,signalement,maintenance',
            'priorite'         =>'required|in:faible,normal,haute,critique',
            'titre'            =>'required|string|max:180',
            'description'      =>'nullable|string',
        ]);
        $data['opened_by'] = auth()->id();
        $ticket = Ticket::create($data);
        return redirect()->route('tickets.show',$ticket)->with('success','Ticket créé');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['client','dossier','team','comments.user']);
        return view('tickets.show', compact('ticket'));
    }

    public function update(Request $r, Ticket $ticket)
    {
        $data = $r->validate([
            'statut'           =>'nullable|in:ouvert,en_cours,resolu,ferme',
            'assigned_team_id' =>'nullable|exists:teams,id',
            'priorite'         =>'nullable|in:faible,normal,haute,critique',
            'date_resolution'  =>'nullable|date',
        ]);
        $ticket->update($data);
        return back()->with('success','Ticket mis à jour');
    }





}
