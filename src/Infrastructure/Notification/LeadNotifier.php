<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final readonly class LeadNotifier
{
    public function __construct(
        private MailerInterface $mailer,
        private string $adminEmail,
        private string $fromEmail,
    ) {
    }

    public function notifierNouveauLead(string $nom, string $telephone, string $score, string $typeCapture): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, 'ClearWay Débarras'))
            ->to($this->adminEmail)
            ->subject(($score === 'Chaud' ? '[URGENT] ' : '') . 'Nouveau lead - ' . str_replace(["\r", "\n"], '', $nom) . ' (' . $score . ')')
            ->htmlTemplate('email/lead_admin.html.twig')
            ->context([
                'nom' => $nom,
                'telephone' => $telephone,
                'score' => $score,
                'type_capture' => $typeCapture,
            ]);

        $this->mailer->send($email);
    }

    public function envoyerRelance(string $nom, string $clientEmail): void
    {
        if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, 'ClearWay Débarras'))
            ->to($clientEmail)
            ->subject('Votre projet de débarras - Nous sommes disponibles')
            ->htmlTemplate('email/relance_lead.html.twig')
            ->context(['nom' => $nom]);

        $this->mailer->send($email);
    }
}
