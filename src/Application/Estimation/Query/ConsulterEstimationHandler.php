<?php

declare(strict_types=1);

namespace App\Application\Estimation\Query;

use App\Domain\Estimation\EstimationId;
use App\Domain\Estimation\EstimationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ConsulterEstimationHandler
{
    public function __construct(
        private readonly EstimationRepository $repository,
    ) {
    }

    public function __invoke(ConsulterEstimationQuery $query): ?EstimationResult
    {
        $estimation = $this->repository->findById(
            EstimationId::fromString($query->estimationId)
        );

        if ($estimation === null) {
            return null;
        }

        return new EstimationResult(
            id: $estimation->id()->value(),
            typeDeBien: $estimation->typeDeBien()->label(),
            superficie: $estimation->superficie()->label(),
            encombrement: $estimation->encombrement()->label(),
            salete: $estimation->salete()->label(),
            accessibilite: $estimation->accessibilite()->label(),
            options: $estimation->options()->toArray(),
            coordonnees: $estimation->coordonnees()->toArray(),
            prixMin: $estimation->fourchette()->prixMin,
            prixMax: $estimation->fourchette()->prixMax,
            fourchette: $estimation->fourchette()->formatte(),
            statut: $estimation->statut()->label(),
            statutCouleur: $estimation->statut()->couleur(),
            createdAt: $estimation->createdAt(),
            commentaire: $estimation->commentaire(),
            photos: $estimation->photos(),
            accessToken: $estimation->accessToken(),
        );
    }
}
