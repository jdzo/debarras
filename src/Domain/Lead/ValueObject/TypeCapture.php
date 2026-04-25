<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObject;

enum TypeCapture: string
{
    case ESTIMATION_COMPLETE = 'estimation_complete';
    case ESTIMATION_RAPIDE = 'estimation_rapide';
    case RAPPEL_GRATUIT = 'rappel_gratuit';
    case CONTACT = 'contact';

    public function label(): string
    {
        return match ($this) {
            self::ESTIMATION_COMPLETE => 'Estimation complète',
            self::ESTIMATION_RAPIDE => 'Estimation rapide',
            self::RAPPEL_GRATUIT => 'Rappel gratuit',
            self::CONTACT => 'Contact',
        };
    }
}
