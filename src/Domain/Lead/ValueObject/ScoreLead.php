<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObject;

enum ScoreLead: string
{
    case HOT = 'hot';
    case WARM = 'warm';
    case COLD = 'cold';

    public function label(): string
    {
        return match ($this) {
            self::HOT => 'Chaud',
            self::WARM => 'Tiède',
            self::COLD => 'Froid',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::HOT => 'danger',
            self::WARM => 'warning',
            self::COLD => 'secondary',
        };
    }
}
