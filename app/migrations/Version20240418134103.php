<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240418134103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add version_malformated column to package table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE package ADD version_malformated TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE package DROP version_malformated');
    }
}
