<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // On récupère (ou crée si besoin) les rôles de ton PermissionSeeder
        $admin       = Role::firstOrCreate(['name' => 'admin']);
        $superadmin       = Role::firstOrCreate(['name' => 'superadmin']);
        $superviseur = Role::firstOrCreate(['name' => 'superviseur']);
        $technicien  = Role::firstOrCreate(['name' => 'technicien']);
        $commercial  = Role::firstOrCreate(['name' => 'commercial']);

        $users = [
            ['name' => 'Admin Test',       'email' => 'admin@test.com',       'role' => $admin],
            ['name' => 'Superviseur Test', 'email' => 'superviseur@test.com', 'role' => $superviseur],
            ['name' => 'Technicien Test',  'email' => 'technicien@test.com',  'role' => $technicien],
            ['name' => 'Commercial Test',  'email' => 'commercial@test.com',  'role' => $commercial],

               // 👉 Ton utilisateur réel
            ['name' => 'Hurbain Houngnon', 'email' => 'hurbain.houngnon@premiumentreprise.com', 'role' => $superadmin, 'password' => 'Hurbain@2025'],

        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name'     => $u['name'],
                    'password' => Hash::make('password'), // mot de passe: password
                ]
            );
            // (ré)assigne le rôle au cas où
            $user->syncRoles([$u['role']->name]);
        }
    }
}
