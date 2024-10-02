<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240924105618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE migration_versions');
        $this->addSql('ALTER TABLE magasin DROP ip, CHANGE portable portable VARCHAR(24) DEFAULT NULL');
        $this->addSql('ALTER TABLE message CHANGE message message LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE ticket CHANGE mag_id mag_id INT DEFAULT NULL, CHANGE avis_id avis_id INT DEFAULT NULL, CHANGE id_team_vw id_team_vw INT DEFAULT NULL, CHANGE code_team_wv code_team_wv VARCHAR(32) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE migration_versions (version VARCHAR(14) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, executed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(version)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE ticket CHANGE mag_id mag_id INT NOT NULL, CHANGE avis_id avis_id INT DEFAULT 1, CHANGE id_team_vw id_team_vw VARCHAR(64) DEFAULT NULL, CHANGE code_team_wv code_team_wv VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE magasin ADD ip VARCHAR(255) DEFAULT NULL COMMENT \'IP du magagsin\', CHANGE portable portable BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE message CHANGE message message LONGTEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_general_ci`');
    }
}
