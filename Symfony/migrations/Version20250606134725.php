<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606134725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE channels ADD min_role INT DEFAULT 1 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hashtags DROP FOREIGN KEY FK_21E12BEF537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hashtags ADD CONSTRAINT FK_21E12BEF537A1329 FOREIGN KEY (message_id) REFERENCES messages (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE channels DROP min_role
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hashtags DROP FOREIGN KEY FK_21E12BEF537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hashtags ADD CONSTRAINT FK_21E12BEF537A1329 FOREIGN KEY (message_id) REFERENCES messages (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
