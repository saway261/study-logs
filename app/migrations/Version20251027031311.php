<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027031311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Postエンティティを新規作成し、PostSubjectエンティティとリレーションを定義';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post_subject ADD post_id INT NOT NULL');
        $this->addSql('ALTER TABLE post_subject ADD CONSTRAINT FK_C62689494B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_post_subject_post ON post_subject (post_id)');
        $this->addSql('ALTER TABLE post ADD UNIQUE INDEX uniq_post_date (date);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_subject DROP FOREIGN KEY FK_C62689494B89032C');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP INDEX idx_post_subject_post ON post_subject');
        $this->addSql('ALTER TABLE post_subject DROP post_id');
        $this->addSql('DROP INDEX uniq_post_date ON post;');
    }
}
