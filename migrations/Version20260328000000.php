<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260328000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add access_token column to estimation table for secure public access';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE estimation ADD COLUMN access_token VARCHAR(64) NOT NULL DEFAULT ''");
        $this->addSql("UPDATE estimation SET access_token = md5(random()::text) || md5(random()::text) WHERE access_token = ''");
        $this->addSql("ALTER TABLE estimation ALTER COLUMN access_token DROP DEFAULT");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE estimation DROP COLUMN access_token');
    }
}
