<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240417084233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE2558A7A5');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE2558A7A5 FOREIGN KEY (credential_id) REFERENCES credential (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE2558A7A5');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE2558A7A5 FOREIGN KEY (credential_id) REFERENCES credential (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
