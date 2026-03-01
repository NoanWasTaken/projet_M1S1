<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260203000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create product table for e-commerce catalog';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            category VARCHAR(100) NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            stock INT NOT NULL,
            brand VARCHAR(100) DEFAULT NULL,
            specifications JSON DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            is_available BOOLEAN NOT NULL DEFAULT true,
            rating NUMERIC(3, 1) DEFAULT NULL
        )');
        $this->addSql('COMMENT ON COLUMN product.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product');
    }
}
