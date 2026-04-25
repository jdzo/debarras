<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

enum StatutEstimation: string
{
    case NOUVELLE = 'nouvelle';
    case CONTACTEE = 'contactee';
    case DEVIS_ENVOYE = 'devis_envoye';
    case ACCEPTEE = 'acceptee';
    case REFUSEE = 'refusee';
    case EXPIREE = 'expiree';

    public function label(): string
    {
        return match ($this) {
            self::NOUVELLE => 'Nouvelle',
            self::CONTACTEE => 'Contactée',
            self::DEVIS_ENVOYE => 'Devis envoyé',
            self::ACCEPTEE => 'Acceptée',
            self::REFUSEE => 'Refusée',
            self::EXPIREE => 'Expirée',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::NOUVELLE => 'info',
            self::CONTACTEE => 'warning',
            self::DEVIS_ENVOYE => 'primary',
            self::ACCEPTEE => 'success',
            self::REFUSEE => 'danger',
            self::EXPIREE => 'secondary',
        };
    }
}
