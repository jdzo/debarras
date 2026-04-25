<?php

namespace App\Domain\Event;

use App\Domain\ChantierId;

final class ChantierDemarre
{
    public function __construct(public readonly ChantierId $id)
    {

    }
}
