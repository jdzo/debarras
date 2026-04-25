<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

enum NiveauSalete: string
{
    case PROPRE = 'propre';
    case SALE = 'sale';
    case TRES_SALE = 'tres_sale';
    case DIOGENE = 'diogene';

    public function label(): string
    {
        return match ($this) {
            self::PROPRE => 'Propre',
            self::SALE => 'Sale',
            self::TRES_SALE => 'Très sale',
            self::DIOGENE => 'Syndrome de Diogène',
        };
    }

    public function coefficient(): float
    {
        return match ($this) {
            self::PROPRE => 1.0,
            self::SALE => 1.2,
            self::TRES_SALE => 1.5,
            self::DIOGENE => 2.0,
        };
    }
}
