<?php

namespace App\Enums;

enum StatutDossier: string
{
    case INDISPONIBLE   = 'indisponible';
    case INJOIGNABLE    = 'injoignable';
    case PBO_SATURE     = 'pbo_sature';
    case ZONE_DEPOURVUE = 'zone_depourvue';
    case REALISE        = 'realise';
    case EN_APPEL       = 'en_appel';
    case EN_EQUIPE      = 'en_equipe';
    case ACTIVE         = 'active';
    case NOUVEAU_RENDEZ_VOUS = 'nouveau_rendez_vous';
    case DEPASSEMENT_LINEAIRE = 'depassement_lineaire'; // ✅ nouveau statut
    case IMPLANTATION_POTEAU = 'implantation_poteau'; // ✅ nouveau statut

    public static function labels(): array
    {
        return [
            self::INDISPONIBLE->value   => 'Indisponible',
            self::INJOIGNABLE->value    => 'Injoignable',
            self::PBO_SATURE->value     => 'PBO saturé',
            self::ZONE_DEPOURVUE->value => 'Zone dépourvue',
            self::REALISE->value        => 'Réalisé',
            self::EN_APPEL->value       => 'En appel',
            self::EN_EQUIPE->value      => 'En équipe',
            self::ACTIVE->value         => 'Active',
            self::NOUVEAU_RENDEZ_VOUS->value => 'Nouveau rendez-vous',
            self::DEPASSEMENT_LINEAIRE->value => 'Dépassement linéaire', // ✅ label
            self::IMPLANTATION_POTEAU->value => 'Implantation poteau', // ✅ label
        ];
    }
}
