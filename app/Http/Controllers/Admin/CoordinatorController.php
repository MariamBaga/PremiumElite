<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CoordinatorController extends Controller
{
    public function index()
    {
        $coordinators = User::role('superviseur')->get();
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
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->route('admin.coordinators.index')->with('success', 'Coordinateur mis à jour !');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.coordinators.index')->with('success', 'Coordinateur supprimé !');
    }
}
