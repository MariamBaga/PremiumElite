<?php

namespace App\Enums;

enum StatutDossier: string
{
    case INJOIGNABLE    = 'injoignable';
    case PBO_SATURE     = 'pbo_sature';
    case ZONE_DEPOURVUE = 'zone_depourvue';
    case REALISE        = 'realise';
    case EN_APPEL       = 'en_appel';
    case EN_EQUIPE      = 'en_equipe';
    case ON             = 'on';

    public static function labels(): array
    {
        return [
            self::INJOIGNABLE->value    => 'Injoignable',
            self::PBO_SATURE->value     => 'PBO saturé',
            self::ZONE_DEPOURVUE->value => 'Zone dépourvue',
            self::REALISE->value        => 'Réalisé',
            self::EN_APPEL->value       => 'En appel',
            self::EN_EQUIPE->value      => 'En équipe',
            self::ON->value             => 'On',
        ];
    }

    public static function ouverts(): array
{
    return [
        self::EN_APPEL->value,
        self::EN_EQUIPE->value,
        self::ON->value,
    ];
}

public function isOuvert(): bool
{
    return in_array($this->value, self::ouverts(), true);
}
}
