<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    protected $fillable = [
        'code','zone','statut','foyers_cibles','roi_estime','geom'
    ];

    protected $casts = [
        'foyers_cibles' => 'integer',
        'roi_estime'    => 'decimal:2',
        'geom'          => 'array', // GeoJSON stocké en JSON
    ];

    // Accesseur pratique pour le statut lisible
    public static function statuts(): array
    {
        return [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine'  => 'Terminé',
        ];
    }
}
