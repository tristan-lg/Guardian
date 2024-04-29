<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240422120315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cve_count column to analysis table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE analysis ADD cve_count INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE analysis DROP cve_count');
    }
}
