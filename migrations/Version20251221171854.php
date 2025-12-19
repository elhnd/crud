<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221171854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE badge (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, description CLOB NOT NULL, icon VARCHAR(50) NOT NULL, category VARCHAR(20) NOT NULL, rarity VARCHAR(20) NOT NULL, xp_reward INTEGER NOT NULL, conditions CLOB NOT NULL, is_hidden BOOLEAN NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEF0481D77153098 ON badge (code)');
        $this->addSql('CREATE TABLE flashcard (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, easiness DOUBLE PRECISION NOT NULL, repetitions INTEGER NOT NULL, interval INTEGER NOT NULL, next_review_date DATE NOT NULL, last_review_date DATE DEFAULT NULL, total_reviews INTEGER NOT NULL, correct_reviews INTEGER NOT NULL, created_at DATETIME NOT NULL, user_id INTEGER NOT NULL, question_id INTEGER NOT NULL, CONSTRAINT FK_70511A09A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_70511A091E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_70511A09A76ED395 ON flashcard (user_id)');
        $this->addSql('CREATE INDEX IDX_70511A091E27F6BF ON flashcard (question_id)');
        $this->addSql('CREATE INDEX idx_flashcard_review_date ON flashcard (next_review_date)');
        $this->addSql('CREATE TABLE user_badge (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, earned_at DATETIME NOT NULL, user_progress_id INTEGER NOT NULL, badge_id INTEGER NOT NULL, CONSTRAINT FK_1C32B345EAC04E5C FOREIGN KEY (user_progress_id) REFERENCES user_progress (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1C32B345F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1C32B345EAC04E5C ON user_badge (user_progress_id)');
        $this->addSql('CREATE INDEX IDX_1C32B345F7A2C2FC ON user_badge (badge_id)');
        $this->addSql('CREATE UNIQUE INDEX user_badge_unique ON user_badge (user_progress_id, badge_id)');
        $this->addSql('CREATE TABLE user_progress (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, total_xp INTEGER NOT NULL, level INTEGER NOT NULL, current_streak INTEGER NOT NULL, longest_streak INTEGER NOT NULL, last_activity_date DATE DEFAULT NULL, total_questions_answered INTEGER NOT NULL, total_correct_answers INTEGER NOT NULL, total_quizzes_completed INTEGER NOT NULL, perfect_quizzes INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_C28C1646A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C28C1646A76ED395 ON user_progress (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE flashcard');
        $this->addSql('DROP TABLE user_badge');
        $this->addSql('DROP TABLE user_progress');
    }
}
