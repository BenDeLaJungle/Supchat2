<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250606Manual extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration manuelle - création table manquante ou colonne min_role';
    }

    public function up(Schema $schema): void
    {
        // Exemple : ajout d'une colonne min_role sur la table users
        $this->addSql('ALTER TABLE users ADD min_role VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP min_role');
    }
}
