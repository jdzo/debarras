<?php

declare(strict_types=1);

namespace App\Application\Estimation\Command;

use App\Domain\Estimation\Estimation;
use App\Domain\Estimation\EstimationRepository;
use App\Domain\Estimation\Service\CalculateurPrix;
use App\Domain\Estimation\ValueObject\Accessibilite;
use App\Domain\Estimation\ValueObject\Coordonnees;
use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Options;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Estimation\ValueObject\TypeDeBien;
use App\Domain\Lead\Lead;
use App\Domain\Lead\LeadRepository;
use App\Domain\Lead\Service\ScoringLead;
use App\Domain\Lead\ValueObject\ContactLead;
use App\Domain\Lead\ValueObject\SourceTracking;
use App\Domain\Lead\ValueObject\TypeCapture;
use App\Domain\Shared\MessageBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreerEstimationHandler
{
    public function __construct(
        private readonly EstimationRepository $repository,
        private readonly LeadRepository $leadRepository,
        private readonly CalculateurPrix $calculateur,
        private readonly ScoringLead $scoring,
        private readonly MessageBus $messageBus,
    ) {
    }

    /**
     * @return array{id: string, accessToken: string}
     */
    public function __invoke(CreerEstimationCommand $command): array
    {
        $estimation = Estimation::creer(
            id: $this->repository->nextId(),
            typeDeBien: TypeDeBien::from($command->typeDeBien),
            superficie: Superficie::from($command->superficie),
            encombrement: NiveauEncombrement::from($command->encombrement),
            salete: NiveauSalete::from($command->salete),
            accessibilite: Accessibilite::from($command->accessibilite),
            options: new Options(
                nettoyage: $command->optionNettoyage,
                desinfection: $command->optionDesinfection,
                demontage: $command->optionDemontage,
            ),
            coordonnees: Coordonnees::create(
                nom: $command->nom,
                telephone: $command->telephone,
                email: $command->email,
                adresse: $command->adresse,
                codePostal: $command->codePostal,
                ville: $command->ville,
            ),
            calculateur: $this->calculateur,
            commentaire: $command->commentaire,
            photos: $command->photos,
        );

        $this->repository->save($estimation);

        $this->creerLeadDepuisEstimation($command, $estimation);

        foreach ($estimation->pullDomainEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        return [
            'id' => $estimation->id()->value(),
            'accessToken' => $estimation->accessToken(),
        ];
    }

    private function creerLeadDepuisEstimation(CreerEstimationCommand $command, Estimation $estimation): void
    {
        $score = $this->scoring->scorer(
            typeCapture: TypeCapture::ESTIMATION_COMPLETE,
            salete: NiveauSalete::tryFrom($command->salete),
            superficie: Superficie::tryFrom($command->superficie),
            encombrement: NiveauEncombrement::tryFrom($command->encombrement),
            prixEstime: $estimation->fourchette()->prixMin,
        );

        $lead = Lead::creer(
            id: $this->leadRepository->nextId(),
            contact: new ContactLead($command->nom, $command->telephone, $command->email ?: null),
            typeCapture: TypeCapture::ESTIMATION_COMPLETE,
            source: new SourceTracking(
                $command->utmSource, $command->utmMedium, $command->utmCampaign,
                $command->utmTerm, $command->utmContent, $command->referrer, $command->landingPage,
            ),
            score: $score,
            estimationId: $estimation->id(),
        );

        $this->leadRepository->save($lead);

        foreach ($lead->pullDomainEvents() as $event) {
            $this->messageBus->dispatch($event);
        }
    }
}
