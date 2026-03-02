<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302012013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE saved_cart (id SERIAL NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_59C7AA2A76ED395 ON saved_cart (user_id)');
        $this->addSql('CREATE TABLE saved_cart_products (saved_cart_id INT NOT NULL, products_id INT NOT NULL, PRIMARY KEY(saved_cart_id, products_id))');
        $this->addSql('CREATE INDEX IDX_121DB5F390D40228 ON saved_cart_products (saved_cart_id)');
        $this->addSql('CREATE INDEX IDX_121DB5F36C8A81A9 ON saved_cart_products (products_id)');
        $this->addSql('ALTER TABLE saved_cart ADD CONSTRAINT FK_59C7AA2A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE saved_cart_products ADD CONSTRAINT FK_121DB5F390D40228 FOREIGN KEY (saved_cart_id) REFERENCES saved_cart (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE saved_cart_products ADD CONSTRAINT FK_121DB5F36C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE saved_cart DROP CONSTRAINT FK_59C7AA2A76ED395');
        $this->addSql('ALTER TABLE saved_cart_products DROP CONSTRAINT FK_121DB5F390D40228');
        $this->addSql('ALTER TABLE saved_cart_products DROP CONSTRAINT FK_121DB5F36C8A81A9');
        $this->addSql('DROP TABLE saved_cart');
        $this->addSql('DROP TABLE saved_cart_products');
    }
}
