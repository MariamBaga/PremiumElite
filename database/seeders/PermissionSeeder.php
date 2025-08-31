<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions des coordinateurs
        $coordPerms = ['coordinators.view','coordinators.create','coordinators.update','coordinators.delete'];
        foreach ($coordPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $perms = [
            'dossiers.view', 'dossiers.create', 'dossiers.update', 'dossiers.delete',
            'dossiers.assign', 'dossiers.update-status', 'dossiers.add-contact', 'dossiers.add-intervention'
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $roles = [
            'admin', 'superviseur', 'technicien', 'commercial', 'superadmin'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'admin')->first();
        $super = Role::where('name', 'superviseur')->first();
        $tech = Role::where('name', 'technicien')->first();
        $com = Role::where('name', 'commercial')->first();
        $superadmin = Role::where('name', 'superadmin')->first();

        // Attribution des permissions
        $admin->givePermissionTo($perms);
        $super->givePermissionTo($perms);
        $com->givePermissionTo(['dossiers.view', 'dossiers.create']);
        $tech->givePermissionTo(['dossiers.view', 'dossiers.update', 'dossiers.update-status', 'dossiers.add-contact', 'dossiers.add-intervention']);

        // Permissions clients
        $clientPerms = ['clients.view', 'clients.create', 'clients.update', 'clients.delete'];
        foreach ($clientPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin->givePermissionTo($clientPerms);
        $super->givePermissionTo(['clients.view', 'clients.create', 'clients.update']);
        $com->givePermissionTo(['clients.view', 'clients.create']);
        $superadmin->givePermissionTo($coordPerms);

        // Permissions équipes
        $teamPerms = [
            'teams.view','teams.create','teams.update','teams.delete',
            'teams.restore','teams.force-delete',
            'teams.assign-lead','teams.manage-members'
        ];
        foreach ($teamPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin->givePermissionTo($teamPerms);
        $super->givePermissionTo(['teams.view','teams.create','teams.update','teams.assign-lead','teams.manage-members']);
        $tech->givePermissionTo(['teams.view']);
        $com->givePermissionTo([]);

        // Permissions extensions
        $extPerms = ['extensions.view','extensions.create','extensions.update','extensions.delete'];
        foreach ($extPerms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin->givePermissionTo($extPerms);
        $super->givePermissionTo($extPerms);
        $tech->givePermissionTo(['extensions.view']);
        $com->givePermissionTo(['extensions.view']);

        // Superadmin a toutes les permissions
        $allPermissions = Permission::all();
        $superadmin->givePermissionTo($allPermissions);
    }
}
