<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role(
            ['admin','superviseur','technicien','commercial','superadmin','chef_equipe','coordinateur','client'],
            'web' // préciser le guard
        );

        if ($request->filled('role')) {
            $query->role($request->role, 'web'); // préciser le guard ici aussi
        }

        $users = $query->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6|confirmed',
            'role'     => 'required|string|exists:roles,name',
        ], [
            'name.required'     => 'Le nom est obligatoire.',
            'name.max'          => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required'    => 'L’email est obligatoire.',
            'email.email'       => 'L’email doit être valide.',
            'email.unique'      => 'Cet email est déjà utilisé.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed'=> 'Le mot de passe et sa confirmation ne correspondent pas.',
            'role.required'     => 'Le rôle est obligatoire.',
            'role.exists'       => 'Le rôle choisi est invalide.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password ? Hash::make($request->password) : null,
        ]);

        // Attribution du rôle choisi
        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès !');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role'     => 'required|string|exists:roles,name',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // Réassigner les rôles
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour !');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé !');
    }
}
