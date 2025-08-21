<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentativeContact extends Model
{
    protected $table = 'tentatives_contact';
    protected $fillable = ['dossier_id','user_id','methode','resultat','notes','effectuee_le'];
    protected $casts = ['effectuee_le' => 'datetime'];

    public function dossier(){ return $this->belongsTo(DossierRaccordement::class,'dossier_id'); }
    public function user()   { return $this->belongsTo(User::class); }
}
