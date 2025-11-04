<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251104015714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'subjectテーブルに対し、is_deleted=trueかつstatus_id=1のレコードにnameカラムのユニーク制約を設定';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
        ALTER TABLE subject
          ADD COLUMN active_name VARCHAR(20)
            GENERATED ALWAYS AS (
              CASE WHEN status_id = 1 AND is_deleted = 0 THEN name ELSE NULL END
            ) STORED,
          ADD UNIQUE INDEX uniq_subject_active_name (active_name)
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("
        ALTER TABLE subject
          DROP INDEX uniq_subject_active_name,
          DROP COLUMN active_name
        ");
    }
}
