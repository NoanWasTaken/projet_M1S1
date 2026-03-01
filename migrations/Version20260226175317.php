<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226175317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE player_profile (id SERIAL NOT NULL, owner_id INT NOT NULL, xp_total INT NOT NULL, level INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E0A3554A7E3C61F9 ON player_profile (owner_id)');
        $this->addSql('COMMENT ON COLUMN player_profile.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN player_profile.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE reward (id SERIAL NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(120) NOT NULL, type VARCHAR(50) NOT NULL, ruletype VARCHAR(50) NOT NULL, rule_value VARCHAR(120) DEFAULT NULL, description TEXT DEFAULT NULL, unlocks JSON DEFAULT NULL, is_active BOOLEAN NOT NULL, creted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4ED1725377153098 ON reward (code)');
        $this->addSql('COMMENT ON COLUMN reward.creted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_reward (id SERIAL NOT NULL, profile_id INT NOT NULL, reward_id INT NOT NULL, unlocked_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, source VARCHAR(50) DEFAULT NULL, meta JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2B83696ECCFA12B8 ON user_reward (profile_id)');
        $this->addSql('CREATE INDEX IDX_2B83696EE466ACA1 ON user_reward (reward_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_profile_reward ON user_reward (profile_id, reward_id)');
        $this->addSql('COMMENT ON COLUMN user_reward.unlocked_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE xpevent (id SERIAL NOT NULL, profile_id INT NOT NULL, amount INT NOT NULL, reason VARCHAR(100) NOT NULL, meta JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B445B149CCFA12B8 ON xpevent (profile_id)');
        $this->addSql('COMMENT ON COLUMN xpevent.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE player_profile ADD CONSTRAINT FK_E0A3554A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696ECCFA12B8 FOREIGN KEY (profile_id) REFERENCES player_profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_reward ADD CONSTRAINT FK_2B83696EE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE xpevent ADD CONSTRAINT FK_B445B149CCFA12B8 FOREIGN KEY (profile_id) REFERENCES player_profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE player_profile DROP CONSTRAINT FK_E0A3554A7E3C61F9');
        $this->addSql('ALTER TABLE user_reward DROP CONSTRAINT FK_2B83696ECCFA12B8');
        $this->addSql('ALTER TABLE user_reward DROP CONSTRAINT FK_2B83696EE466ACA1');
        $this->addSql('ALTER TABLE xpevent DROP CONSTRAINT FK_B445B149CCFA12B8');
        $this->addSql('DROP TABLE player_profile');
        $this->addSql('DROP TABLE reward');
        $this->addSql('DROP TABLE user_reward');
        $this->addSql('DROP TABLE xpevent');
    }
}
