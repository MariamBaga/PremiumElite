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
        'adresse_ligne1','adresse_ligne2','ville','zone','latitude','longitude','metadonnees'
    ];

    protected $casts = [
        'metadonnees' => 'array',
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
    ];

    public function dossiers() {
        return $this->hasMany(DossierRaccordement::class);
    }

    public function displayName(): Attribute {
        return Attribute::get(function () {
            return $this->type === 'professionnel'
                ? ($this->raison_sociale ?? 'Entreprise')
                : trim(($this->prenom ?? '').' '.($this->nom ?? 'Client'));
        });
    }
}
