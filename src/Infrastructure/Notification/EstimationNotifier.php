<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Application\Estimation\Query\EstimationResult;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class EstimationNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $adminEmail,
        private readonly string $fromEmail,
    ) {
    }

    public function envoyerConfirmationClient(EstimationResult $estimation): void
    {
        $clientEmail = $estimation->coordonnees['email'];
        if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, 'ClearWay Débarras'))
            ->to($clientEmail)
            ->subject('Votre estimation de débarras - ' . $estimation->fourchette)
            ->htmlTemplate('email/confirmation_client.html.twig')
            ->context([
                'estimation' => $estimation,
            ]);

        $this->mailer->send($email);
    }

    public function notifierAdmin(EstimationResult $estimation): void
    {
        $safeNom = str_replace(["\r", "\n"], '', $estimation->coordonnees['nom']);

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, 'ClearWay Débarras'))
            ->to($this->adminEmail)
            ->subject('Nouveau lead - ' . $safeNom . ' - ' . $estimation->fourchette)
            ->htmlTemplate('email/notification_admin.html.twig')
            ->context([
                'estimation' => $estimation,
            ]);

        $this->mailer->send($email);
    }
}
