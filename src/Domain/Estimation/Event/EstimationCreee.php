<?php

declare(strict_types=1);

namespace App\Domain\Estimation\Event;

use App\Domain\Estimation\EstimationId;

final readonly class EstimationCreee
{
    public function __construct(
        public EstimationId $estimationId,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
