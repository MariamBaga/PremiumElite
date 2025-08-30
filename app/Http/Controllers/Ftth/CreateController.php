<?php

namespace App\Http\Controllers\Ftth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;

class CreateController extends Controller
{
    /**
     * Page "Création unique" : onglets Nouveau Client / Nouveau Dossier.
     * Vue : resources/views/ftth/create.blade.php
     */
    public function __invoke(Request $request)
    {
        $clients = Client::orderBy('created_at','desc')->take(200)->get();
        // Conserver un éventuel tab passé en query ?tab=dossier|client
        $activeTab = $request->get('tab', 'client');

        return view('ftth.create', [
            'clients'    => $clients,
            'active_tab' => $activeTab,
        ]);
    }
}
