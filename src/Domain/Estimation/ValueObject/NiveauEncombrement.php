<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

enum NiveauEncombrement: string
{
    case VIDE = 'vide';
    case MEUBLE_NORMAL = 'meuble_normal';
    case TRES_ENCOMBRE = 'tres_encombre';

    public function label(): string
    {
        return match ($this) {
            self::VIDE => 'Vide ou presque vide',
            self::MEUBLE_NORMAL => 'Meublé normalement',
            self::TRES_ENCOMBRE => 'Très encombré',
        };
    }

    public function coefficient(): float
    {
        return match ($this) {
            self::VIDE => 1.0,
            self::MEUBLE_NORMAL => 1.3,
            self::TRES_ENCOMBRE => 1.6,
        };
    }
}
