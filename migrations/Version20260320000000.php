<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260320000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create estimation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE estimation (
            id UUID NOT NULL,
            type_de_bien VARCHAR(30) NOT NULL,
            superficie VARCHAR(20) NOT NULL,
            encombrement VARCHAR(30) NOT NULL,
            salete VARCHAR(20) NOT NULL,
            accessibilite VARCHAR(30) NOT NULL,
            option_nettoyage BOOLEAN NOT NULL DEFAULT FALSE,
            option_desinfection BOOLEAN NOT NULL DEFAULT FALSE,
            option_demontage BOOLEAN NOT NULL DEFAULT FALSE,
            coord_nom VARCHAR(255) NOT NULL,
            coord_telephone VARCHAR(30) NOT NULL,
            coord_email VARCHAR(255) NOT NULL,
            coord_adresse VARCHAR(255) DEFAULT NULL,
            coord_code_postal VARCHAR(10) DEFAULT NULL,
            coord_ville VARCHAR(100) DEFAULT NULL,
            prix_min INT NOT NULL,
            prix_max INT NOT NULL,
            statut VARCHAR(20) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            commentaire TEXT DEFAULT NULL,
            photos JSON NOT NULL DEFAULT \'[]\',
            PRIMARY KEY(id)
        )');

        $this->addSql('COMMENT ON COLUMN estimation.created_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('CREATE INDEX idx_estimation_statut ON estimation (statut)');
        $this->addSql('CREATE INDEX idx_estimation_created_at ON estimation (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE estimation');
    }
}
