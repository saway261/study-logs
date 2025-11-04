<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027134614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Postエンティティに論理削除用のis_deletedカラムを追加し、dateカラムとの複合ユニーク制約を設定';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_post_date ON post');
        $this->addSql('ALTER TABLE post ADD is_deleted TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_post_date_isdel ON post (date, is_deleted)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_post_date_isdel ON post');
        $this->addSql('ALTER TABLE post DROP is_deleted');
        $this->addSql('CREATE UNIQUE INDEX uniq_post_date ON post (date)');
    }
}
