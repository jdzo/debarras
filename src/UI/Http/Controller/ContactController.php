<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use App\Infrastructure\Notification\ContactNotifier;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    #[Route('/contact', name: 'contact_envoyer', methods: ['POST'])]
    public function envoyer(
        Request $request,
        LoggerInterface $logger,
        ContactNotifier $contactNotifier,
        RateLimiterFactory $contactFormLimiter,
    ): Response {
        if (!$this->isCsrfTokenValid('contact', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $limiter = $contactFormLimiter->create($request->getClientIp() ?? 'anonymous');
        if (!$limiter->consume()->isAccepted()) {
            $this->addFlash('success', 'Trop de tentatives. Veuillez réessayer dans quelques minutes.');
            return $this->redirectToRoute('contact');
        }

        $nom = trim($request->request->getString('nom'));
        $telephone = trim($request->request->getString('telephone'));
        $email = trim($request->request->getString('email'));
        $message = trim($request->request->getString('message'));

        $nom = mb_substr($nom, 0, 100);
        $telephone = mb_substr($telephone, 0, 20);
        $email = mb_substr($email, 0, 180);
        $message = mb_substr($message, 0, 2000);

        $errors = [];
        if ($nom === '') {
            $errors[] = 'Le nom est obligatoire.';
        }
        if ($telephone === '') {
            $errors[] = 'Le téléphone est obligatoire.';
        }
        if ($telephone !== '' && !preg_match('/^[\d\s\+\-\.()]{6,20}$/', $telephone)) {
            $errors[] = 'Le format du téléphone n\'est pas valide.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email n\'est pas valide.';
        }

        if ($errors !== []) {
            return $this->render('contact/index.html.twig', [
                'errors' => $errors,
                'old' => ['nom' => $nom, 'telephone' => $telephone, 'email' => $email, 'message' => $message],
            ]);
        }

        $logger->info('Nouveau message de contact reçu');

        $contactNotifier->notifierAdmin($nom, $telephone, $email, $message);

        $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous recontacterons rapidement.');

        return $this->redirectToRoute('contact');
    }
}
