<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241001075137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status DROP FOREIGN KEY FK_7B00651CEF1A9D84');
        $this->addSql('DROP INDEX IDX_7B00651CEF1A9D84 ON status');
        $this->addSql('ALTER TABLE status DROP notification_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status ADD notification_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651CEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_7B00651CEF1A9D84 ON status (notification_id)');
    }
}
