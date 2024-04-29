<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240419151432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove cve column from package table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE package DROP cve');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE package ADD cve VARCHAR(255) DEFAULT NULL');
    }
}
