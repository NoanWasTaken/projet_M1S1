<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302083555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intro_profile_answer ALTER answered_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN intro_profile_answer.answered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX idx_user RENAME TO IDX_5B228EA3A76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE intro_profile_answer ALTER answered_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN intro_profile_answer.answered_at IS NULL');
        $this->addSql('ALTER INDEX idx_5b228ea3a76ed395 RENAME TO idx_user');
    }
}
