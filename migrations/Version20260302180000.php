<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260302180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create intro_profile_answer table';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->hasTable('intro_profile_answer'),
            'Table intro_profile_answer already exists.'
        );
        $this->addSql('CREATE TABLE intro_profile_answer (id SERIAL NOT NULL, user_id INT DEFAULT NULL, game_type VARCHAR(50) NOT NULL, answered_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN intro_profile_answer.answered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_5B228EA3A76ED395 ON intro_profile_answer (user_id)');
        $this->addSql('ALTER TABLE intro_profile_answer ADD CONSTRAINT FK_USER FOREIGN KEY (user_id) REFERENCES "user"(id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE intro_profile_answer');
    }
}
