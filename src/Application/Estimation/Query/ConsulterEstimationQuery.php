<?php

declare(strict_types=1);

namespace App\Application\Estimation\Query;

final readonly class ConsulterEstimationQuery
{
    public function __construct(
        public string $estimationId,
    ) {
    }
}
