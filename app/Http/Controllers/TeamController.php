<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DossierRaccordement;
use Illuminate\Support\Facades\Hash;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // tu peux aussi chaîner permission:teams.view etc. sur chaque méthode via les routes
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $q = Team::query()
            ->with(['lead'])
            ->when($request->filled('only_trashed'), fn($qr) => $qr->onlyTrashed())
            ->when($request->filled('search'), function($qr) use ($request){
                $s = '%'.$request->search.'%';
                $qr->where(function($sub) use ($s) {
                    $sub->where('name','like',$s)
                        ->orWhere('zone','like',$s);
                });
            });

        // 🔒 Restriction : si ce n’est pas un superadmin ou coordinateur
        if (!$user->hasAnyRole(['superadmin','coordinateur'])) {
            $q->where(function($qr) use ($user) {
                $qr->where('lead_id', $user->id) // chef d'équipe
                   ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id)); // membre
            });
        }

        $q->orderBy('name');

        return view('teams.index', [
            'teams' => $q->paginate(15)->withQueryString()
        ]);
    }


    public function create()
    {
        $this->authorize('create', Team::class);
        $users = User::role('chef_equipe')->orderBy('name')->get();

        $dossiers = DossierRaccordement::with('client')
            ->whereNull('assigned_team_id')
            ->where('statut', '!=', 'en_appel') // <-- empêche les dossiers en appel
            ->latest()->limit(200)->get(); // limite pour éviter des listes énormes
        return view('teams.create', compact('users','dossiers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Team::class);

        $data = $request->validate([
            'name' => 'required|string|max:100|unique:teams,name',
            'zone' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'members_names' => 'nullable|string',
            'lead_id' => 'nullable|exists:users,id',
            'dossier_ids' => 'array',
'dossier_ids.*' => 'integer|exists:dossiers_raccordement,id',

        ]);

        $team = Team::create([
            'name' => $data['name'],
            'zone' => $data['zone'],
            'description' => $data['description'],
        ]);

        // Ajouter les membres en texte
        if (!empty($data['members_names'])) {
            $names = preg_split("/[\r\n,]+/", $data['members_names']);
            $team->members_names = json_encode(array_map('trim', $names)); // stocké en JSON
            $team->save();
        }

        // Affecter le chef d'équipe
        if (!empty($data['lead_id'])) {
            $team->setLeader(User::find($data['lead_id']));
        }

        // 🔗 Assignation des dossiers sélectionnés
        if (!empty($data['dossier_ids'])) {
            // On ne touche qu’aux dossiers non assignés (évite d’écraser une autre équipe par erreur)
            DossierRaccordement::whereIn('id', $data['dossier_ids'])
                ->whereNull('assigned_team_id')
                ->where('statut', '!=', 'en_appel')
                ->update(['assigned_team_id' => $team->id]);
        }

        return redirect()->route('teams.show', $team)->with('success','Équipe créée et dossiers assignés.');
    }


    public function show(Team $team)
    {

    $user = auth()->user();

    // 🔒 Vérification des droits
    if (!$user->hasAnyRole(['superadmin', 'coordinateur'])) {
        if ($team->lead_id !== $user->id && !$team->members->contains($user->id)) {
            abort(403, "Accès refusé");
        }
    }
        $team->load(['lead','members' => fn($q)=>$q->orderBy('name')]);
        return view('teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        $this->authorize('update', $team);
        $users = User::role('chef_equipe')->orderBy('name')->get();


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
            'members_names' => 'nullable|string',
            'lead_id'     => 'nullable|exists:users,id',
            'dossier_ids' => 'array',
            'dossier_ids.*' => 'integer|exists:dossiers_raccordement,id',
        ]);

        $team->update(collect($data)->only('name','zone','description')->toArray());
        // Mise à jour des membres en texte libre
if (!empty($data['members_names'])) {
    $names = preg_split("/[\r\n,]+/", $data['members_names']);
    $team->members_names = json_encode(array_map('trim', $names));
} else {
    $team->members_names = null;
}
$team->save();

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
            DossierRaccordement::whereIn('id', $toAttach)
                ->where('statut', '!=', 'en_appel') // <-- empêche les dossiers en appel
                ->update(['assigned_team_id' => $team->id]);
        }


        return redirect()->route('teams.show',$team)->with('success','Équipe mise à jour et dossiers synchronisés.');
    }


    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);
        $team->forceDelete(); // suppression définitive
        return redirect()->route('teams.index')->with('success','Équipe supprimée.');
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




     /**
     * Créer un nouvel utilisateur + l’ajouter à l’équipe
     * (pratique pour créer rapidement un technicien)
     */
    public function createAndAddMember(Request $request, Team $team)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'role'     => 'nullable|string' // si Spatie
        ]);

        $password = $data['password'] ?? 'password123'; // valeur par défaut
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($password),
        ]);

        // Spatie (optionnel)
        if (!empty($data['role']) && method_exists($user, 'assignRole')) {
            $user->assignRole($data['role']);
        }

        $team->members()->attach($user->id);

        return back()->with('success',"Utilisateur créé et ajouté à l’équipe.");
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
        $this->authorize('update', $team);

        $request->validate([
            'name' => 'required|string|max:120'
        ]);

        // Récupérer les membres actuels
        $members = $team->members_names ? json_decode($team->members_names, true) : [];

        // Ajouter le nouveau membre
        $members[] = $request->name;

        // Sauvegarder
        $team->members_names = json_encode($members);
        $team->save();

        return back()->with('success', 'Membre ajouté à l’équipe.');
    }


    public function removeMember(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $request->validate([
            'name' => 'required|string'
        ]);

        // Récupérer les membres actuels
        $members = $team->members_names ? json_decode($team->members_names, true) : [];

        // Retirer le membre
        $members = array_filter($members, fn($m) => $m !== $request->name);

        // Réindexer et sauvegarder
        $team->members_names = json_encode(array_values($members));
        $team->save();

        return back()->with('success', 'Membre retiré de l’équipe.');
    }

    /**
 * Retirer un dossier assigné de l'équipe.
 */
public function removeDossier(Team $team, DossierRaccordement $dossier)
{
    $this->authorize('update', $team);

    // Vérifier que le dossier appartient bien à cette équipe
    if ($dossier->assigned_team_id !== $team->id) {
        return back()->with('error', 'Ce dossier n’est pas assigné à cette équipe.');
    }

    // Désassigner le dossier
    $dossier->assigned_team_id = null;
    $dossier->save();

    return back()->with('success', "Dossier '{$dossier->reference}' retiré de l’équipe.");
}


}
