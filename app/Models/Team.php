<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','zone','description','lead_id', 'members_names'];
    protected $casts = [
        'members_names' => 'array',   // <— important
    ];


    // Chef d’équipe (User)
    public function lead()
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    // Membres
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('is_lead')
            ->withTimestamps();
    }

    // Dossiers (si tu as ajouté assigned_team_id)
    public function dossiers()
    {
        return $this->hasMany(DossierRaccordement::class, 'assigned_team_id');
    }

    // app/Models/Team.php
public function teamDossiers(){ return $this->hasMany(TeamDossier::class); }

// app/Models/DossierRaccordement.php
public function teamItem(){ return $this->hasOne(TeamDossier::class, 'dossier_id'); }


    /* ---------- Logique chef d’équipe ---------- */

    // Définit un chef (en forçant l’unicité)
    public function setLeader(User $user): void
    {
        // s’assure qu’il est membre
        if (! $this->members()->where('users.id', $user->id)->exists()) {
            $this->members()->attach($user->id, ['is_lead' => true]);
        }

        // remet tous les autres à false
        $this->members()->updateExistingPivot(
            $this->members()->pluck('users.id')->all(), ['is_lead' => false]
        );

        // ce user devient lead
        $this->members()->updateExistingPivot($user->id, ['is_lead' => true]);

        // enregistre sur la colonne lead_id pour requêtes rapides
        $this->update(['lead_id' => $user->id]);
    }

    // Promote / demote helpers
    public function promote(User $user): void
    {
        $this->setLeader($user);
    }
    public function demoteLeader(): void
    {
        $this->members()->updateExistingPivot(
            $this->members()->pluck('users.id')->all(), ['is_lead' => false]
        );
        $this->update(['lead_id' => null]);
    }
}

