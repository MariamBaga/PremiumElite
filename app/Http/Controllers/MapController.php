<?php

// app/Http/Controllers/MapController.php
namespace App\Http\Controllers;

use App\Models\Plaque;
use App\Models\Extension;
use App\Models\Client;

class MapController extends Controller
{
    public function index() {
        return view('map.index');
    }

    public function data()
    {
        // GeoJSON “FeatureCollection” minimal
        $features = [];

        // Plaques (polygones/lines)
        foreach (Plaque::whereNotNull('geom')->get() as $p) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => $p->geom,
                'properties' => [
                    'layer' => 'plaque',
                    'code'  => $p->code,
                    'nom'   => $p->nom,
                    'statut'=> $p->statut,
                    'zone'  => $p->zone,
                ]
            ];
        }

        // Extensions
        foreach (Extension::whereNotNull('geom')->get() as $e) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => $e->geom,
                'properties' => [
                    'layer' => 'extension',
                    'code'  => $e->code,
                    'statut'=> $e->statut,
                    'zone'  => $e->zone,
                ]
            ];
        }

        // Clients (points si tu as lat/long)
        foreach (Client::whereNotNull('latitude')->whereNotNull('longitude')->limit(1000)->get() as $c) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$c->longitude, (float)$c->latitude],
                ],
                'properties' => [
                    'layer' => 'client',
                    'name'  => $c->type==='professionnel' ? ($c->raison_sociale ?? 'Entreprise') : trim(($c->prenom ?? '').' '.($c->nom ?? '')),
                    'zone'  => $c->zone,
                ]
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }
}
