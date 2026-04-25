<?php

namespace App\Infrastructure\Messaging;

use     App\Domain\Shared\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyMessageBus implements MessageBus
{
    public function __construct(private MessageBusInterface $bus) {}

    public function dispatch(object $message): void
    {
        $this->bus->dispatch($message);
    }
}
