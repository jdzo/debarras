<?php

declare(strict_types=1);

namespace App\Domain\Estimation\Service;

use App\Domain\Estimation\ValueObject\Accessibilite;
use App\Domain\Estimation\ValueObject\FourchetteEstimation;
use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Options;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Estimation\ValueObject\ZoneTarifaire;

final class CalculateurPrix
{
    private const BASE_PAR_M2 = 15;
    private const MARGE = 0.15;

    public function calculer(
        Superficie $superficie,
        NiveauEncombrement $encombrement,
        NiveauSalete $salete,
        Accessibilite $accessibilite,
        Options $options,
        ZoneTarifaire $zone = ZoneTarifaire::PROVINCE,
    ): FourchetteEstimation {
        $prixTotal = $this->calculerPrixBase($superficie, $encombrement, $salete, $accessibilite, $options, $zone);

        return FourchetteEstimation::fromPrixBase($prixTotal, self::MARGE);
    }

    public function calculerPrixBase(
        Superficie $superficie,
        NiveauEncombrement $encombrement,
        NiveauSalete $salete,
        Accessibilite $accessibilite,
        Options $options,
        ZoneTarifaire $zone = ZoneTarifaire::PROVINCE,
    ): int {
        $base = self::BASE_PAR_M2 * $superficie->moyenneM2();

        $prixBase = $base
            * $encombrement->coefficient()
            * $salete->coefficient()
            * $accessibilite->coefficient()
            * $zone->coefficient();

        return (int) round($prixBase) + $options->total();
    }
}
