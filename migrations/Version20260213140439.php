<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213140439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__question_explanation AS SELECT id, content, locale, generated_at, updated_at, model_used, tokens_used, question_id FROM question_explanation');
        $this->addSql('DROP TABLE question_explanation');
        $this->addSql('CREATE TABLE question_explanation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL, locale VARCHAR(5) NOT NULL, generated_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, model_used VARCHAR(50) DEFAULT NULL, tokens_used INTEGER DEFAULT NULL, question_id INTEGER NOT NULL, CONSTRAINT FK_6B206C831E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO question_explanation (id, content, locale, generated_at, updated_at, model_used, tokens_used, question_id) SELECT id, content, locale, generated_at, updated_at, model_used, tokens_used, question_id FROM __temp__question_explanation');
        $this->addSql('DROP TABLE __temp__question_explanation');
        $this->addSql('CREATE INDEX IDX_6B206C831E27F6BF ON question_explanation (question_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_question_locale ON question_explanation (question_id, locale)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__question_explanation AS SELECT id, content, locale, generated_at, updated_at, model_used, tokens_used, question_id FROM question_explanation');
        $this->addSql('DROP TABLE question_explanation');
        $this->addSql('CREATE TABLE question_explanation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL, locale VARCHAR(5) NOT NULL, generated_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, model_used VARCHAR(50) DEFAULT NULL, tokens_used INTEGER DEFAULT NULL, question_id INTEGER NOT NULL, CONSTRAINT FK_6B206C831E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO question_explanation (id, content, locale, generated_at, updated_at, model_used, tokens_used, question_id) SELECT id, content, locale, generated_at, updated_at, model_used, tokens_used, question_id FROM __temp__question_explanation');
        $this->addSql('DROP TABLE __temp__question_explanation');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6B206C831E27F6BF ON question_explanation (question_id)');
    }
}
