<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027020019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '科目と学習時間、学習内容の概要を保持するPostSubjectエンティティの追加';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_subject (id INT AUTO_INCREMENT NOT NULL, subject_id INT NOT NULL, summary VARCHAR(100) DEFAULT NULL, minutes INT NOT NULL, INDEX idx_post_subject_subject (subject_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post_subject ADD CONSTRAINT FK_C626894923EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_subject DROP FOREIGN KEY FK_C626894923EDC87');
        $this->addSql('DROP TABLE post_subject');
    }
}
