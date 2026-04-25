<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Estimation\Estimation;
use App\Domain\Estimation\EstimationId;
use App\Domain\Estimation\EstimationRepository;
use App\Domain\Estimation\ValueObject\Accessibilite;
use App\Domain\Estimation\ValueObject\Coordonnees;
use App\Domain\Estimation\ValueObject\FourchetteEstimation;
use App\Domain\Estimation\ValueObject\NiveauEncombrement;
use App\Domain\Estimation\ValueObject\NiveauSalete;
use App\Domain\Estimation\ValueObject\Options;
use App\Domain\Estimation\ValueObject\StatutEstimation;
use App\Domain\Estimation\ValueObject\Superficie;
use App\Domain\Estimation\ValueObject\TypeDeBien;
use App\Infrastructure\Persistence\Doctrine\Entity\EstimationEntity;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineEstimationRepository implements EstimationRepository
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function save(Estimation $estimation): void
    {
        $entity = $this->em->find(EstimationEntity::class, $estimation->id()->value());

        if (null === $entity) {
            $entity = new EstimationEntity();
        }

        $this->mapToEntity($estimation, $entity);

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function findById(EstimationId $id): ?Estimation
    {
        $entity = $this->em->find(EstimationEntity::class, $id->value());

        if (null === $entity) {
            return null;
        }

        return $this->mapToDomain($entity);
    }

    public function delete(Estimation $estimation): void
    {
        $entity = $this->em->find(EstimationEntity::class, $estimation->id()->value());

        if (null !== $entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }
    }

    public function nextId(): EstimationId
    {
        return EstimationId::generate();
    }

    private function mapToEntity(Estimation $estimation, EstimationEntity $entity): void
    {
        $entity->id = $estimation->id()->value();
        $entity->typeDeBien = $estimation->typeDeBien()->value;
        $entity->superficie = $estimation->superficie()->value;
        $entity->encombrement = $estimation->encombrement()->value;
        $entity->salete = $estimation->salete()->value;
        $entity->accessibilite = $estimation->accessibilite()->value;
        $entity->optionNettoyage = $estimation->options()->nettoyage;
        $entity->optionDesinfection = $estimation->options()->desinfection;
        $entity->optionDemontage = $estimation->options()->demontage;
        $entity->coordNom = $estimation->coordonnees()->nom;
        $entity->coordTelephone = $estimation->coordonnees()->telephone;
        $entity->coordEmail = $estimation->coordonnees()->email;
        $entity->coordAdresse = $estimation->coordonnees()->adresse;
        $entity->coordCodePostal = $estimation->coordonnees()->codePostal;
        $entity->coordVille = $estimation->coordonnees()->ville;
        $entity->prixMin = $estimation->fourchette()->prixMin;
        $entity->prixMax = $estimation->fourchette()->prixMax;
        $entity->statut = $estimation->statut()->value;
        $entity->accessToken = $estimation->accessToken();
        $entity->createdAt = $estimation->createdAt();
        $entity->commentaire = $estimation->commentaire();
        $entity->photos = $estimation->photos();
    }

    private function mapToDomain(EstimationEntity $entity): Estimation
    {
        $coordonnees = new Coordonnees(
            nom: $entity->coordNom,
            telephone: $entity->coordTelephone,
            email: $entity->coordEmail,
            adresse: $entity->coordAdresse,
            codePostal: $entity->coordCodePostal,
            ville: $entity->coordVille,
        );

        $options = new Options(
            nettoyage: $entity->optionNettoyage,
            desinfection: $entity->optionDesinfection,
            demontage: $entity->optionDemontage,
        );

        $fourchette = new FourchetteEstimation(
            prixMin: $entity->prixMin,
            prixMax: $entity->prixMax,
        );

        return Estimation::reconstituer(
            id: EstimationId::fromString($entity->id),
            typeDeBien: TypeDeBien::from($entity->typeDeBien),
            superficie: Superficie::from($entity->superficie),
            encombrement: NiveauEncombrement::from($entity->encombrement),
            salete: NiveauSalete::from($entity->salete),
            accessibilite: Accessibilite::from($entity->accessibilite),
            options: $options,
            coordonnees: $coordonnees,
            fourchette: $fourchette,
            statut: StatutEstimation::from($entity->statut),
            createdAt: $entity->createdAt,
            accessToken: $entity->accessToken,
            commentaire: $entity->commentaire,
            photos: $entity->photos,
        );
    }
}
