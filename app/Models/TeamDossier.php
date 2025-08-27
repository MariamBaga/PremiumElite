<?php

// app/Models/TeamDossier.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamDossier extends Model
{
    protected $fillable = ['team_id','dossier_id','etat','motif','date_report','updated_by'];

    protected $casts = [
        'date_report' => 'datetime',
    ];

    public function team(){ return $this->belongsTo(Team::class); }
    public function dossier(){ return $this->belongsTo(DossierRaccordement::class,'dossier_id'); }
    public function updater(){ return $this->belongsTo(User::class, 'updated_by'); }

    // Portées utiles
    public function scopeActifs($q){ return $q->whereIn('etat',['en_cours','contrainte','reporte']); }
    public function scopeClotures($q){ return $q->where('etat','cloture'); }
}
