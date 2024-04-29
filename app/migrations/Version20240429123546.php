<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240429123546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add working column to notification_channel table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_channel ADD working TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_channel DROP working');
    }
}
