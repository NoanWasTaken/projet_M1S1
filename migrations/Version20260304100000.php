<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make order.user_id nullable so orders are preserved when a user is deleted';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ALTER COLUMN user_id DROP NOT NULL');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT IF EXISTS fk_f5299398a76ed395');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT IF EXISTS fk_f5299398a76ed395');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ALTER COLUMN user_id SET NOT NULL');
    }
}
