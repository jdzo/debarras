<?php

declare(strict_types=1);

namespace App\Tests\Unit\Estimation;

use App\Application\Estimation\Command\CreerEstimationCommand;
use App\Application\Estimation\Command\CreerEstimationHandler;
use App\Domain\Estimation\Estimation;
use App\Domain\Estimation\EstimationId;
use App\Domain\Estimation\EstimationRepository;
use App\Domain\Estimation\Service\CalculateurPrix;
use App\Domain\Lead\Lead;
use App\Domain\Lead\LeadId;
use App\Domain\Lead\LeadRepository;
use App\Domain\Lead\Service\ScoringLead;
use App\Domain\Shared\MessageBus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CreerEstimationHandlerTest extends TestCase
{
    public function testCreerEstimationViHandler(): void
    {
        $savedEstimation = null;
        $dispatchedEvents = [];

        $repository = new class($savedEstimation) implements EstimationRepository {
            public function __construct(private ?Estimation &$saved)
            {
            }

            public function save(Estimation $estimation): void
            {
                $this->saved = $estimation;
            }

            public function findById(EstimationId $id): ?Estimation
            {
                return null;
            }

            public function delete(Estimation $estimation): void
            {
            }

            public function nextId(): EstimationId
            {
                return EstimationId::generate();
            }
        };

        $messageBus = new class($dispatchedEvents) implements MessageBus {
            public function __construct(private array &$events)
            {
            }

            public function dispatch(object $message): void
            {
                $this->events[] = $message;
            }
        };

        $leadRepository = new class implements LeadRepository {
            public function save(Lead $lead): void
            {
            }

            public function findById(LeadId $id): ?Lead
            {
                return null;
            }

            public function nextId(): LeadId
            {
                return LeadId::generate();
            }

            public function findLeadsARelancer(DateTimeImmutable $avant): array
            {
                return [];
            }
        };

        $handler = new CreerEstimationHandler(
            $repository,
            $leadRepository,
            new CalculateurPrix(),
            new ScoringLead(),
            $messageBus,
        );

        $command = new CreerEstimationCommand(
            typeDeBien: 'maison',
            superficie: '50_100',
            encombrement: 'meuble_normal',
            salete: 'propre',
            accessibilite: 'rdc',
            optionNettoyage: true,
            nom: 'Jean Dupont',
            telephone: '0612345678',
            email: 'jean@example.com',
        );

        $result = $handler($command);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result['id']);
        $this->assertNotEmpty($result['accessToken']);
        $this->assertNotNull($savedEstimation);
        $this->assertEquals('maison', $savedEstimation->typeDeBien()->value);
        $this->assertTrue($savedEstimation->options()->nettoyage);
        $this->assertCount(2, $dispatchedEvents);
    }
}
