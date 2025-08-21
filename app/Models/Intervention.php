<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    protected $fillable = ['dossier_id','technicien_id','debut','fin','etat','observations','metriques'];
    protected $casts = ['debut'=>'datetime','fin'=>'datetime','metriques'=>'array'];

    public function dossier(){ return $this->belongsTo(DossierRaccordement::class,'dossier_id'); }
    public function technicien(){ return $this->belongsTo(User::class,'technicien_id'); }
}
