<?php

namespace App\Domain\Shared;

interface MessageBus
{
    public function dispatch(object $message): void;
}
