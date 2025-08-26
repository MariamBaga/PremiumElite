<?php

// app/Policies/TeamPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Team;

class TeamPolicy
{
    public function viewAny(User $user): bool { return $user->can('teams.view'); }
    public function view(User $user, Team $team): bool { return $user->can('teams.view'); }
    public function create(User $user): bool { return $user->can('teams.create'); }
    public function update(User $user, Team $team): bool { return $user->can('teams.update'); }
    public function delete(User $user, Team $team): bool { return $user->can('teams.delete'); }
    public function restore(User $user, Team $team): bool { return $user->can('teams.restore'); }
    public function forceDelete(User $user, Team $team): bool { return $user->can('teams.force-delete'); }
}
