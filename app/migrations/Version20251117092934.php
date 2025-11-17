<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251117092934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CredentialProject entity, modify Analysis platform field to be non-nullable, add updatedAt field to Credential entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE credential_project (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', credential_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', gitlab_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_1B9122422558A7A5 (credential_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE credential_project ADD CONSTRAINT FK_1B9122422558A7A5 FOREIGN KEY (credential_id) REFERENCES credential (id)');
        $this->addSql('ALTER TABLE analysis CHANGE platform platform JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE credential ADD updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credential_project DROP FOREIGN KEY FK_1B9122422558A7A5');
        $this->addSql('DROP TABLE credential_project');
        $this->addSql('ALTER TABLE credential DROP updated_at');
        $this->addSql('ALTER TABLE analysis CHANGE platform platform JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
