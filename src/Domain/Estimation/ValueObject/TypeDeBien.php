<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

enum TypeDeBien: string
{
    case MAISON = 'maison';
    case APPARTEMENT = 'appartement';
    case LOCAL_COMMERCIAL = 'local_commercial';
    case CAVE_GRENIER = 'cave_grenier';
    case GARAGE = 'garage';

    public function label(): string
    {
        return match ($this) {
            self::MAISON => 'Maison',
            self::APPARTEMENT => 'Appartement',
            self::LOCAL_COMMERCIAL => 'Local commercial',
            self::CAVE_GRENIER => 'Cave / Grenier',
            self::GARAGE => 'Garage',
        };
    }
}
