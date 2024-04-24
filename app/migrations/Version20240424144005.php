<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240424144005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE advisory ADD advisory_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE advisory ADD CONSTRAINT FK_4112BDD97941003F FOREIGN KEY (analysis_id) REFERENCES analysis (id)');
        $this->addSql('CREATE INDEX IDX_4112BDD97941003F ON advisory (analysis_id)');
        $this->addSql('ALTER TABLE analysis ADD advisory_hash VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE advisory DROP FOREIGN KEY FK_4112BDD97941003F');
        $this->addSql('DROP INDEX IDX_4112BDD97941003F ON advisory');
        $this->addSql('ALTER TABLE advisory DROP advisory_id');
        $this->addSql('ALTER TABLE analysis DROP advisory_hash');
    }
}
