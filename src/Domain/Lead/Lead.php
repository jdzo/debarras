<?php

declare(strict_types=1);

namespace App\Domain\Lead;

use App\Domain\Estimation\EstimationId;
use App\Domain\Lead\Event\LeadCree;
use App\Domain\Lead\ValueObject\ContactLead;
use App\Domain\Lead\ValueObject\ScoreLead;
use App\Domain\Lead\ValueObject\SourceTracking;
use App\Domain\Lead\ValueObject\StatutLead;
use App\Domain\Lead\ValueObject\TypeCapture;
use DateTimeImmutable;
use DomainException;

class Lead
{
    private array $domainEvents = [];

    private function __construct(
        private LeadId $id,
        private ContactLead $contact,
        private TypeCapture $typeCapture,
        private SourceTracking $source,
        private ScoreLead $score,
        private StatutLead $statut,
        private DateTimeImmutable $createdAt,
        private ?EstimationId $estimationId = null,
        private ?DateTimeImmutable $relancedAt = null,
        private ?DateTimeImmutable $contactedAt = null,
    ) {
    }

    public static function creer(
        LeadId $id,
        ContactLead $contact,
        TypeCapture $typeCapture,
        SourceTracking $source,
        ScoreLead $score,
        ?EstimationId $estimationId = null,
    ): self {
        $lead = new self(
            id: $id,
            contact: $contact,
            typeCapture: $typeCapture,
            source: $source,
            score: $score,
            statut: StatutLead::NOUVEAU,
            createdAt: new DateTimeImmutable(),
            estimationId: $estimationId,
        );

        $lead->domainEvents[] = new LeadCree($id, $score, $typeCapture, $lead->createdAt);

        return $lead;
    }

    public static function reconstituer(
        LeadId $id,
        ContactLead $contact,
        TypeCapture $typeCapture,
        SourceTracking $source,
        ScoreLead $score,
        StatutLead $statut,
        DateTimeImmutable $createdAt,
        ?EstimationId $estimationId = null,
        ?DateTimeImmutable $relancedAt = null,
        ?DateTimeImmutable $contactedAt = null,
    ): self {
        return new self(
            id: $id,
            contact: $contact,
            typeCapture: $typeCapture,
            source: $source,
            score: $score,
            statut: $statut,
            createdAt: $createdAt,
            estimationId: $estimationId,
            relancedAt: $relancedAt,
            contactedAt: $contactedAt,
        );
    }

    public function marquerContacte(): void
    {
        if (StatutLead::NOUVEAU !== $this->statut) {
            throw new DomainException('Seul un lead nouveau peut être marqué comme contacté');
        }
        $this->statut = StatutLead::CONTACTE;
        $this->contactedAt = new DateTimeImmutable();
    }

    public function convertir(): void
    {
        if (StatutLead::CONTACTE !== $this->statut) {
            throw new DomainException('Seul un lead contacté peut être converti');
        }
        $this->statut = StatutLead::CONVERTI;
    }

    public function perdre(): void
    {
        if (StatutLead::CONVERTI === $this->statut) {
            throw new DomainException('Un lead converti ne peut pas être perdu');
        }
        $this->statut = StatutLead::PERDU;
    }

    public function enregistrerRelance(): void
    {
        $this->relancedAt = new DateTimeImmutable();
    }

    public function id(): LeadId
    {
        return $this->id;
    }

    public function contact(): ContactLead
    {
        return $this->contact;
    }

    public function typeCapture(): TypeCapture
    {
        return $this->typeCapture;
    }

    public function source(): SourceTracking
    {
        return $this->source;
    }

    public function score(): ScoreLead
    {
        return $this->score;
    }

    public function statut(): StatutLead
    {
        return $this->statut;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function estimationId(): ?EstimationId
    {
        return $this->estimationId;
    }

    public function relancedAt(): ?DateTimeImmutable
    {
        return $this->relancedAt;
    }

    public function contactedAt(): ?DateTimeImmutable
    {
        return $this->contactedAt;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
