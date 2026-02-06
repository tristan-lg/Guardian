<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260206084657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase version length in package table to 64 characters';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE package CHANGE required_version required_version VARCHAR(64) DEFAULT NULL, CHANGE installed_version installed_version VARCHAR(64) NOT NULL, CHANGE available_patch available_patch VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE package CHANGE required_version required_version VARCHAR(32) DEFAULT NULL, CHANGE installed_version installed_version VARCHAR(16) NOT NULL, CHANGE available_patch available_patch VARCHAR(16) DEFAULT NULL');
    }
}
