<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'type','nom','prenom','raison_sociale','telephone','email',
        'adresse_ligne1','adresse_ligne2','ville','zone',
        'numero_ligne','numero_point_focal','localisation',
        'date_paiement','date_affectation',
        'latitude','longitude','metadonnees'
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude'=> 'decimal:7',
        'metadonnees' => 'array',
        'date_paiement' => 'date',
        'date_affectation' => 'date',
    ];


    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                if (($attributes['type'] ?? 'residentiel') === 'professionnel') {
                    return $attributes['raison_sociale'] ?? 'Entreprise';
                }

                $prenom = $attributes['prenom'] ?? '';
                $nom    = $attributes['nom'] ?? '';
                $name   = trim("$prenom $nom");

                return $name !== '' ? $name : 'Client';
            });
        }


           // 🔑 Relation manquante
    public function dossiers()
    {
        return $this->hasMany(DossierRaccordement::class, 'client_id');
    }
}
