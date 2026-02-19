<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206135738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Only drop if exists
        $this->addSql('DROP TABLE IF EXISTS product CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS product_id_seq CASCADE');
        
        $this->addSql('ALTER TABLE products ADD category VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD stock INT NOT NULL');
        $this->addSql('ALTER TABLE products ADD brand VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD specifications JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE products ADD is_available BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE products ADD rating NUMERIC(3, 1) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN products.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE "user" ALTER name SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER surname SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE product (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, price NUMERIC(10, 2) NOT NULL, category VARCHAR(100) NOT NULL, image VARCHAR(255) DEFAULT NULL, stock INT NOT NULL, brand VARCHAR(100) DEFAULT NULL, specifications JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_available BOOLEAN NOT NULL, rating NUMERIC(3, 1) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN product.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE products DROP category');
        $this->addSql('ALTER TABLE products DROP stock');
        $this->addSql('ALTER TABLE products DROP brand');
        $this->addSql('ALTER TABLE products DROP specifications');
        $this->addSql('ALTER TABLE products DROP created_at');
        $this->addSql('ALTER TABLE products DROP is_available');
        $this->addSql('ALTER TABLE products DROP rating');
        $this->addSql('ALTER TABLE cart_item DROP CONSTRAINT fk_f0fe25274584665a');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT fk_f0fe25274584665a FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ALTER name DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER surname DROP NOT NULL');
    }
}
