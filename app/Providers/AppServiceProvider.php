<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\DossierRaccordement;
use App\Policies\DossierRaccordementPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Lier explicitement la policy au modèle
        Gate::policy(DossierRaccordement::class, DossierRaccordementPolicy::class);

        // (Optionnel) Laisser toujours passer les admins
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
