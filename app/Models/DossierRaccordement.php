<?php

namespace App\Models;

use App\Enums\StatutDossier;
use Illuminate\Database\Eloquent\Model;
use App\Models\Team;

class DossierRaccordement extends Model
{

    protected $table = 'dossiers_raccordement';

    protected $fillable = [
        'client_id','reference','type_service','pbo','pm','statut','assigned_team_id',
        'description','tags','assigned_to','date_planifiee','date_realisation','pieces_jointes'
        // ...
 , 'nature',
  'rapport_installation','msan','fat','port','port_disponible','type_cable',
  'lineaire_m','puissance_fat_dbm','puissance_pto_dbm'
    ];

    protected $casts = [
        'statut'           => StatutDossier::class,
        'tags'             => 'array',
        'rapport_installation' => 'array',
        'pieces_jointes'   => 'array',
        'date_planifiee'   => 'datetime',
        'date_realisation' => 'datetime',
    ];

    public function client()       { return $this->belongsTo(Client::class); }
    public function technicien()   { return $this->belongsTo(User::class, 'assigned_to'); }
    public function tentatives()   { return $this->hasMany(TentativeContact::class, 'dossier_id'); }
    public function interventions(){ return $this->hasMany(Intervention::class, 'dossier_id'); }
    public function statuts()      { return $this->hasMany(DossierStatusHistory::class, 'dossier_id'); }
    public function team()
{
    return $this->belongsTo(Team::class, 'assigned_team_id');
}




    protected static function booted(): void
    {
        static::creating(function(self $dossier){
            if (empty($dossier->reference)) {
                $seq = str_pad((string)((self::max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT);
                $dossier->reference = 'DR-'.date('Y').'-'.$seq;
            }
        });

        static::updating(function(self $dossier){
            if ($dossier->isDirty('statut')) {
                $dossier->statuts()->create([
                    'ancien_statut'  => $dossier->getOriginal('statut')?->value ?? null,
                    'nouveau_statut' => $dossier->statut->value,
                    'user_id'        => auth()->id(),
                    'commentaire'    => request('commentaire_statut')
                ]);
                if ($dossier->statut === StatutDossier::REALISE && is_null($dossier->date_realisation)) {
                    $dossier->date_realisation = now();
                }
            }
        });
    }
}
