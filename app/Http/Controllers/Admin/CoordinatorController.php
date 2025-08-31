<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CoordinatorController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role(
            ['admin','superviseur','technicien','commercial','superadmin'],
            'web' // <-- ajouter le guard
        );

        if ($request->filled('role')) {
            $query->role($request->role, 'web'); // préciser le guard ici aussi
        }


        $coordinators = $query->paginate(10);

        return view('admin.coordinators.index', compact('coordinators'));
    }


    public function create()
    {
        $role = Role::firstOrCreate(['name' => 'superviseur']);
        return view('admin.coordinators.create', compact('role'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::firstOrCreate(['name' => 'superviseur']);
        $user->assignRole($role);

        return redirect()->route('admin.coordinators.index')->with('success', 'Coordinateur créé avec succès !');
    }

    public function edit(User $user)
    {
        $role = Role::firstOrCreate(['name' => 'superviseur']);
        return view('admin.coordinators.edit', compact('user', 'role'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string|exists:roles,name', // validation du rôle
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // Supprimer les rôles existants et assigner le nouveau
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.coordinators.index')->with('success', 'Coordinateur mis à jour !');
    }


    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.coordinators.index')->with('success', 'Coordinateur supprimé !');
    }
}
