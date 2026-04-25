<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

enum Accessibilite: string
{
    case RDC = 'rdc';
    case ETAGE_AVEC_ASCENSEUR = 'etage_avec_ascenseur';
    case ETAGE_SANS_ASCENSEUR = 'etage_sans_ascenseur';

    public function label(): string
    {
        return match ($this) {
            self::RDC => 'Rez-de-chaussée',
            self::ETAGE_AVEC_ASCENSEUR => 'Étage avec ascenseur',
            self::ETAGE_SANS_ASCENSEUR => 'Étage sans ascenseur',
        };
    }

    public function coefficient(): float
    {
        return match ($this) {
            self::RDC => 1.0,
            self::ETAGE_AVEC_ASCENSEUR => 1.1,
            self::ETAGE_SANS_ASCENSEUR => 1.25,
        };
    }
}
