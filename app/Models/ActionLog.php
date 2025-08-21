<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    protected $fillable = [
        'subject_type','subject_id','dossier_id','causer_id','action','properties','ip','user_agent'
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function subject()   { return $this->morphTo(); }
    public function dossier()   { return $this->belongsTo(DossierRaccordement::class, 'dossier_id'); }
    public function causer()    { return $this->belongsTo(User::class, 'causer_id'); }
}
