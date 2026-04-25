<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\ChantierId;

final class ChantierDemarre
{
    public function __construct(public readonly ChantierId $id)
    {
    }
}
