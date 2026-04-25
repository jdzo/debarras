<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Application\Estimation\Query\EstimationResult;
use App\Infrastructure\Notification\EstimationNotifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EstimationNotifierTest extends TestCase
{
    private function createEstimationResult(): EstimationResult
    {
        return new EstimationResult(
            id: '550e8400-e29b-41d4-a716-446655440000',
            typeDeBien: 'Maison',
            superficie: '50 à 100 m²',
            encombrement: 'Meublé normalement',
            salete: 'Propre',
            accessibilite: 'Rez-de-chaussée',
            options: ['nettoyage' => true, 'desinfection' => false, 'demontage' => false],
            coordonnees: ['nom' => 'Jean Dupont', 'telephone' => '0612345678', 'email' => 'jean@example.com', 'adresse' => null, 'code_postal' => null, 'ville' => null],
            prixMin: 1200,
            prixMax: 1600,
            fourchette: '1200€ - 1600€',
            statut: 'Nouvelle',
            statutCouleur: 'info',
            createdAt: new \DateTimeImmutable(),
            commentaire: null,
            photos: [],
            accessToken: 'test-token',
        );
    }

    public function testEnvoyerConfirmationClient(): void
    {
        $sentEmails = [];
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Email $email) use (&$sentEmails) {
                $sentEmails[] = $email;
            });

        $notifier = new EstimationNotifier($mailer, 'admin@test.com', 'noreply@test.com');
        $notifier->envoyerConfirmationClient($this->createEstimationResult());

        $this->assertCount(1, $sentEmails);
        $this->assertStringContainsString('jean@example.com', $sentEmails[0]->getTo()[0]->getAddress());
    }

    public function testNotifierAdmin(): void
    {
        $sentEmails = [];
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Email $email) use (&$sentEmails) {
                $sentEmails[] = $email;
            });

        $notifier = new EstimationNotifier($mailer, 'admin@test.com', 'noreply@test.com');
        $notifier->notifierAdmin($this->createEstimationResult());

        $this->assertCount(1, $sentEmails);
        $this->assertStringContainsString('admin@test.com', $sentEmails[0]->getTo()[0]->getAddress());
    }
}
