<?php

declare(strict_types=1);

namespace App\Tests\Functional\Lead;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LeadCaptureTest extends WebTestCase
{
    public function testRappelGratuitValide(): void
    {
        $client = static::createClient();
        $token = $this->getCsrfToken($client);

        $client->request('POST', '/rappel-gratuit', [
            'nom' => 'Jean Test',
            'telephone' => '0612345678',
            '_token' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function testRappelGratuitAvecEmail(): void
    {
        $client = static::createClient();
        $token = $this->getCsrfToken($client);

        $client->request('POST', '/rappel-gratuit', [
            'nom' => 'Marie Test',
            'telephone' => '0698765432',
            'email' => 'marie@test.fr',
            '_token' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function testRappelGratuitSansNom(): void
    {
        $client = static::createClient();
        $token = $this->getCsrfToken($client);

        $client->request('POST', '/rappel-gratuit', [
            'nom' => '',
            'telephone' => '0612345678',
            '_token' => $token,
        ]);

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testRappelGratuitSansTelephone(): void
    {
        $client = static::createClient();
        $token = $this->getCsrfToken($client);

        $client->request('POST', '/rappel-gratuit', [
            'nom' => 'Jean Test',
            'telephone' => '',
            '_token' => $token,
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRappelGratuitSansCsrf(): void
    {
        $client = static::createClient();

        $client->request('POST', '/rappel-gratuit', [
            'nom' => 'Jean Test',
            'telephone' => '0612345678',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEstimationRapideValide(): void
    {
        $client = static::createClient();
        $token = $this->getCsrfToken($client);

        $client->request('POST', '/estimation-rapide', [
            'nom' => 'Pierre Test',
            'telephone' => '0611223344',
            'email' => 'pierre@test.fr',
            'type_de_bien' => 'maison',
            'superficie' => '50_100',
            '_token' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function testEstimationRapideInvalide(): void
    {
        $client = static::createClient();
        $token = $this->getCsrfToken($client);

        $client->request('POST', '/estimation-rapide', [
            'nom' => '',
            'telephone' => '',
            '_token' => $token,
        ]);

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testEstimationRapideSansCsrf(): void
    {
        $client = static::createClient();

        $client->request('POST', '/estimation-rapide', [
            'nom' => 'Pierre Test',
            'telephone' => '0611223344',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    private function getCsrfToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        $crawler = $client->request('GET', '/');

        return $crawler->filter('input[name="_token"]')->attr('value');
    }
}
