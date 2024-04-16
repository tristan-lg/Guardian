<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416143028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE credential (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', domain VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, access_token VARCHAR(512) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project ADD credential_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE2558A7A5 FOREIGN KEY (credential_id) REFERENCES credential (id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE2558A7A5 ON project (credential_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE2558A7A5');
        $this->addSql('DROP TABLE credential');
        $this->addSql('DROP INDEX IDX_2FB3D0EE2558A7A5 ON project');
        $this->addSql('ALTER TABLE project DROP credential_id');
    }
}
