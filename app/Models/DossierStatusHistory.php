<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierStatusHistory extends Model
{
    protected $table = 'dossier_status_history';
    protected $fillable = ['dossier_id','ancien_statut','nouveau_statut','user_id','commentaire'];

    public function dossier(){ return $this->belongsTo(DossierRaccordement::class, 'dossier_id'); }
    public function user()   { return $this->belongsTo(User::class); }
}
