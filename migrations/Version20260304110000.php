<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix intro_profile_answer FK on user_id to SET NULL on delete';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intro_profile_answer ALTER COLUMN user_id DROP NOT NULL');
        $this->addSql('ALTER TABLE intro_profile_answer DROP CONSTRAINT IF EXISTS fk_user');
        $this->addSql('ALTER TABLE intro_profile_answer ADD CONSTRAINT FK_USER FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intro_profile_answer DROP CONSTRAINT IF EXISTS fk_user');
        $this->addSql('ALTER TABLE intro_profile_answer ADD CONSTRAINT FK_USER FOREIGN KEY (user_id) REFERENCES "user" (id)');
        $this->addSql('ALTER TABLE intro_profile_answer ALTER COLUMN user_id SET NOT NULL');
    }
}
