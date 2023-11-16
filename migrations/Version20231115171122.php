<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231115171122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE moral_customers (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, company_name VARCHAR(255) NOT NULL, company_type VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, zip VARCHAR(20) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_4C7E65CAE7927C74 (email), INDEX IDX_4C7E65CA32C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE moral_customers ADD CONSTRAINT FK_4C7E65CA32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A742BACE7927C74 ON physical_customers (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moral_customers DROP FOREIGN KEY FK_4C7E65CA32C8A3DE');
        $this->addSql('DROP TABLE moral_customers');
        $this->addSql('DROP INDEX UNIQ_9A742BACE7927C74 ON physical_customers');
    }
}
