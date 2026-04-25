<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Domain\Lead\LeadRepository;
use App\Infrastructure\Notification\LeadNotifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:leads:relancer', description: 'Relance les leads sans réponse après 24h')]
final class RelancerLeadsCommand extends Command
{
    public function __construct(
        private readonly LeadRepository $repository,
        private readonly LeadNotifier $notifier,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $avant24h = new \DateTimeImmutable('-24 hours');
        $leads = $this->repository->findLeadsARelancer($avant24h);

        $count = 0;
        foreach ($leads as $lead) {
            $contact = $lead->contact();

            if ($contact->email !== null) {
                $this->notifier->envoyerRelance($contact->nom, $contact->email);
            }

            $lead->enregistrerRelance();
            $this->repository->save($lead);
            $count++;
        }

        $this->logger->info("Relance terminée", ['leads_relancés' => $count]);
        $output->writeln(sprintf('%d lead(s) relancé(s).', $count));

        return Command::SUCCESS;
    }
}
