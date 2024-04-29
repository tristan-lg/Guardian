<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240425123526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase size of advisory columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE advisory CHANGE title title VARCHAR(1024) NOT NULL, CHANGE affected_versions affected_versions VARCHAR(2048) NOT NULL, CHANGE source source VARCHAR(1024) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE advisory CHANGE title title VARCHAR(255) NOT NULL, CHANGE affected_versions affected_versions VARCHAR(255) NOT NULL, CHANGE source source VARCHAR(255) NOT NULL');
    }
}
