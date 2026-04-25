<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use App\Application\Estimation\Command\CreerEstimationCommand;
use App\Application\Estimation\Query\ConsulterEstimationHandler;
use App\Application\Estimation\Query\ConsulterEstimationQuery;
use App\Domain\Estimation\Service\CalculateurPrix;
use App\Domain\Estimation\ValueObject\Accessibilite;
use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Options;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Estimation\ValueObject\TypeDeBien;
use App\Domain\Estimation\ValueObject\ZoneTarifaire;
use App\Infrastructure\Upload\PhotoUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

class EstimationController extends AbstractController
{
    #[Route('/estimation', name: 'estimation_formulaire', methods: ['GET'])]
    public function formulaire(): Response
    {
        return $this->render('estimation/formulaire.html.twig', [
            'types_de_bien' => TypeDeBien::cases(),
            'superficies' => Superficie::cases(),
            'niveaux_encombrement' => NiveauEncombrement::cases(),
            'niveaux_salete' => NiveauSalete::cases(),
            'accessibilites' => Accessibilite::cases(),
        ]);
    }

    #[Route('/estimation', name: 'estimation_soumettre', methods: ['POST'])]
    public function soumettre(
        Request $request,
        MessageBusInterface $bus,
        PhotoUploader $photoUploader,
        RateLimiterFactory $estimationFormLimiter,
    ): Response {
        if (!$this->isCsrfTokenValid('estimation', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $limiter = $estimationFormLimiter->create($request->getClientIp() ?? 'anonymous');
        if (!$limiter->consume()->isAccepted()) {
            $this->addFlash('error', 'Trop de tentatives. Veuillez réessayer dans quelques minutes.');

            return $this->redirectToRoute('estimation_formulaire');
        }

        $uploadedFiles = array_slice($request->files->all('photos') ?? [], 0, 5);
        $estimationIdForUpload = bin2hex(random_bytes(16));
        $photos = $photoUploader->upload($uploadedFiles, $estimationIdForUpload);

        $utm = $request->getSession()->get('utm_tracking', []);

        $command = new CreerEstimationCommand(
            typeDeBien: $request->request->getString('type_de_bien'),
            superficie: $request->request->getString('superficie'),
            encombrement: $request->request->getString('encombrement'),
            salete: $request->request->getString('salete'),
            accessibilite: $request->request->getString('accessibilite'),
            optionNettoyage: $request->request->getBoolean('option_nettoyage'),
            optionDesinfection: $request->request->getBoolean('option_desinfection'),
            optionDemontage: $request->request->getBoolean('option_demontage'),
            nom: mb_substr($request->request->getString('nom'), 0, 100),
            telephone: mb_substr($request->request->getString('telephone'), 0, 20),
            email: mb_substr($request->request->getString('email'), 0, 180),
            adresse: null !== $request->request->get('adresse') ? mb_substr($request->request->get('adresse'), 0, 255) : null,
            codePostal: null !== $request->request->get('code_postal') ? mb_substr($request->request->get('code_postal'), 0, 10) : null,
            ville: null !== $request->request->get('ville') ? mb_substr($request->request->get('ville'), 0, 100) : null,
            commentaire: null !== $request->request->get('commentaire') ? mb_substr($request->request->get('commentaire'), 0, 2000) : null,
            photos: $photos,
            utmSource: $utm['utm_source'] ?? null,
            utmMedium: $utm['utm_medium'] ?? null,
            utmCampaign: $utm['utm_campaign'] ?? null,
            utmTerm: $utm['utm_term'] ?? null,
            utmContent: $utm['utm_content'] ?? null,
            referrer: $utm['referrer'] ?? null,
            landingPage: $utm['landing_page'] ?? null,
        );

        $envelope = $bus->dispatch($command);
        $handledStamp = $envelope->last(HandledStamp::class);
        $result = $handledStamp->getResult();

        return $this->redirectToRoute('estimation_resultat', [
            'id' => $result['id'],
            'token' => $result['accessToken'],
        ]);
    }

    #[Route('/estimation/{id}', name: 'estimation_resultat', methods: ['GET'])]
    public function resultat(string $id, Request $request, ConsulterEstimationHandler $handler): Response
    {
        $result = $handler(new ConsulterEstimationQuery($id));

        if (null === $result) {
            throw $this->createNotFoundException('Estimation introuvable');
        }

        $token = $request->query->getString('token');
        if (!hash_equals($result->accessToken, $token)) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('estimation/resultat.html.twig', [
            'estimation' => $result,
        ]);
    }

    #[Route('/estimation/apercu', name: 'estimation_apercu', methods: ['POST'])]
    public function apercu(Request $request, CalculateurPrix $calculateur): Response
    {
        $superficie = Superficie::tryFrom($request->request->getString('superficie'));
        $encombrement = NiveauEncombrement::tryFrom($request->request->getString('encombrement'));
        $salete = NiveauSalete::tryFrom($request->request->getString('salete'));
        $accessibilite = Accessibilite::tryFrom($request->request->getString('accessibilite'));

        if (!$superficie || !$encombrement || !$salete || !$accessibilite) {
            return $this->json(['error' => 'Données incomplètes'], 400);
        }

        $options = new Options(
            nettoyage: $request->request->getBoolean('option_nettoyage'),
            desinfection: $request->request->getBoolean('option_desinfection'),
            demontage: $request->request->getBoolean('option_demontage'),
        );

        $zone = ZoneTarifaire::fromCodePostal($request->request->getString('code_postal') ?: null);
        $fourchette = $calculateur->calculer($superficie, $encombrement, $salete, $accessibilite, $options, $zone);

        return $this->json([
            'prix_min' => $fourchette->prixMin,
            'prix_max' => $fourchette->prixMax,
            'formatte' => $fourchette->formatte(),
        ]);
    }
}
