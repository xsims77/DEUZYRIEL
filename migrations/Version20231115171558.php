<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231115171558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE donations (id INT AUTO_INCREMENT NOT NULL, moral_customer_id INT DEFAULT NULL, physical_customer_id INT DEFAULT NULL, donation_amount DOUBLE PRECISION NOT NULL, donation_currency VARCHAR(15) NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CDE9896282A33EB9 (moral_customer_id), INDEX IDX_CDE9896220604805 (physical_customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE donations ADD CONSTRAINT FK_CDE9896282A33EB9 FOREIGN KEY (moral_customer_id) REFERENCES moral_customers (id)');
        $this->addSql('ALTER TABLE donations ADD CONSTRAINT FK_CDE9896220604805 FOREIGN KEY (physical_customer_id) REFERENCES physical_customers (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE donations DROP FOREIGN KEY FK_CDE9896282A33EB9');
        $this->addSql('ALTER TABLE donations DROP FOREIGN KEY FK_CDE9896220604805');
        $this->addSql('DROP TABLE donations');
    }
}
