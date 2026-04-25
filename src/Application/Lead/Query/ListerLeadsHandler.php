<?php

declare(strict_types=1);

namespace App\Application\Lead\Query;

use App\Domain\Lead\ValueObject\ScoreLead;
use App\Domain\Lead\ValueObject\StatutLead;
use App\Domain\Lead\ValueObject\TypeCapture;
use App\Infrastructure\Persistence\Doctrine\Entity\LeadEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ListerLeadsHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return array{leads: LeadResult[], total: int, pages: int}
     */
    public function __invoke(ListerLeadsQuery $query): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('l')
            ->from(LeadEntity::class, 'l')
            ->orderBy('l.createdAt', 'DESC');

        $countQb = $this->em->createQueryBuilder()
            ->select('COUNT(l.id)')
            ->from(LeadEntity::class, 'l');

        if (null !== $query->statut && '' !== $query->statut) {
            $qb->andWhere('l.statut = :statut')->setParameter('statut', $query->statut);
            $countQb->andWhere('l.statut = :statut')->setParameter('statut', $query->statut);
        }

        if (null !== $query->score && '' !== $query->score) {
            $qb->andWhere('l.score = :score')->setParameter('score', $query->score);
            $countQb->andWhere('l.score = :score')->setParameter('score', $query->score);
        }

        if (null !== $query->recherche && '' !== $query->recherche) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query->recherche);
            $like = '%' . $escaped . '%';
            $where = 'l.nom LIKE :q OR l.email LIKE :q OR l.telephone LIKE :q';
            $qb->andWhere($where)->setParameter('q', $like);
            $countQb->andWhere($where)->setParameter('q', $like);
        }

        $total = (int) $countQb->getQuery()->getSingleScalarResult();
        $pages = max(1, (int) ceil($total / $query->limit));
        $page = max(1, min($query->page, $pages));
        $offset = ($page - 1) * $query->limit;

        $entities = $qb->setFirstResult($offset)->setMaxResults($query->limit)->getQuery()->getResult();

        return [
            'leads' => array_map(fn (LeadEntity $e) => $this->toResult($e), $entities),
            'total' => $total,
            'pages' => $pages,
        ];
    }

    private function toResult(LeadEntity $e): LeadResult
    {
        $scoreEnum = ScoreLead::from($e->score);
        $statutEnum = StatutLead::from($e->statut);
        $typeCaptureEnum = TypeCapture::from($e->typeCapture);

        return new LeadResult(
            id: $e->id,
            nom: $e->nom,
            telephone: $e->telephone,
            email: $e->email,
            typeCapture: $typeCaptureEnum->label(),
            score: $scoreEnum->label(),
            scoreCouleur: $scoreEnum->couleur(),
            statut: $statutEnum->label(),
            statutCouleur: $statutEnum->couleur(),
            estimationId: $e->estimationId,
            source: [
                'utm_source' => $e->utmSource,
                'utm_medium' => $e->utmMedium,
                'utm_campaign' => $e->utmCampaign,
            ],
            createdAt: $e->createdAt,
            relancedAt: $e->relancedAt,
            contactedAt: $e->contactedAt,
        );
    }
}
