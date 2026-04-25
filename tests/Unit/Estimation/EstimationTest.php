<?php

declare(strict_types=1);

namespace App\Tests\Unit\Estimation;

use App\Domain\Estimation\Estimation;
use App\Domain\Estimation\EstimationId;
use App\Domain\Estimation\Event\EstimationCreee;
use App\Domain\Estimation\Service\CalculateurPrix;
use App\Domain\Estimation\ValueObject\Accessibilite;
use App\Domain\Estimation\ValueObject\Coordonnees;
use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Options;
use App\Domain\Estimation\ValueObject\StatutEstimation;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Estimation\ValueObject\TypeDeBien;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class EstimationTest extends TestCase
{
    public function testCreerEstimation(): void
    {
        $estimation = $this->creerEstimation();

        $this->assertEquals(TypeDeBien::MAISON, $estimation->typeDeBien());
        $this->assertEquals(Superficie::S_50_100, $estimation->superficie());
        $this->assertEquals(StatutEstimation::NOUVELLE, $estimation->statut());
        $this->assertGreaterThan(0, $estimation->fourchette()->prixMin);
        $this->assertGreaterThan($estimation->fourchette()->prixMin, $estimation->fourchette()->prixMax);
    }

    public function testCreerEstimationEmetEvenement(): void
    {
        $estimation = $this->creerEstimation();
        $events = $estimation->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(EstimationCreee::class, $events[0]);
    }

    public function testPullDomainEventsVideApresAppel(): void
    {
        $estimation = $this->creerEstimation();
        $estimation->pullDomainEvents();

        $this->assertEmpty($estimation->pullDomainEvents());
    }

    public function testTransitionStatuts(): void
    {
        $estimation = $this->creerEstimation();
        $estimation->pullDomainEvents();

        $estimation->marquerContactee();
        $this->assertEquals(StatutEstimation::CONTACTEE, $estimation->statut());

        $estimation->envoyerDevis();
        $this->assertEquals(StatutEstimation::DEVIS_ENVOYE, $estimation->statut());

        $estimation->accepter();
        $this->assertEquals(StatutEstimation::ACCEPTEE, $estimation->statut());
    }

    public function testRefuserApresAccepteeEchoue(): void
    {
        $estimation = $this->creerEstimation();
        $estimation->pullDomainEvents();
        $estimation->envoyerDevis();
        $estimation->accepter();

        $this->expectException(DomainException::class);
        $estimation->refuser();
    }

    public function testReconstituerNeGenereAucunEvenement(): void
    {
        $estimation = Estimation::reconstituer(
            id: EstimationId::generate(),
            typeDeBien: TypeDeBien::APPARTEMENT,
            superficie: Superficie::S_100_200,
            encombrement: NiveauEncombrement::TRES_ENCOMBRE,
            salete: NiveauSalete::SALE,
            accessibilite: Accessibilite::ETAGE_SANS_ASCENSEUR,
            options: new Options(nettoyage: true),
            coordonnees: new Coordonnees('Test', '0600000000', 'test@test.com'),
            fourchette: new \App\Domain\Estimation\ValueObject\FourchetteEstimation(800, 1200),
            statut: StatutEstimation::CONTACTEE,
            createdAt: new DateTimeImmutable(),
            accessToken: bin2hex(random_bytes(32)),
        );

        $this->assertEmpty($estimation->pullDomainEvents());
        $this->assertEquals(StatutEstimation::CONTACTEE, $estimation->statut());
    }

    private function creerEstimation(): Estimation
    {
        return Estimation::creer(
            id: EstimationId::generate(),
            typeDeBien: TypeDeBien::MAISON,
            superficie: Superficie::S_50_100,
            encombrement: NiveauEncombrement::MEUBLE_NORMAL,
            salete: NiveauSalete::PROPRE,
            accessibilite: Accessibilite::RDC,
            options: Options::aucune(),
            coordonnees: Coordonnees::create('Jean Dupont', '0612345678', 'jean@example.com'),
            calculateur: new CalculateurPrix(),
        );
    }
}
