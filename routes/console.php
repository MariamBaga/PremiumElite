<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Models\DossierRaccordement;

// Runs hourly
Schedule::call(function () {

    // 1) RDV imminents (dans les 24h)
    $in24h = DossierRaccordement::with(['team.lead'])
        ->whereNotNull('date_planifiee')
        ->whereBetween('date_planifiee', [now(), now()->addDay()])
        ->get();

    // TODO: notifier les leads
    foreach ($in24h as $d) {
        Log::info("RDV imminent: {$d->reference} pour équipe {$d->team?->name}");
        // Notification::send($d->team?->lead, new RdvImminent($d));
    }

    // 2) Dossiers inactifs (> 3 jours sans update)
    $stale = DossierRaccordement::with(['team.lead'])
        ->whereIn('statut', ['en_appel','en_equipe','replanifie','injoignable'])
        ->where('updated_at','<', now()->subDays(3))
        ->get();

    foreach ($stale as $d) {
        Log::warning("Dossier inactif 3j+: {$d->reference}");
        // Notification::send($d->team?->lead, new DossierInactif($d));
    }

    // 3) Zones redevenues OK (réalisation hier → réveiller les pendants “zone_depourvue”)
    $zonesOK = DossierRaccordement::where('statut','realise')
        ->whereDate('date_realisation','>=', now()->subDay()->toDateString())
        ->join('clients','clients.id','=','dossiers_raccordement.client_id')
        ->pluck('clients.zone')
        ->filter()
        ->unique();

    foreach ($zonesOK as $zone) {
        $pendants = DossierRaccordement::where('statut','zone_depourvue')
            ->whereHas('client', fn($q)=>$q->where('zone',$zone))
            ->get();

        if ($pendants->isNotEmpty()) {
            Log::info("Zone redevenue OK: $zone — dossiers en attente: ".$pendants->count());
            // Notification::route('mail', $coordMail)->notify(new ZoneRedevenueOK($zone, $pendants));
        }
    }

})->hourly();
