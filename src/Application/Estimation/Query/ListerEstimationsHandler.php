<?php

declare(strict_types=1);

namespace App\Application\Estimation\Query;

use App\Infrastructure\Persistence\Doctrine\Entity\EstimationEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ListerEstimationsHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return array{estimations: EstimationResult[], total: int, pages: int}
     */
    public function __invoke(ListerEstimationsQuery $query): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from(EstimationEntity::class, 'e')
            ->orderBy('e.createdAt', 'DESC');

        $countQb = $this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(EstimationEntity::class, 'e');

        if ($query->statut !== null && $query->statut !== '') {
            $qb->andWhere('e.statut = :statut')->setParameter('statut', $query->statut);
            $countQb->andWhere('e.statut = :statut')->setParameter('statut', $query->statut);
        }

        if ($query->recherche !== null && $query->recherche !== '') {
            $rechercheLike = '%' . $query->recherche . '%';
            $searchWhere = 'e.coordNom LIKE :recherche OR e.coordEmail LIKE :recherche OR e.coordTelephone LIKE :recherche';
            $qb->andWhere($searchWhere)->setParameter('recherche', $rechercheLike);
            $countQb->andWhere($searchWhere)->setParameter('recherche', $rechercheLike);
        }

        $total = (int) $countQb->getQuery()->getSingleScalarResult();
        $pages = max(1, (int) ceil($total / $query->limit));
        $page = max(1, min($query->page, $pages));

        $offset = ($page - 1) * $query->limit;
        $entities = $qb->setFirstResult($offset)->setMaxResults($query->limit)->getQuery()->getResult();

        $estimations = array_map(fn (EstimationEntity $e) => $this->toResult($e), $entities);

        return [
            'estimations' => $estimations,
            'total' => $total,
            'pages' => $pages,
        ];
    }

    private function toResult(EstimationEntity $e): EstimationResult
    {
        $statutEnum = \App\Domain\Estimation\ValueObject\StatutEstimation::from($e->statut);
        $typeDeBienEnum = \App\Domain\Estimation\ValueObject\TypeDeBien::from($e->typeDeBien);
        $superficieEnum = \App\Domain\Estimation\ValueObject\Superficie::from($e->superficie);
        $encombrementEnum = \App\Domain\Estimation\ValueObject\NiveauEncombrement::from($e->encombrement);
        $saleteEnum = \App\Domain\Estimation\ValueObject\NiveauSalete::from($e->salete);
        $accessibiliteEnum = \App\Domain\Estimation\ValueObject\Accessibilite::from($e->accessibilite);

        $fourchette = new \App\Domain\Estimation\ValueObject\FourchetteEstimation($e->prixMin, $e->prixMax);

        return new EstimationResult(
            id: $e->id,
            typeDeBien: $typeDeBienEnum->label(),
            superficie: $superficieEnum->label(),
            encombrement: $encombrementEnum->label(),
            salete: $saleteEnum->label(),
            accessibilite: $accessibiliteEnum->label(),
            options: [
                'nettoyage' => $e->optionNettoyage,
                'desinfection' => $e->optionDesinfection,
                'demontage' => $e->optionDemontage,
            ],
            coordonnees: [
                'nom' => $e->coordNom,
                'telephone' => $e->coordTelephone,
                'email' => $e->coordEmail,
                'adresse' => $e->coordAdresse,
                'code_postal' => $e->coordCodePostal,
                'ville' => $e->coordVille,
            ],
            prixMin: $e->prixMin,
            prixMax: $e->prixMax,
            fourchette: $fourchette->formatte(),
            statut: $statutEnum->label(),
            statutCouleur: $statutEnum->couleur(),
            createdAt: $e->createdAt,
            commentaire: $e->commentaire,
            photos: $e->photos,
            accessToken: $e->accessToken,
        );
    }
}
