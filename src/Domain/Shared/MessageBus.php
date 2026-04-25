<?php

declare(strict_types=1);

namespace App\Domain\Shared;

interface MessageBus
{
    public function dispatch(object $message): void;
}
