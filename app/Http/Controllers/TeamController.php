<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DossierRaccordement;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // tu peux aussi chaîner permission:teams.view etc. sur chaque méthode via les routes
    }

    public function index(Request $request)
    {
        $q = Team::query()
            ->with(['lead'])
            ->when($request->filled('only_trashed'), fn($qr) => $qr->onlyTrashed())
            ->when($request->filled('search'), function($qr) use ($request){
                $s = '%'.$request->search.'%';
                $qr->where('name','like',$s)->orWhere('zone','like',$s);
            })
            ->orderBy('name');

        return view('teams.index', ['teams'=>$q->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        $this->authorize('create', Team::class);
        $users = User::orderBy('name')->get();
        $dossiers = DossierRaccordement::with('client')
            ->whereNull('assigned_team_id')
            ->latest()->limit(200)->get(); // limite pour éviter des listes énormes
        return view('teams.create', compact('users','dossiers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Team::class);

        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:teams,name',
            'zone'        => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'members'     => 'array',
            'members.*'   => 'exists:users,id',
            'lead_id'     => 'nullable|exists:users,id',
            'dossier_ids' => 'array',
            'dossier_ids.*' => 'integer|exists:dossiers_raccordement,id',
        ]);

        $team = Team::create(collect($data)->only('name','zone','description')->toArray());

        if (!empty($data['members'])) {
            $team->members()->attach($data['members']);
        }
        if (!empty($data['lead_id'])) {
            $team->setLeader(User::find($data['lead_id']));
        }

        // 🔗 Assignation des dossiers sélectionnés
        if (!empty($data['dossier_ids'])) {
            // On ne touche qu’aux dossiers non assignés (évite d’écraser une autre équipe par erreur)
            DossierRaccordement::whereIn('id', $data['dossier_ids'])
                ->whereNull('assigned_team_id')
                ->update(['assigned_team_id' => $team->id]);
        }

        return redirect()->route('teams.show', $team)->with('success','Équipe créée et dossiers assignés.');
    }


    public function show(Team $team)
    {
        $team->load(['lead','members' => fn($q)=>$q->orderBy('name')]);
        return view('teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        $this->authorize('update', $team);
        $users = User::orderBy('name')->get();

        $dossiers = DossierRaccordement::with('client')
            ->where(function($q) use ($team){
                $q->whereNull('assigned_team_id')
                  ->orWhere('assigned_team_id', $team->id);
            })
            ->latest()->limit(300)->get();

        $team->load('members','dossiers'); // pour pré-sélectionner
        return view('teams.edit', compact('team','users','dossiers'));
    }

    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:teams,name,'.$team->id,
            'zone'        => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'members'     => 'array',
            'members.*'   => 'exists:users,id',
            'lead_id'     => 'nullable|exists:users,id',
            'dossier_ids' => 'array',
            'dossier_ids.*' => 'integer|exists:dossiers_raccordement,id',
        ]);

        $team->update(collect($data)->only('name','zone','description')->toArray());
        $team->members()->sync($data['members'] ?? []);
        if (!empty($data['lead_id'])) {
            $team->setLeader(User::find($data['lead_id']));
        } else {
            $team->demoteLeader();
        }

        // 🔄 Sync dossiers
        $new = collect($data['dossier_ids'] ?? [])->map(fn($v)=>(int)$v)->values();
        $current = $team->dossiers()->pluck('id');

        $toDetach = $current->diff($new); // enlever
        $toAttach = $new->diff($current); // ajouter

        if ($toDetach->isNotEmpty()) {
            DossierRaccordement::whereIn('id', $toDetach)->update(['assigned_team_id' => null]);
        }
        if ($toAttach->isNotEmpty()) {
            // attacher même si un dossier était pris par erreur par une autre équipe ?
            // Ici on force l’assignation à cette équipe :
            DossierRaccordement::whereIn('id', $toAttach)->update(['assigned_team_id' => $team->id]);
            // Si tu veux éviter d’écraser, remplace par ->whereNull('assigned_team_id')...
        }

        return redirect()->route('teams.show',$team)->with('success','Équipe mise à jour et dossiers synchronisés.');
    }


    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);
        $team->delete(); // Soft delete => corbeille
        return redirect()->route('teams.index')->with('success','Équipe mise en corbeille.');
    }

    // ----- Corbeille -----

    public function trash(Request $request)
    {
        $this->authorize('viewAny', Team::class);
        $teams = Team::onlyTrashed()->orderBy('deleted_at','desc')->paginate(15);
        return view('teams.trash', compact('teams'));
    }

    public function restore(int $id)
    {
        $this->authorize('restore', Team::class);
        $team = Team::onlyTrashed()->findOrFail($id);
        $team->restore();
        return redirect()->route('teams.trash')->with('success','Équipe restaurée.');
    }

    public function forceDelete(int $id)
    {
        $this->authorize('forceDelete', Team::class);
        $team = Team::onlyTrashed()->findOrFail($id);
        $team->forceDelete();
        return redirect()->route('teams.trash')->with('success','Équipe supprimée définitivement.');
    }

    // ----- Actions rapides -----

    public function setLead(Team $team, User $user)
    {
        $this->authorize('update', $team); // ou permission:teams.assign-lead
        $team->setLeader($user);
        return back()->with('success','Chef d’équipe mis à jour.');
    }

    public function addMember(Request $request, Team $team)
    {
        $this->authorize('update', $team); // ou permission:teams.manage-members
        $request->validate(['user_id'=>'required|exists:users,id']);
        $team->members()->syncWithoutDetaching([$request->user_id]);
        return back()->with('success','Membre ajouté.');
    }

    public function removeMember(Team $team, User $user)
    {
        $this->authorize('update', $team);
        $team->members()->detach($user->id);
        // si c’était le chef
        if ($team->lead_id === $user->id) {
            $team->demoteLeader();
        }
        return back()->with('success','Membre retiré.');
    }
}
