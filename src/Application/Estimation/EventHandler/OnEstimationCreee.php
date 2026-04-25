<?php

declare(strict_types=1);

namespace App\Application\Estimation\EventHandler;

use App\Application\Estimation\Query\ConsulterEstimationHandler;
use App\Application\Estimation\Query\ConsulterEstimationQuery;
use App\Domain\Estimation\Event\EstimationCreee;
use App\Infrastructure\Notification\EstimationNotifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class OnEstimationCreee
{
    public function __construct(
        private readonly ConsulterEstimationHandler $queryHandler,
        private readonly EstimationNotifier $notifier,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(EstimationCreee $event): void
    {
        $this->logger->info('Nouvelle estimation créée', [
            'estimation_id' => $event->estimationId->value(),
            'created_at' => $event->createdAt->format('Y-m-d H:i:s'),
        ]);

        $estimation = $this->queryHandler(
            new ConsulterEstimationQuery($event->estimationId->value())
        );

        if (null === $estimation) {
            $this->logger->error('Estimation introuvable pour notification', [
                'estimation_id' => $event->estimationId->value(),
            ]);

            return;
        }

        $this->notifier->envoyerConfirmationClient($estimation);
        $this->notifier->notifierAdmin($estimation);

        $this->logger->info('Notifications envoyées pour estimation', [
            'estimation_id' => $event->estimationId->value(),
        ]);
    }
}
