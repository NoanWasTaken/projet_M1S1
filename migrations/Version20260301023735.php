<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301023735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE player_profile ADD body_skin VARCHAR(255)");
        $this->addSql("UPDATE player_profile SET body_skin = 'normal_body.webp' WHERE body_skin IS NULL");
        $this->addSql("ALTER TABLE player_profile ALTER COLUMN body_skin SET DEFAULT 'normal_body.webp'");
        $this->addSql("ALTER TABLE player_profile ALTER COLUMN body_skin SET NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE player_profile DROP body_skin");
    }
}
