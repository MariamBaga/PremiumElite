<?php

namespace App\Notifications;

use App\Models\DossierRaccordement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NouveauRdvNotification extends Notification
{
    use Queueable;

    protected $dossier;

    public function __construct(DossierRaccordement $dossier)
    {
        $this->dossier = $dossier;
    }

    // Définir les canaux utilisés
    public function via($notifiable)
    {
        return ['database'];
    }

    // Contenu de la notification stockée en base
    public function toDatabase($notifiable)
    {
        return [
            'dossier_id' => $this->dossier->id,
            'client' => $this->dossier->client->nom ?? null,
            'message' => 'Un nouveau rendez-vous a été créé.',
            'date_rdv' => $this->dossier->date_planifiee,
        ];
    }

    // Optionnel si tu veux l’envoyer par mail aussi
    // public function toMail($notifiable) {}
}
