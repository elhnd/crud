<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251220135109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subcategory ADD COLUMN documentation_url VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__subcategory AS SELECT id, name, description, category_id FROM subcategory');
        $this->addSql('DROP TABLE subcategory');
        $this->addSql('CREATE TABLE subcategory (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, category_id INTEGER NOT NULL, CONSTRAINT FK_DDCA44812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO subcategory (id, name, description, category_id) SELECT id, name, description, category_id FROM __temp__subcategory');
        $this->addSql('DROP TABLE __temp__subcategory');
        $this->addSql('CREATE INDEX IDX_DDCA44812469DE2 ON subcategory (category_id)');
    }
}
