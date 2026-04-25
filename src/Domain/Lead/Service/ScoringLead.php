<?php

declare(strict_types=1);

namespace App\Domain\Lead\Service;

use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Lead\ValueObject\ScoreLead;
use App\Domain\Lead\ValueObject\TypeCapture;

final class ScoringLead
{
    public function scorer(
        TypeCapture $typeCapture,
        ?NiveauSalete $salete = null,
        ?Superficie $superficie = null,
        ?NiveauEncombrement $encombrement = null,
        ?int $prixEstime = null,
    ): ScoreLead {
        if ($this->estChaud($salete, $superficie, $encombrement, $prixEstime)) {
            return ScoreLead::HOT;
        }

        if ($this->estTiede($typeCapture, $superficie, $encombrement, $prixEstime)) {
            return ScoreLead::WARM;
        }

        return ScoreLead::COLD;
    }

    private function estChaud(
        ?NiveauSalete $salete,
        ?Superficie $superficie,
        ?NiveauEncombrement $encombrement,
        ?int $prixEstime,
    ): bool {
        if ($salete === NiveauSalete::DIOGENE) {
            return true;
        }
        if ($superficie === Superficie::S_200_PLUS) {
            return true;
        }
        if ($prixEstime !== null && $prixEstime > 2000) {
            return true;
        }
        if ($encombrement === NiveauEncombrement::TRES_ENCOMBRE && $salete !== null && $salete !== NiveauSalete::PROPRE) {
            return true;
        }

        return false;
    }

    private function estTiede(
        TypeCapture $typeCapture,
        ?Superficie $superficie,
        ?NiveauEncombrement $encombrement,
        ?int $prixEstime,
    ): bool {
        if ($typeCapture === TypeCapture::ESTIMATION_COMPLETE) {
            return true;
        }
        if ($superficie === Superficie::S_100_200) {
            return true;
        }
        if ($prixEstime !== null && $prixEstime > 800) {
            return true;
        }
        if ($encombrement === NiveauEncombrement::TRES_ENCOMBRE) {
            return true;
        }

        return false;
    }
}
