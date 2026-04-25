<?php

declare(strict_types=1);

namespace App\Tests\Unit\Estimation;

use App\Domain\Estimation\Service\CalculateurPrix;
use App\Domain\Estimation\ValueObject\Accessibilite;
use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Options;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Estimation\ValueObject\ZoneTarifaire;
use PHPUnit\Framework\TestCase;

class CalculateurPrixTest extends TestCase
{
    private CalculateurPrix $calculateur;

    protected function setUp(): void
    {
        $this->calculateur = new CalculateurPrix();
    }

    public function testCalculBasiqueSansCoefficient(): void
    {
        // 75m² moyenne × 15€/m² × 1.0 × 1.0 × 1.0 = 1125€
        $fourchette = $this->calculateur->calculer(
            Superficie::S_50_100,
            NiveauEncombrement::VIDE,
            NiveauSalete::PROPRE,
            Accessibilite::RDC,
            Options::aucune(),
        );

        $this->assertEquals(956, $fourchette->prixMin);  // 1125 × 0.85
        $this->assertEquals(1294, $fourchette->prixMax);  // 1125 × 1.15
    }

    public function testCalculAvecTousCoefficients(): void
    {
        // 75m² × 15€ × 1.6 (encombré) × 2.0 (diogène) × 1.25 (sans ascenseur) = 4500€
        $prixBase = $this->calculateur->calculerPrixBase(
            Superficie::S_50_100,
            NiveauEncombrement::TRES_ENCOMBRE,
            NiveauSalete::DIOGENE,
            Accessibilite::ETAGE_SANS_ASCENSEUR,
            Options::aucune(),
        );

        $this->assertEquals(4500, $prixBase);
    }

    public function testCalculAvecOptions(): void
    {
        // Base: 1125€ + nettoyage(150) + desinfection(200) = 1475€
        $prixBase = $this->calculateur->calculerPrixBase(
            Superficie::S_50_100,
            NiveauEncombrement::VIDE,
            NiveauSalete::PROPRE,
            Accessibilite::RDC,
            new Options(nettoyage: true, desinfection: true),
        );

        $this->assertEquals(1475, $prixBase);
    }

    public function testCalculAvecZoneIleDeFrance(): void
    {
        // 75m² × 15€ × 1.0 × 1.0 × 1.0 × 1.3 (IDF) = 1462.5 → 1463
        $prixBase = $this->calculateur->calculerPrixBase(
            Superficie::S_50_100,
            NiveauEncombrement::VIDE,
            NiveauSalete::PROPRE,
            Accessibilite::RDC,
            Options::aucune(),
            ZoneTarifaire::ILE_DE_FRANCE,
        );

        $this->assertEquals(1463, $prixBase);
    }

    public function testCalculAvecZoneGrandeVille(): void
    {
        // 75m² × 15€ × 1.0 × 1.0 × 1.0 × 1.15 = 1293.75 → 1294
        $prixBase = $this->calculateur->calculerPrixBase(
            Superficie::S_50_100,
            NiveauEncombrement::VIDE,
            NiveauSalete::PROPRE,
            Accessibilite::RDC,
            Options::aucune(),
            ZoneTarifaire::GRANDE_VILLE,
        );

        $this->assertEquals(1294, $prixBase);
    }
}
