<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObject;

enum StatutLead: string
{
    case NOUVEAU = 'nouveau';
    case CONTACTE = 'contacte';
    case CONVERTI = 'converti';
    case PERDU = 'perdu';

    public function label(): string
    {
        return match ($this) {
            self::NOUVEAU => 'Nouveau',
            self::CONTACTE => 'Contacté',
            self::CONVERTI => 'Converti',
            self::PERDU => 'Perdu',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::NOUVEAU => 'info',
            self::CONTACTE => 'warning',
            self::CONVERTI => 'success',
            self::PERDU => 'secondary',
        };
    }
}
