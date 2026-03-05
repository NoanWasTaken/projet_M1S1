<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create site_settings table and seed chatbot_enabled default';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE site_settings (setting_key VARCHAR(100) NOT NULL, setting_value VARCHAR(255) NOT NULL, PRIMARY KEY(setting_key))');
        $this->addSql("INSERT INTO site_settings (setting_key, setting_value) VALUES ('chatbot_enabled', '1')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE site_settings');
    }
}
