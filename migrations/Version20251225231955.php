<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251225231955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE flashcard');
        $this->addSql('DROP TABLE user_badge');
        $this->addSql('DROP TABLE user_progress');
        $this->addSql('ALTER TABLE question ADD COLUMN is_active BOOLEAN DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE badge (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(100) NOT NULL COLLATE "BINARY", name VARCHAR(100) NOT NULL COLLATE "BINARY", description CLOB NOT NULL COLLATE "BINARY", icon VARCHAR(50) NOT NULL COLLATE "BINARY", category VARCHAR(20) NOT NULL COLLATE "BINARY", rarity VARCHAR(20) NOT NULL COLLATE "BINARY", xp_reward INTEGER NOT NULL, conditions CLOB NOT NULL COLLATE "BINARY", is_hidden BOOLEAN NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEF0481D77153098 ON badge (code)');
        $this->addSql('CREATE TABLE flashcard (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, easiness DOUBLE PRECISION NOT NULL, repetitions INTEGER NOT NULL, interval INTEGER NOT NULL, next_review_date DATE NOT NULL, last_review_date DATE DEFAULT NULL, total_reviews INTEGER NOT NULL, correct_reviews INTEGER NOT NULL, created_at DATETIME NOT NULL, user_id INTEGER NOT NULL, question_id INTEGER NOT NULL, CONSTRAINT FK_70511A09A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_70511A091E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX idx_flashcard_review_date ON flashcard (next_review_date)');
        $this->addSql('CREATE INDEX IDX_70511A091E27F6BF ON flashcard (question_id)');
        $this->addSql('CREATE INDEX IDX_70511A09A76ED395 ON flashcard (user_id)');
        $this->addSql('CREATE TABLE user_badge (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, earned_at DATETIME NOT NULL, user_progress_id INTEGER NOT NULL, badge_id INTEGER NOT NULL, CONSTRAINT FK_1C32B345EAC04E5C FOREIGN KEY (user_progress_id) REFERENCES user_progress (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1C32B345F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX user_badge_unique ON user_badge (user_progress_id, badge_id)');
        $this->addSql('CREATE INDEX IDX_1C32B345F7A2C2FC ON user_badge (badge_id)');
        $this->addSql('CREATE INDEX IDX_1C32B345EAC04E5C ON user_badge (user_progress_id)');
        $this->addSql('CREATE TABLE user_progress (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, total_xp INTEGER NOT NULL, level INTEGER NOT NULL, current_streak INTEGER NOT NULL, longest_streak INTEGER NOT NULL, last_activity_date DATE DEFAULT NULL, total_questions_answered INTEGER NOT NULL, total_correct_answers INTEGER NOT NULL, total_quizzes_completed INTEGER NOT NULL, perfect_quizzes INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_C28C1646A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C28C1646A76ED395 ON user_progress (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__question AS SELECT id, identifier, text, explanation, resource_url, type, difficulty, created_at, updated_at, is_certification, category_id, subcategory_id FROM question');
        $this->addSql('DROP TABLE question');
        $this->addSql('CREATE TABLE question (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, identifier VARCHAR(64) DEFAULT NULL, text CLOB NOT NULL, explanation CLOB DEFAULT NULL, resource_url CLOB DEFAULT NULL, type VARCHAR(20) NOT NULL, difficulty INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, is_certification BOOLEAN DEFAULT 0 NOT NULL, category_id INTEGER NOT NULL, subcategory_id INTEGER NOT NULL, CONSTRAINT FK_B6F7494E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B6F7494E5DC6FE57 FOREIGN KEY (subcategory_id) REFERENCES subcategory (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO question (id, identifier, text, explanation, resource_url, type, difficulty, created_at, updated_at, is_certification, category_id, subcategory_id) SELECT id, identifier, text, explanation, resource_url, type, difficulty, created_at, updated_at, is_certification, category_id, subcategory_id FROM __temp__question');
        $this->addSql('DROP TABLE __temp__question');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6F7494E772E836A ON question (identifier)');
        $this->addSql('CREATE INDEX IDX_B6F7494E12469DE2 ON question (category_id)');
        $this->addSql('CREATE INDEX IDX_B6F7494E5DC6FE57 ON question (subcategory_id)');
        $this->addSql('CREATE INDEX idx_question_identifier ON question (identifier)');
    }
}
