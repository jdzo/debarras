<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

enum Superficie: string
{
    case S_0_50 = '0_50';
    case S_50_100 = '50_100';
    case S_100_200 = '100_200';
    case S_200_PLUS = '200_plus';

    public function label(): string
    {
        return match ($this) {
            self::S_0_50 => 'Moins de 50 m²',
            self::S_50_100 => '50 à 100 m²',
            self::S_100_200 => '100 à 200 m²',
            self::S_200_PLUS => 'Plus de 200 m²',
        };
    }

    public function moyenneM2(): int
    {
        return match ($this) {
            self::S_0_50 => 35,
            self::S_50_100 => 75,
            self::S_100_200 => 150,
            self::S_200_PLUS => 250,
        };
    }
}
