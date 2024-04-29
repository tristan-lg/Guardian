<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240424090305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add avatar_url column to project table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD git_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project DROP git_url');
    }
}
