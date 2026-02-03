<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203095838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_types (id SERIAL NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE products (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, photos TEXT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN products.photos IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE products_game_types (products_id INT NOT NULL, game_types_id INT NOT NULL, PRIMARY KEY(products_id, game_types_id))');
        $this->addSql('CREATE INDEX IDX_B95C08A16C8A81A9 ON products_game_types (products_id)');
        $this->addSql('CREATE INDEX IDX_B95C08A125F6CC71 ON products_game_types (game_types_id)');
        $this->addSql('CREATE TABLE user_game_types (user_id INT NOT NULL, game_types_id INT NOT NULL, PRIMARY KEY(user_id, game_types_id))');
        $this->addSql('CREATE INDEX IDX_CC643653A76ED395 ON user_game_types (user_id)');
        $this->addSql('CREATE INDEX IDX_CC64365325F6CC71 ON user_game_types (game_types_id)');
        $this->addSql('ALTER TABLE products_game_types ADD CONSTRAINT FK_B95C08A16C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE products_game_types ADD CONSTRAINT FK_B95C08A125F6CC71 FOREIGN KEY (game_types_id) REFERENCES game_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_game_types ADD CONSTRAINT FK_CC643653A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_game_types ADD CONSTRAINT FK_CC64365325F6CC71 FOREIGN KEY (game_types_id) REFERENCES game_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD surname VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE products_game_types DROP CONSTRAINT FK_B95C08A16C8A81A9');
        $this->addSql('ALTER TABLE products_game_types DROP CONSTRAINT FK_B95C08A125F6CC71');
        $this->addSql('ALTER TABLE user_game_types DROP CONSTRAINT FK_CC643653A76ED395');
        $this->addSql('ALTER TABLE user_game_types DROP CONSTRAINT FK_CC64365325F6CC71');
        $this->addSql('DROP TABLE game_types');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE products_game_types');
        $this->addSql('DROP TABLE user_game_types');
        $this->addSql('ALTER TABLE "user" DROP name');
        $this->addSql('ALTER TABLE "user" DROP surname');
    }
}
