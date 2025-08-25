<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'dossiers.view','dossiers.create','dossiers.update','dossiers.delete',
            'dossiers.assign','dossiers.update-status',
            'dossiers.add-contact','dossiers.add-intervention'
        ];
        foreach ($perms as $p) Permission::findOrCreate($p);

        $admin = Role::findOrCreate('admin');
        $super = Role::findOrCreate('superviseur');
        $tech  = Role::findOrCreate('technicien');
        $com   = Role::findOrCreate('commercial');

        $admin->givePermissionTo($perms);
        $super->givePermissionTo($perms);
        $com->givePermissionTo(['dossiers.view','dossiers.create']);
        $tech->givePermissionTo(['dossiers.view','dossiers.update','dossiers.update-status','dossiers.add-contact','dossiers.add-intervention']);


        // dans PermissionSeeder
foreach (['clients.view','clients.create','clients.update','clients.delete'] as $p) {
    Permission::findOrCreate($p);
}
$admin->givePermissionTo(['clients.view','clients.create','clients.update','clients.delete']);
$super->givePermissionTo(['clients.view','clients.create','clients.update']);
$com->givePermissionTo(['clients.view','clients.create']);
// technicien: pas de droits clients par défaut

    }
}
