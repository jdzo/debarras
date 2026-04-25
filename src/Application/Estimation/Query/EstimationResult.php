<?php

declare(strict_types=1);

namespace App\Application\Estimation\Query;

final readonly class EstimationResult
{
    public function __construct(
        public string $id,
        public string $typeDeBien,
        public string $superficie,
        public string $encombrement,
        public string $salete,
        public string $accessibilite,
        public array $options,
        public array $coordonnees,
        public int $prixMin,
        public int $prixMax,
        public string $fourchette,
        public string $statut,
        public string $statutCouleur,
        public \DateTimeImmutable $createdAt,
        public ?string $commentaire,
        public array $photos,
        public string $accessToken,
    ) {
    }
}
