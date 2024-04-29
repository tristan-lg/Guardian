<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240424144655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add analysis_id to advisory table and advisory_hash to analysis table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE advisory ADD analysis_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE advisory ADD CONSTRAINT FK_4112BDD97941003F FOREIGN KEY (analysis_id) REFERENCES analysis (id)');
        $this->addSql('CREATE INDEX IDX_4112BDD97941003F ON advisory (analysis_id)');
        $this->addSql('ALTER TABLE analysis ADD advisory_hash VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE analysis DROP advisory_hash');
        $this->addSql('ALTER TABLE advisory DROP FOREIGN KEY FK_4112BDD97941003F');
        $this->addSql('DROP INDEX IDX_4112BDD97941003F ON advisory');
        $this->addSql('ALTER TABLE advisory DROP analysis_id');
    }
}
