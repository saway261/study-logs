<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027000522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Subjectエンティティと、それが参照するSubjectStatusエンティティの追加';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subject (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, name VARCHAR(20) NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_FBCE3E7A6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subject_status (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, UNIQUE INDEX uniq_subject_status_name (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7A6BF700BD FOREIGN KEY (status_id) REFERENCES subject_status (id) ON DELETE RESTRICT');
        $this->addSql("INSERT INTO subject_status (id, status) VALUES (1, '学習中'), (2, '学習終了') ON DUPLICATE KEY UPDATE status = VALUES(status)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7A6BF700BD');
        $this->addSql('DROP TABLE subject');
        $this->addSql('DROP TABLE subject_status');
    }
}
