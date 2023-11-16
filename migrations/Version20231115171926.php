<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231115171926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE donations ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE donations ADD CONSTRAINT FK_CDE98962166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_CDE98962166D1F9C ON donations (project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE donations DROP FOREIGN KEY FK_CDE98962166D1F9C');
        $this->addSql('DROP INDEX IDX_CDE98962166D1F9C ON donations');
        $this->addSql('ALTER TABLE donations DROP project_id');
    }
}
