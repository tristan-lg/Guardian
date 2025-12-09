<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251201150953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow to disable notification channel on the fly';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_channel ADD active TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_channel DROP active');
    }
}
