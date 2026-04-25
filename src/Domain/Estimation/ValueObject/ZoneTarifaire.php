<?php

declare(strict_types=1);

namespace App\Domain\Estimation\ValueObject;

enum ZoneTarifaire: string
{
    case ILE_DE_FRANCE = 'ile_de_france';
    case GRANDE_VILLE = 'grande_ville';
    case PROVINCE = 'province';

    public function coefficient(): float
    {
        return match ($this) {
            self::ILE_DE_FRANCE => 1.3,
            self::GRANDE_VILLE => 1.15,
            self::PROVINCE => 1.0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ILE_DE_FRANCE => 'Ile-de-France',
            self::GRANDE_VILLE => 'Grande ville',
            self::PROVINCE => 'Province',
        };
    }

    public static function fromCodePostal(?string $codePostal): self
    {
        if ($codePostal === null || $codePostal === '') {
            return self::PROVINCE;
        }

        $departement = substr($codePostal, 0, 2);

        if (in_array($departement, ['75', '77', '78', '91', '92', '93', '94', '95'], true)) {
            return self::ILE_DE_FRANCE;
        }

        $grandesVilles = ['13', '31', '33', '34', '44', '59', '67', '69'];
        if (in_array($departement, $grandesVilles, true)) {
            return self::GRANDE_VILLE;
        }

        return self::PROVINCE;
    }
}
