<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\DossierRaccordement;
use App\Policies\DossierRaccordementPolicy;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\HeadingRowFormatter;
use Illuminate\Support\Facades\View;

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

 // Injecter le compteur RDV dans toutes les vues
 View::composer('adminlte::partials.navbar.navbar', function ($view) {
    $user = auth()->user();

    $rdvAlertCount = DossierRaccordement::whereNotNull('date_planifiee')
        ->whereDate('date_planifiee', '>=', now()->format('Y-m-d'))
        ->whereDate('date_planifiee', '<=', now()->addDay()->format('Y-m-d'))
        ->when($user->hasRole('chef_equipe'), function($q) use ($user) {
            $teamId = \App\Models\Team::where('lead_id', $user->id)->value('id');
            $q->where('assigned_team_id', $teamId);
        })
        ->count();

    $view->with('rdvAlertCount', $rdvAlertCount);
});
    }
}
