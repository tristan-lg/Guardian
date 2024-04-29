<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240426133822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add expire_at column to credential table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credential ADD expire_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credential DROP expire_at');
    }
}
