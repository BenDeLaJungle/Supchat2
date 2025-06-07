<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604182333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE channel_options (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, user_id INT NOT NULL, push_up TINYINT(1) NOT NULL, mail TINYINT(1) NOT NULL, notification VARCHAR(255) NOT NULL, INDEX IDX_6776C58672F5A1AA (channel_id), INDEX IDX_6776C586A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE channels (id INT AUTO_INCREMENT NOT NULL, workspace_id INT NOT NULL, name VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, min_role INT DEFAULT 1 NOT NULL, INDEX IDX_F314E2B682D40A1F (workspace_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE files (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, file_path VARCHAR(255) NOT NULL, INDEX IDX_6354059537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE hashtags (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, message_id INT NOT NULL, INDEX IDX_21E12BEF72F5A1AA (channel_id), INDEX IDX_21E12BEF537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE mentions (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_FE39735F537A1329 (message_id), INDEX IDX_FE39735FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, channel_id INT DEFAULT NULL, user_id INT NOT NULL, recipient_id INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_DB021E9672F5A1AA (channel_id), INDEX IDX_DB021E96A76ED395 (user_id), INDEX IDX_DB021E96E92F8F78 (recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, user_id INT NOT NULL, at_read TINYINT(1) NOT NULL, INDEX IDX_6000B0D3537A1329 (message_id), INDEX IDX_6000B0D3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reactions (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, user_id INT NOT NULL, emoji_code VARCHAR(255) NOT NULL, INDEX IDX_38737FB3537A1329 (message_id), INDEX IDX_38737FB3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, publish TINYINT(1) NOT NULL, moderate TINYINT(1) NOT NULL, manage TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, user_name VARCHAR(255) NOT NULL, email_address VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, role VARCHAR(50) NOT NULL, theme TINYINT(1) NOT NULL, status VARCHAR(255) NOT NULL, oauth_provider VARCHAR(255) DEFAULT NULL, oauth_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E924A232CF (user_name), UNIQUE INDEX UNIQ_1483A5E9B08E074E (email_address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE workspace_members (id INT AUTO_INCREMENT NOT NULL, workspace_id INT NOT NULL, user_id INT NOT NULL, role_id INT NOT NULL, publish TINYINT(1) NOT NULL, moderate TINYINT(1) NOT NULL, manage TINYINT(1) NOT NULL, INDEX IDX_9D9D39F482D40A1F (workspace_id), INDEX IDX_9D9D39F4A76ED395 (user_id), INDEX IDX_9D9D39F4D60322AC (role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE workspaces (id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, name VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, INDEX IDX_7FE8F3CB61220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE channel_options ADD CONSTRAINT FK_6776C58672F5A1AA FOREIGN KEY (channel_id) REFERENCES channels (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE channel_options ADD CONSTRAINT FK_6776C586A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE channels ADD CONSTRAINT FK_F314E2B682D40A1F FOREIGN KEY (workspace_id) REFERENCES workspaces (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE files ADD CONSTRAINT FK_6354059537A1329 FOREIGN KEY (message_id) REFERENCES messages (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hashtags ADD CONSTRAINT FK_21E12BEF72F5A1AA FOREIGN KEY (channel_id) REFERENCES channels (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hashtags ADD CONSTRAINT FK_21E12BEF537A1329 FOREIGN KEY (message_id) REFERENCES messages (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mentions ADD CONSTRAINT FK_FE39735F537A1329 FOREIGN KEY (message_id) REFERENCES messages (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mentions ADD CONSTRAINT FK_FE39735FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD CONSTRAINT FK_DB021E9672F5A1AA FOREIGN KEY (channel_id) REFERENCES channels (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD CONSTRAINT FK_DB021E96A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD CONSTRAINT FK_DB021E96E92F8F78 FOREIGN KEY (recipient_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3537A1329 FOREIGN KEY (message_id) REFERENCES messages (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3537A1329 FOREIGN KEY (message_id) REFERENCES messages (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workspace_members ADD CONSTRAINT FK_9D9D39F482D40A1F FOREIGN KEY (workspace_id) REFERENCES workspaces (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workspace_members ADD CONSTRAINT FK_9D9D39F4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workspace_members ADD CONSTRAINT FK_9D9D39F4D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workspaces ADD CONSTRAINT FK_7FE8F3CB61220EA6 FOREIGN KEY (creator_id) REFERENCES users (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE channel_options DROP FOREIGN KEY FK_6776C58672F5A1AA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE channel_options DROP FOREIGN KEY FK_6776C586A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE channels DROP FOREIGN KEY FK_F314E2B682D40A1F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE files DROP FOREIGN KEY FK_6354059537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hashtags DROP FOREIGN KEY FK_21E12BEF72F5A1AA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hashtags DROP FOREIGN KEY FK_21E12BEF537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mentions DROP FOREIGN KEY FK_FE39735F537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mentions DROP FOREIGN KEY FK_FE39735FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP FOREIGN KEY FK_DB021E9672F5A1AA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96E92F8F78
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workspace_members DROP FOREIGN KEY FK_9D9D39F482D40A1F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workspace_members DROP FOREIGN KEY FK_9D9D39F4A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workspace_members DROP FOREIGN KEY FK_9D9D39F4D60322AC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE workspaces DROP FOREIGN KEY FK_7FE8F3CB61220EA6
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE channel_options
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE channels
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE files
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE hashtags
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mentions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notifications
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reactions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE roles
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE workspace_members
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE workspaces
        SQL);
    }
}
