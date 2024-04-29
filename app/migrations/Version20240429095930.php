<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240429095930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add last_notification column to credential table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credential ADD last_notification DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credential DROP last_notification');
    }
}
