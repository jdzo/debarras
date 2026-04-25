<?php

declare(strict_types=1);

namespace App\Tests\Unit\Lead;

use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Lead\Service\ScoringLead;
use App\Domain\Lead\ValueObject\ScoreLead;
use App\Domain\Lead\ValueObject\TypeCapture;
use PHPUnit\Framework\TestCase;

class ScoringLeadTest extends TestCase
{
    private ScoringLead $scoring;

    protected function setUp(): void
    {
        $this->scoring = new ScoringLead();
    }

    public function testDiogeneEstChaud(): void
    {
        $score = $this->scoring->scorer(TypeCapture::ESTIMATION_COMPLETE, NiveauSalete::DIOGENE);
        $this->assertSame(ScoreLead::HOT, $score);
    }

    public function testGrandeSuperficieEstChaud(): void
    {
        $score = $this->scoring->scorer(TypeCapture::ESTIMATION_COMPLETE, superficie: Superficie::S_200_PLUS);
        $this->assertSame(ScoreLead::HOT, $score);
    }

    public function testPrixEleveEstChaud(): void
    {
        $score = $this->scoring->scorer(TypeCapture::RAPPEL_GRATUIT, prixEstime: 2500);
        $this->assertSame(ScoreLead::HOT, $score);
    }

    public function testTresEncombreEtSaleEstChaud(): void
    {
        $score = $this->scoring->scorer(
            TypeCapture::ESTIMATION_COMPLETE,
            NiveauSalete::TRES_SALE,
            encombrement: NiveauEncombrement::TRES_ENCOMBRE,
        );
        $this->assertSame(ScoreLead::HOT, $score);
    }

    public function testEstimationCompleteEstTiede(): void
    {
        $score = $this->scoring->scorer(
            TypeCapture::ESTIMATION_COMPLETE,
            NiveauSalete::PROPRE,
            Superficie::S_0_50,
            NiveauEncombrement::VIDE,
        );
        $this->assertSame(ScoreLead::WARM, $score);
    }

    public function testSuperficieMoyenneEstTiede(): void
    {
        $score = $this->scoring->scorer(TypeCapture::RAPPEL_GRATUIT, superficie: Superficie::S_100_200);
        $this->assertSame(ScoreLead::WARM, $score);
    }

    public function testRappelGratuitSansDetailsEstFroid(): void
    {
        $score = $this->scoring->scorer(TypeCapture::RAPPEL_GRATUIT);
        $this->assertSame(ScoreLead::COLD, $score);
    }

    public function testContactSimpleEstFroid(): void
    {
        $score = $this->scoring->scorer(TypeCapture::CONTACT);
        $this->assertSame(ScoreLead::COLD, $score);
    }
}
