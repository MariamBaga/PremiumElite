<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\DossierRaccordement;
use App\Policies\DossierRaccordementPolicy;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\HeadingRowFormatter;

use App\Models\Team;
use App\Policies\TeamPolicy;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {



        
        if (class_exists(HeadingRowFormatter::class)) {
            HeadingRowFormatter::default('none');
        }

        // Lier explicitement la policy au modèle
        Gate::policy(DossierRaccordement::class, DossierRaccordementPolicy::class);

        // (Optionnel) Laisser toujours passer les admins
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });






        Gate::policy(Team::class, TeamPolicy::class);

        // Admin bypass (si Spatie est en place et le role admin existe)
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'hasRole') && $user->hasRole('admin') ? true : null;
        });
    }
}
