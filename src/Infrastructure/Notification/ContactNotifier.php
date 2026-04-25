<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final readonly class ContactNotifier
{
    public function __construct(
        private MailerInterface $mailer,
        private string $adminEmail,
        private string $fromEmail,
    ) {
    }

    public function notifierAdmin(string $nom, string $telephone, string $email, string $message): void
    {
        $safeNom = str_replace(["\r", "\n"], '', $nom);

        $emailMessage = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, 'ClearWay Débarras'))
            ->to($this->adminEmail)
            ->subject('Nouveau message de contact - ' . $safeNom)
            ->htmlTemplate('email/contact_admin.html.twig')
            ->context([
                'nom' => $nom,
                'telephone' => $telephone,
                'contact_email' => $email,
                'message' => $message,
            ]);

        $this->mailer->send($emailMessage);
    }
}
