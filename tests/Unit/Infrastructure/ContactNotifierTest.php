<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Infrastructure\Notification\ContactNotifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactNotifierTest extends TestCase
{
    public function testNotifierAdminSendsEmailToAdmin(): void
    {
        $sentEmails = [];
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(static function (Email $email) use (&$sentEmails): void {
                $sentEmails[] = $email;
            });

        $notifier = new ContactNotifier($mailer, 'admin@test.com', 'noreply@test.com');
        $notifier->notifierAdmin('Jean Dupont', '0612345678', 'jean@example.com', 'Je souhaite un devis.');

        $this->assertCount(1, $sentEmails);
        $this->assertSame('admin@test.com', $sentEmails[0]->getTo()[0]->getAddress());
        $this->assertStringContainsString('Jean Dupont', $sentEmails[0]->getSubject());
    }
}
