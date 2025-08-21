<?php

namespace App\Enums;

enum StatutDossier: string
{
    case A_TRAITER         = 'a_traiter';
    case INJOIGNABLE       = 'injoignable';
    case PBO_SATURE        = 'pbo_sature';
    case ZONE_DEPOURVUE    = 'zone_depourvue';
    case REALISE           = 'realise';
    case EN_ATTENTE_MATERIEL= 'en_attente_materiel';
    case REPLANIFIE        = 'replanifie';
    case ANNULE            = 'annule';

    public static function labels(): array {
        return [
            self::A_TRAITER->value          => 'À traiter',
            self::INJOIGNABLE->value        => 'Injoignable',
            self::PBO_SATURE->value         => 'PBO saturé',
            self::ZONE_DEPOURVUE->value     => 'Zone dépourvue',
            self::REALISE->value            => 'Réalisé',
            self::EN_ATTENTE_MATERIEL->value=> 'En attente matériel',
            self::REPLANIFIE->value         => 'Replanifié',
            self::ANNULE->value             => 'Annulé',
        ];
    }
}
