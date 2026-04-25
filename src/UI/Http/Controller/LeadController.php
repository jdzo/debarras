<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use App\Application\Lead\Command\CreerLeadCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

class LeadController extends AbstractController
{
    #[Route('/rappel-gratuit', name: 'lead_rappel', methods: ['POST'])]
    public function rappelGratuit(Request $request, MessageBusInterface $bus, RateLimiterFactory $leadFormLimiter): Response
    {
        if (!$this->isCsrfTokenValid('lead', $request->request->getString('_token'))) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        $limiter = $leadFormLimiter->create($request->getClientIp() ?? 'anonymous');
        if (!$limiter->consume()->isAccepted()) {
            return new JsonResponse(['error' => 'Trop de tentatives'], 429);
        }

        $nom = mb_substr(trim($request->request->getString('nom')), 0, 100);
        $telephone = mb_substr(trim($request->request->getString('telephone')), 0, 20);
        $email = $request->request->getString('email') ?: null;
        if (null !== $email) {
            $email = mb_substr($email, 0, 180);
        }

        if ('' === $nom || '' === $telephone) {
            return new JsonResponse(['error' => 'Nom et téléphone obligatoires'], 400);
        }

        if (!preg_match('/^[\d\s\+\-\.()]{6,20}$/', $telephone)) {
            return new JsonResponse(['error' => 'Numéro de téléphone invalide'], 400);
        }

        $utm = $request->getSession()->get('utm_tracking', []);

        $bus->dispatch(new CreerLeadCommand(
            nom: $nom,
            telephone: $telephone,
            email: $email,
            typeCapture: 'rappel_gratuit',
            utmSource: $utm['utm_source'] ?? null,
            utmMedium: $utm['utm_medium'] ?? null,
            utmCampaign: $utm['utm_campaign'] ?? null,
            utmTerm: $utm['utm_term'] ?? null,
            utmContent: $utm['utm_content'] ?? null,
            referrer: $utm['referrer'] ?? null,
            landingPage: $utm['landing_page'] ?? null,
        ));

        return new JsonResponse(['success' => true]);
    }

    #[Route('/estimation-rapide', name: 'lead_estimation_rapide', methods: ['POST'])]
    public function estimationRapide(Request $request, MessageBusInterface $bus, RateLimiterFactory $leadFormLimiter): Response
    {
        if (!$this->isCsrfTokenValid('lead', $request->request->getString('_token'))) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        $limiter = $leadFormLimiter->create($request->getClientIp() ?? 'anonymous');
        if (!$limiter->consume()->isAccepted()) {
            return new JsonResponse(['error' => 'Trop de tentatives'], 429);
        }

        $nom = mb_substr(trim($request->request->getString('nom')), 0, 100);
        $telephone = mb_substr(trim($request->request->getString('telephone')), 0, 20);
        $email = $request->request->getString('email') ?: null;
        if (null !== $email) {
            $email = mb_substr($email, 0, 180);
        }

        if ('' === $nom || '' === $telephone) {
            return new JsonResponse(['error' => 'Nom et téléphone obligatoires'], 400);
        }

        if (!preg_match('/^[\d\s\+\-\.()]{6,20}$/', $telephone)) {
            return new JsonResponse(['error' => 'Numéro de téléphone invalide'], 400);
        }

        $utm = $request->getSession()->get('utm_tracking', []);

        $bus->dispatch(new CreerLeadCommand(
            nom: $nom,
            telephone: $telephone,
            email: $email,
            typeCapture: 'estimation_rapide',
            typeDeBien: $request->request->getString('type_de_bien') ?: null,
            superficie: $request->request->getString('superficie') ?: null,
            utmSource: $utm['utm_source'] ?? null,
            utmMedium: $utm['utm_medium'] ?? null,
            utmCampaign: $utm['utm_campaign'] ?? null,
            utmTerm: $utm['utm_term'] ?? null,
            utmContent: $utm['utm_content'] ?? null,
            referrer: $utm['referrer'] ?? null,
            landingPage: $utm['landing_page'] ?? null,
        ));

        return new JsonResponse(['success' => true]);
    }
}
