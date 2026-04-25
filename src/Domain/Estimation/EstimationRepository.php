<?php

declare(strict_types=1);

namespace App\Domain\Estimation;

interface EstimationRepository
{
    public function save(Estimation $estimation): void;

    public function findById(EstimationId $id): ?Estimation;

    public function delete(Estimation $estimation): void;

    public function nextId(): EstimationId;
}
