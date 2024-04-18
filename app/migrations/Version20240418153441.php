<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418153441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE advisory (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', package_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', advisory_id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, link VARCHAR(1024) NOT NULL, cve VARCHAR(255) NOT NULL, affected_versions VARCHAR(255) NOT NULL, source VARCHAR(255) NOT NULL, reported_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', severity VARCHAR(32) NOT NULL, INDEX IDX_4112BDD9F44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE advisory ADD CONSTRAINT FK_4112BDD9F44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE advisory DROP FOREIGN KEY FK_4112BDD9F44CABFF');
        $this->addSql('DROP TABLE advisory');
    }
}
