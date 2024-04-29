<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240423131937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notification_channel table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification_channel (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(255) NOT NULL, value VARCHAR(1024) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notification_channel');
    }
}
