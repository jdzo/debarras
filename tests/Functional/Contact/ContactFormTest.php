<?php

declare(strict_types=1);

namespace App\Tests\Functional\Contact;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactFormTest extends WebTestCase
{
    public function testContactPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testSoumettreContactValide(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request('POST', '/contact', [
            '_token' => $token,
            'nom' => 'Jean Dupont',
            'telephone' => '0612345678',
            'email' => 'jean@test.fr',
            'message' => 'Je souhaite un devis.',
        ]);

        $this->assertResponseRedirects('/contact');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success, [class*="success"], [class*="alert"]', 'message a bien été envoyé');
    }

    public function testSoumettreContactSansNom(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $crawler = $client->request('POST', '/contact', [
            '_token' => $token,
            'nom' => '',
            'telephone' => '0612345678',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('nom est obligatoire', $crawler->text());
    }

    public function testSoumettreContactSansTelephone(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $crawler = $client->request('POST', '/contact', [
            '_token' => $token,
            'nom' => 'Jean Dupont',
            'telephone' => '',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('téléphone est obligatoire', $crawler->text());
    }

    public function testSoumettreContactEmailInvalide(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $crawler = $client->request('POST', '/contact', [
            '_token' => $token,
            'nom' => 'Jean Dupont',
            'telephone' => '0612345678',
            'email' => 'pas-un-email',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('email', $crawler->text());
    }

    public function testContactSansEmailEstValide(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request('POST', '/contact', [
            '_token' => $token,
            'nom' => 'Jean Dupont',
            'telephone' => '0612345678',
            'email' => '',
            'message' => '',
        ]);

        $this->assertResponseRedirects('/contact');
    }

    public function testContactSansTokenCsrf(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $client->request('POST', '/contact', [
            'nom' => 'Jean Dupont',
            'telephone' => '0612345678',
        ]);

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($statusCode, [401, 403], true), "Expected 401 or 403, got $statusCode");
    }
}
