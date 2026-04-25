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
        if (NiveauSalete::DIOGENE === $salete) {
            return true;
        }
        if (Superficie::S_200_PLUS === $superficie) {
            return true;
        }
        if (null !== $prixEstime && $prixEstime > 2000) {
            return true;
        }
        if (NiveauEncombrement::TRES_ENCOMBRE === $encombrement && null !== $salete && NiveauSalete::PROPRE !== $salete) {
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
        if (TypeCapture::ESTIMATION_COMPLETE === $typeCapture) {
            return true;
        }
        if (Superficie::S_100_200 === $superficie) {
            return true;
        }
        if (null !== $prixEstime && $prixEstime > 800) {
            return true;
        }
        if (NiveauEncombrement::TRES_ENCOMBRE === $encombrement) {
            return true;
        }

        return false;
    }
}
