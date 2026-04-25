<?php

declare(strict_types=1);

namespace App\Application\Estimation\Query;

final readonly class ListerEstimationsQuery
{
    public function __construct(
        public ?string $statut = null,
        public ?string $recherche = null,
        public int $page = 1,
        public int $limit = 20,
    ) {
    }
}
