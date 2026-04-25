<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Estimation\EstimationId;
use App\Domain\Lead\Lead;
use App\Domain\Lead\LeadId;
use App\Domain\Lead\LeadRepository;
use App\Domain\Lead\ValueObject\ContactLead;
use App\Domain\Lead\ValueObject\ScoreLead;
use App\Domain\Lead\ValueObject\SourceTracking;
use App\Domain\Lead\ValueObject\StatutLead;
use App\Domain\Lead\ValueObject\TypeCapture;
use App\Infrastructure\Persistence\Doctrine\Entity\LeadEntity;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLeadRepository implements LeadRepository
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function save(Lead $lead): void
    {
        $entity = $this->em->find(LeadEntity::class, $lead->id()->value()) ?? new LeadEntity();

        $entity->id = $lead->id()->value();
        $entity->nom = $lead->contact()->nom;
        $entity->telephone = $lead->contact()->telephone;
        $entity->email = $lead->contact()->email;
        $entity->typeCapture = $lead->typeCapture()->value;
        $entity->score = $lead->score()->value;
        $entity->statut = $lead->statut()->value;
        $entity->estimationId = $lead->estimationId()?->value();
        $entity->utmSource = $lead->source()->utmSource;
        $entity->utmMedium = $lead->source()->utmMedium;
        $entity->utmCampaign = $lead->source()->utmCampaign;
        $entity->utmTerm = $lead->source()->utmTerm;
        $entity->utmContent = $lead->source()->utmContent;
        $entity->referrer = $lead->source()->referrer;
        $entity->landingPage = $lead->source()->landingPage;
        $entity->createdAt = $lead->createdAt();
        $entity->relancedAt = $lead->relancedAt();
        $entity->contactedAt = $lead->contactedAt();

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function findById(LeadId $id): ?Lead
    {
        $entity = $this->em->find(LeadEntity::class, $id->value());

        return $entity ? $this->toDomain($entity) : null;
    }

    public function nextId(): LeadId
    {
        return LeadId::generate();
    }

    public function findLeadsARelancer(DateTimeImmutable $avant): array
    {
        $entities = $this->em->createQueryBuilder()
            ->select('l')
            ->from(LeadEntity::class, 'l')
            ->where('l.statut = :statut')
            ->andWhere('l.relancedAt IS NULL')
            ->andWhere('l.createdAt < :avant')
            ->setParameter('statut', StatutLead::NOUVEAU->value)
            ->setParameter('avant', $avant)
            ->getQuery()
            ->getResult();

        return array_map(fn (LeadEntity $e) => $this->toDomain($e), $entities);
    }

    private function toDomain(LeadEntity $e): Lead
    {
        return Lead::reconstituer(
            id: LeadId::fromString($e->id),
            contact: new ContactLead($e->nom, $e->telephone, $e->email),
            typeCapture: TypeCapture::from($e->typeCapture),
            source: new SourceTracking(
                $e->utmSource,
                $e->utmMedium,
                $e->utmCampaign,
                $e->utmTerm,
                $e->utmContent,
                $e->referrer,
                $e->landingPage,
            ),
            score: ScoreLead::from($e->score),
            statut: StatutLead::from($e->statut),
            createdAt: $e->createdAt,
            estimationId: $e->estimationId ? EstimationId::fromString($e->estimationId) : null,
            relancedAt: $e->relancedAt,
            contactedAt: $e->contactedAt,
        );
    }
}
