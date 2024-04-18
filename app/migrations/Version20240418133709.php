<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418133709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE analysis (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', project_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', run_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', grade VARCHAR(8) NOT NULL, end_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_33C730166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE package (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', analysis_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, required_version VARCHAR(32) DEFAULT NULL, installed_version VARCHAR(16) NOT NULL, available_patch VARCHAR(16) DEFAULT NULL, cve VARCHAR(255) DEFAULT NULL, sub_dependency TINYINT(1) NOT NULL, INDEX IDX_DE6867957941003F (analysis_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE analysis ADD CONSTRAINT FK_33C730166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE package ADD CONSTRAINT FK_DE6867957941003F FOREIGN KEY (analysis_id) REFERENCES analysis (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analysis DROP FOREIGN KEY FK_33C730166D1F9C');
        $this->addSql('ALTER TABLE package DROP FOREIGN KEY FK_DE6867957941003F');
        $this->addSql('DROP TABLE analysis');
        $this->addSql('DROP TABLE package');
    }
}
