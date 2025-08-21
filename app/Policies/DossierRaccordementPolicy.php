<?php

namespace App\Policies;

use App\Models\DossierRaccordement;
use App\Models\User;

class DossierRaccordementPolicy
{
    public function viewAny(User $user): bool {
        return $user->can('dossiers.view');
    }
    public function view(User $user, DossierRaccordement $d): bool {
        return $user->can('dossiers.view') || $user->id === $d->assigned_to;
    }
    public function create(User $user): bool {
        return $user->can('dossiers.create');
    }
    public function update(User $user, DossierRaccordement $d): bool {
        return $user->can('dossiers.update') || $user->id === $d->assigned_to;
    }
    public function updateStatus(User $user, DossierRaccordement $d): bool {
        return $user->can('dossiers.update-status') || $user->id === $d->assigned_to;
    }
    public function assign(User $user): bool {
        return $user->can('dossiers.assign');
    }
    public function delete(User $user, DossierRaccordement $d): bool {
        return $user->can('dossiers.delete');
    }
}
