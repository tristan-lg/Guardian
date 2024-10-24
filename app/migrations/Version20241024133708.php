<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024133708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audit (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', file_composer_json_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', file_composer_lock_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_9218FF7923D5770C (file_composer_json_id), INDEX IDX_9218FF79C63F9DE5 (file_composer_lock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', filename VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audit ADD CONSTRAINT FK_9218FF7923D5770C FOREIGN KEY (file_composer_json_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE audit ADD CONSTRAINT FK_9218FF79C63F9DE5 FOREIGN KEY (file_composer_lock_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE analysis ADD audit_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE project_id project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE analysis ADD CONSTRAINT FK_33C730BD29F359 FOREIGN KEY (audit_id) REFERENCES audit (id)');
        $this->addSql('CREATE INDEX IDX_33C730BD29F359 ON analysis (audit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analysis DROP FOREIGN KEY FK_33C730BD29F359');
        $this->addSql('ALTER TABLE audit DROP FOREIGN KEY FK_9218FF7923D5770C');
        $this->addSql('ALTER TABLE audit DROP FOREIGN KEY FK_9218FF79C63F9DE5');
        $this->addSql('DROP TABLE audit');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP INDEX IDX_33C730BD29F359 ON analysis');
        $this->addSql('ALTER TABLE analysis DROP audit_id, CHANGE project_id project_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
