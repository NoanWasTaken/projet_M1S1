<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304002428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promo_code (id SERIAL NOT NULL, code VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, active BOOLEAN NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, max_usage INT DEFAULT NULL, unlock_condition VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3D8C939E77153098 ON promo_code (code)');
        $this->addSql('COMMENT ON COLUMN promo_code.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE promo_code_user (promo_code_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(promo_code_id, user_id))');
        $this->addSql('CREATE INDEX IDX_93B2DB332FAE4625 ON promo_code_user (promo_code_id)');
        $this->addSql('CREATE INDEX IDX_93B2DB33A76ED395 ON promo_code_user (user_id)');
        $this->addSql('ALTER TABLE promo_code_user ADD CONSTRAINT FK_93B2DB332FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_code (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE promo_code_user ADD CONSTRAINT FK_93B2DB33A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE promo_code_user DROP CONSTRAINT FK_93B2DB332FAE4625');
        $this->addSql('ALTER TABLE promo_code_user DROP CONSTRAINT FK_93B2DB33A76ED395');
        $this->addSql('DROP TABLE promo_code');
        $this->addSql('DROP TABLE promo_code_user');
    }
}
