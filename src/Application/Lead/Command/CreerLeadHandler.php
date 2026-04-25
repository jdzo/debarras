<?php

declare(strict_types=1);

namespace App\Application\Lead\Command;

use App\Domain\Estimation\EstimationId;
use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Lead\Lead;
use App\Domain\Lead\LeadRepository;
use App\Domain\Lead\Service\ScoringLead;
use App\Domain\Lead\ValueObject\ContactLead;
use App\Domain\Lead\ValueObject\SourceTracking;
use App\Domain\Lead\ValueObject\TypeCapture;
use App\Domain\Shared\MessageBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreerLeadHandler
{
    public function __construct(
        private readonly LeadRepository $repository,
        private readonly ScoringLead $scoring,
        private readonly MessageBus $messageBus,
    ) {
    }

    public function __invoke(CreerLeadCommand $command): string
    {
        $typeCapture = TypeCapture::from($command->typeCapture);

        $score = $this->scoring->scorer(
            typeCapture: $typeCapture,
            salete: $command->salete ? NiveauSalete::tryFrom($command->salete) : null,
            superficie: $command->superficie ? Superficie::tryFrom($command->superficie) : null,
            encombrement: $command->encombrement ? NiveauEncombrement::tryFrom($command->encombrement) : null,
            prixEstime: $command->prixEstime,
        );

        $lead = Lead::creer(
            id: $this->repository->nextId(),
            contact: new ContactLead($command->nom, $command->telephone, $command->email),
            typeCapture: $typeCapture,
            source: new SourceTracking(
                $command->utmSource, $command->utmMedium, $command->utmCampaign,
                $command->utmTerm, $command->utmContent, $command->referrer, $command->landingPage,
            ),
            score: $score,
            estimationId: $command->estimationId ? EstimationId::fromString($command->estimationId) : null,
        );

        $this->repository->save($lead);

        foreach ($lead->pullDomainEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        return $lead->id()->value();
    }
}
