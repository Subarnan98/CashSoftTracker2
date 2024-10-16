<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241009134020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE magasin_user (magasin_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3CCAC8BF20096AE3 (magasin_id), INDEX IDX_3CCAC8BFA76ED395 (user_id), PRIMARY KEY(magasin_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE magasin_user ADD CONSTRAINT FK_3CCAC8BF20096AE3 FOREIGN KEY (magasin_id) REFERENCES magasin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE magasin_user ADD CONSTRAINT FK_3CCAC8BFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_magasin DROP FOREIGN KEY FK_CA2412D20096AE3');
        $this->addSql('ALTER TABLE user_magasin DROP FOREIGN KEY FK_CA2412DA76ED395');
        $this->addSql('DROP TABLE user_magasin');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_magasin (user_id INT NOT NULL, magasin_id INT NOT NULL, INDEX IDX_CA2412DA76ED395 (user_id), INDEX IDX_CA2412D20096AE3 (magasin_id), PRIMARY KEY(user_id, magasin_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_magasin ADD CONSTRAINT FK_CA2412D20096AE3 FOREIGN KEY (magasin_id) REFERENCES magasin (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_magasin ADD CONSTRAINT FK_CA2412DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE magasin_user DROP FOREIGN KEY FK_3CCAC8BF20096AE3');
        $this->addSql('ALTER TABLE magasin_user DROP FOREIGN KEY FK_3CCAC8BFA76ED395');
        $this->addSql('DROP TABLE magasin_user');
    }
}
