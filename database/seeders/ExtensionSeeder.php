<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Extension;

class ExtensionSeeder extends Seeder
{
    public function run(): void
    {
        Extension::updateOrCreate(['code'=>'EXT-2025-001'], [
            'zone'          => 'Bamako - ACI',
            'statut'        => 'en_cours',
            'foyers_cibles' => 120,
            'roi_estime'    => 2500000,
            'geom'          => [
                "type" => "LineString",
                "coordinates" => [[-7.95,12.65],[-7.96,12.66],[-7.97,12.661]]
            ]
        ]);

        Extension::updateOrCreate(['code'=>'EXT-2025-002'], [
            'zone'          => 'Kalaban',
            'statut'        => 'planifie',
            'foyers_cibles' => 80,
            'geom'          => null,
        ]);
    }
}
