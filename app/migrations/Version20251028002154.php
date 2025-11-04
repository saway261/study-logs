<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251028002154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'PostエンティティのdateカラムにDC2Type:date_immutableコメントを追加';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_post_date_isdel ON post');
        $this->addSql('ALTER TABLE post CHANGE date date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post CHANGE date date DATE NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_post_date_isdel ON post (date, is_deleted)');
    }
}
