<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lead table for lead generation tracking';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lead (
            id UUID NOT NULL,
            nom VARCHAR(255) NOT NULL,
            telephone VARCHAR(30) NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            type_capture VARCHAR(30) NOT NULL,
            score VARCHAR(10) NOT NULL,
            statut VARCHAR(20) NOT NULL,
            estimation_id UUID DEFAULT NULL,
            utm_source VARCHAR(100) DEFAULT NULL,
            utm_medium VARCHAR(100) DEFAULT NULL,
            utm_campaign VARCHAR(100) DEFAULT NULL,
            utm_term VARCHAR(100) DEFAULT NULL,
            utm_content VARCHAR(100) DEFAULT NULL,
            referrer VARCHAR(500) DEFAULT NULL,
            landing_page VARCHAR(500) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            relanced_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            contacted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_lead_statut_created ON lead (statut, created_at)');
        $this->addSql('CREATE INDEX idx_lead_score ON lead (score)');
        $this->addSql('COMMENT ON COLUMN lead.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN lead.relanced_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN lead.contacted_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lead');
    }
}
