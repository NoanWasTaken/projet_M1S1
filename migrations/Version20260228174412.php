<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228174412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE player_profile ADD hair_skin VARCHAR(255)");
        $this->addSql("UPDATE player_profile SET hair_skin = 'bald_head.webp' WHERE hair_skin IS NULL");
        $this->addSql("ALTER TABLE player_profile ALTER COLUMN hair_skin SET NOT NULL");
        $this->addSql("ALTER TABLE player_profile ALTER COLUMN hair_skin SET DEFAULT 'bald_head.webp'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE player_profile DROP hair_skin');
    }
}
