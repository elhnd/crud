<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223121211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question ADD COLUMN symfony_version VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__question AS SELECT id, identifier, text, explanation, resource_url, type, difficulty, created_at, updated_at, is_certification, is_active, category_id, subcategory_id FROM question');
        $this->addSql('DROP TABLE question');
        $this->addSql('CREATE TABLE question (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, identifier VARCHAR(64) DEFAULT NULL, text CLOB NOT NULL, explanation CLOB DEFAULT NULL, resource_url CLOB DEFAULT NULL, type VARCHAR(20) NOT NULL, difficulty INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, is_certification BOOLEAN DEFAULT 0 NOT NULL, is_active BOOLEAN DEFAULT 1 NOT NULL, category_id INTEGER NOT NULL, subcategory_id INTEGER NOT NULL, CONSTRAINT FK_B6F7494E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B6F7494E5DC6FE57 FOREIGN KEY (subcategory_id) REFERENCES subcategory (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO question (id, identifier, text, explanation, resource_url, type, difficulty, created_at, updated_at, is_certification, is_active, category_id, subcategory_id) SELECT id, identifier, text, explanation, resource_url, type, difficulty, created_at, updated_at, is_certification, is_active, category_id, subcategory_id FROM __temp__question');
        $this->addSql('DROP TABLE __temp__question');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6F7494E772E836A ON question (identifier)');
        $this->addSql('CREATE INDEX IDX_B6F7494E12469DE2 ON question (category_id)');
        $this->addSql('CREATE INDEX IDX_B6F7494E5DC6FE57 ON question (subcategory_id)');
        $this->addSql('CREATE INDEX idx_question_identifier ON question (identifier)');
    }
}
