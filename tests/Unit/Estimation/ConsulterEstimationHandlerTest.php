<?php

declare(strict_types=1);

namespace App\Tests\Unit\Estimation;

use App\Application\Estimation\Query\ConsulterEstimationHandler;
use App\Application\Estimation\Query\ConsulterEstimationQuery;
use App\Domain\Estimation\Estimation;
use App\Domain\Estimation\EstimationId;
use App\Domain\Estimation\EstimationRepository;
use App\Domain\Estimation\Service\CalculateurPrix;
use App\Domain\Estimation\ValueObject\Accessibilite;
use App\Domain\Estimation\ValueObject\Coordonnees;
use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Options;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Estimation\ValueObject\TypeDeBien;
use PHPUnit\Framework\TestCase;

class ConsulterEstimationHandlerTest extends TestCase
{
    public function testConsulterEstimationExistante(): void
    {
        $estimation = Estimation::creer(
            id: EstimationId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            typeDeBien: TypeDeBien::APPARTEMENT,
            superficie: Superficie::S_100_200,
            encombrement: NiveauEncombrement::TRES_ENCOMBRE,
            salete: NiveauSalete::SALE,
            accessibilite: Accessibilite::ETAGE_SANS_ASCENSEUR,
            options: new Options(nettoyage: true, desinfection: true),
            coordonnees: Coordonnees::create('Marie Martin', '0698765432', 'marie@example.com'),
            calculateur: new CalculateurPrix(),
        );

        $repository = new class($estimation) implements EstimationRepository {
            public function __construct(private Estimation $estimation) {}

            public function save(Estimation $estimation): void {}
            public function findById(EstimationId $id): ?Estimation
            {
                return $id->value() === $this->estimation->id()->value() ? $this->estimation : null;
            }
            public function delete(Estimation $estimation): void {}
            public function nextId(): EstimationId { return EstimationId::generate(); }
        };

        $handler = new ConsulterEstimationHandler($repository);
        $result = $handler(new ConsulterEstimationQuery('550e8400-e29b-41d4-a716-446655440000'));

        $this->assertNotNull($result);
        $this->assertEquals('Appartement', $result->typeDeBien);
        $this->assertEquals('100 à 200 m²', $result->superficie);
        $this->assertEquals('Nouvelle', $result->statut);
        $this->assertGreaterThan(0, $result->prixMin);
    }

    public function testConsulterEstimationInexistante(): void
    {
        $repository = new class() implements EstimationRepository {
            public function save(Estimation $estimation): void {}
            public function findById(EstimationId $id): ?Estimation { return null; }
            public function delete(Estimation $estimation): void {}
            public function nextId(): EstimationId { return EstimationId::generate(); }
        };

        $handler = new ConsulterEstimationHandler($repository);
        $result = $handler(new ConsulterEstimationQuery('550e8400-e29b-41d4-a716-446655440000'));

        $this->assertNull($result);
    }
}
