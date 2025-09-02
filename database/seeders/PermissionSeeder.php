<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Team;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------
        // 1) Permissions coordinateurs
        // -------------------------------------------------
        $coordPerms = [
            'coordinators.view','coordinators.create','coordinators.update','coordinators.delete'
        ];
        foreach ($coordPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // -------------------------------------------------
        // 2) Permissions dossiers
        // -------------------------------------------------
        $dossierPerms = [
            'dossiers.view','dossiers.create','dossiers.update','dossiers.delete',
            'dossiers.assign','dossiers.update-status','dossiers.add-contact','dossiers.add-intervention'
        ];
        foreach ($dossierPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // -------------------------------------------------
        // 3) Permissions clients
        // -------------------------------------------------
        $clientPerms = ['clients.view','clients.create','clients.update','clients.delete'];
        foreach ($clientPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // -------------------------------------------------
        // 4) Permissions équipes (onglet équipe)
        // -------------------------------------------------
        $teamPerms = [
            'teams.view','teams.create','teams.update','teams.delete',
            'teams.restore','teams.force-delete','teams.assign-lead','teams.manage-members'
        ];
        foreach ($teamPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // -------------------------------------------------
        // 5) Permissions corbeille d’équipe
        // -------------------------------------------------
        $inboxPerms = [
            'teams.inbox.view',
            'teams.inbox.close',
            'teams.inbox.constraint',
            'teams.inbox.reschedule'
        ];
        foreach ($inboxPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // -------------------------------------------------
        // 6) Permissions extensions
        // -------------------------------------------------
        $extPerms = ['extensions.view','extensions.create','extensions.update','extensions.delete'];
        foreach ($extPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // -------------------------------------------------
        // 7) Création des rôles
        // -------------------------------------------------
        $roles = ['admin', 'superviseur', 'technicien', 'commercial', 'superadmin', 'chef_equipe', 'coordinateur'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $admin       = Role::where('name','admin')->first();
        $super       = Role::where('name','superviseur')->first();
        $tech        = Role::where('name','technicien')->first();
        $com         = Role::where('name','commercial')->first();
        $superadmin  = Role::where('name','superadmin')->first();
        $chefEquipe  = Role::where('name','chef_equipe')->first();
        $coord       = Role::where('name','coordinateur')->first();

        // -------------------------------------------------
        // 8) Attribution des permissions
        // -------------------------------------------------
        // Admin
        $admin->givePermissionTo(array_merge($dossierPerms, $clientPerms, $teamPerms, $extPerms, $inboxPerms));

        // Superviseur
        $super->givePermissionTo(array_merge($dossierPerms, ['clients.view','clients.create','clients.update'], $teamPerms, $extPerms, $inboxPerms));

        // Technicien
        $tech->givePermissionTo([
            'dossiers.view','dossiers.update','dossiers.update-status','dossiers.add-contact','dossiers.add-intervention',
            'teams.view',
            'extensions.view'
        ]);

        // Commercial
        $com->givePermissionTo([
            'dossiers.view','dossiers.create',
            'clients.view','clients.create',
            'teams.view',
            'extensions.view'
        ]);

        // Chef d'équipe
        $chefEquipe->givePermissionTo([
            'dossiers.view','dossiers.update','dossiers.update-status',
            'teams.view','teams.manage-members',
            'clients.view',
            'extensions.view'
        ]);

        // Coordinateur
        $coord->givePermissionTo(array_merge($dossierPerms, ['clients.view','clients.create','clients.update'], $teamPerms, $extPerms, $inboxPerms));


        
        // Superadmin → toutes les permissions
        $superadmin->givePermissionTo(Permission::all());
    }
}
