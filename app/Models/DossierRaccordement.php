<?php

namespace App\Models;

use App\Enums\StatutDossier;
use Illuminate\Database\Eloquent\Model;

class DossierRaccordement extends Model
{
    protected $table = 'dossiers_raccordement';

    protected $fillable = [
        // existants
        'client_id','reference','type_service','pbo','pm','statut','assigned_team_id',
        'description','tags','assigned_to','date_planifiee','date_realisation','pieces_jointes',
        'nature','rapport_installation','msan','fat','port','port_disponible','type_cable',
        'lineaire_m','puissance_fat_dbm','puissance_pto_dbm',
        'zone', 'rapport_intervention',
        'raison_non_activation', // au cas où

        // 🆕 nouvelles colonnes (migration ALTER)
        'ligne','contact','service_acces','localite','categorie',
        'date_reception_raccordement','date_fin_travaux',
        'pbo_lineaire_utilise','nb_poteaux_implantes','nb_armements_poteaux',
        'taux_reporting_j1','is_active','observation','pilote_raccordement',
        'rendez_vous_at','rendez_vous_notified_at',
        'action_injoignable','raison_non_activation',
        'rapport_pbo_path','rapport_zone_path','rapport_activation_path',
        'fiche_client_path','rapport_intervention_path','satisfaction_client_path',


    // 🔹 nouvelles colonnes pour Dépassement et Implantation
    'depassement_distance',
    'depassement_gps_abonne',
    'depassement_gps_pbo',
    'depassement_nom_pbo',
    'implantation_gps_abonne',
    'implantation_gps_fat',
    'capture_message',
    ];

    protected $casts = [
        // Enum
        'statut' => StatutDossier::class,

        // JSON
        'tags'                 => 'array',
        'rapport_installation' => 'array',
        'pieces_jointes'       => 'array',

        // Datetime (avec heures)
        'date_planifiee'          => 'datetime',
        'date_realisation'        => 'datetime',
        'rendez_vous_at'          => 'datetime',
        'rendez_vous_notified_at' => 'datetime',

        // Date (jour)
        'date_reception_raccordement' => 'date',
        'date_fin_travaux'            => 'date',

        // Numériques / bool
        'nb_poteaux_implantes'  => 'integer',
        'nb_armements_poteaux'  => 'integer',
        'is_active'             => 'boolean',
    ];

    // Relations
    public function client()       { return $this->belongsTo(Client::class); }
    public function technicien()   { return $this->belongsTo(User::class, 'assigned_to'); }
    public function tentatives()   { return $this->hasMany(TentativeContact::class, 'dossier_id'); }
    public function interventions(){ return $this->hasMany(Intervention::class, 'dossier_id'); }
    public function statuts()      { return $this->hasMany(DossierStatusHistory::class, 'dossier_id'); }
    public function team()         { return $this->belongsTo(Team::class, 'assigned_team_id'); }
    public function assignedTeam() { return $this->belongsTo(Team::class, 'assigned_team_id'); }

    // Pratique pour l’affichage Blade
    public function getStatutLabelAttribute(): ?string
    {
        if (!$this->statut) return null;
        $labels = \App\Enums\StatutDossier::labels();
        // $this->statut est un enum → $this->statut->value donne la clé string
        return $labels[$this->statut->value] ?? $this->statut->value;
    }

    public function isModifiable(): bool
    {
        return !in_array($this->statut?->value, ['active', 'realise']);
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
