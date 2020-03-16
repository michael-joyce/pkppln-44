<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200316221609 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $sql=<<<ENDSQL
RENAME TABLE appuser TO nines_user;
ALTER TABLE nines_user 
    ADD data LONGTEXT NOT NULL COMMENT '(DC2Type:array)', 
    DROP locked, 
    DROP expired, 
    DROP expires_at, 
    DROP credentials_expired, 
    DROP credentials_expire_at, 
    DROP notify,
    CHANGE username username VARCHAR(180) NOT NULL, 
    CHANGE username_canonical username_canonical VARCHAR(180) NOT NULL, 
    CHANGE email email VARCHAR(180) NOT NULL, 
    CHANGE email_canonical email_canonical VARCHAR(180) NOT NULL, 
    CHANGE salt salt VARCHAR(255) DEFAULT NULL, 
    CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL;

CREATE UNIQUE INDEX UNIQ_5BA994A1C05FB297 
    ON nines_user (confirmation_token);

ALTER TABLE nines_user RENAME INDEX uniq_ee8a7c7492fc23a8 
    TO UNIQ_5BA994A192FC23A8;
ALTER TABLE nines_user RENAME INDEX uniq_ee8a7c74a0d96fbf 
    TO UNIQ_5BA994A1A0D96FBF;

ALTER TABLE blacklist 
    ADD updated DATETIME NOT NULL DEFAULT now();

CREATE FULLTEXT INDEX IDX_3B175385D17F50A6 
    ON blacklist (uuid);

UPDATE blacklist SET
    updated = created;

ALTER TABLE journal 
    ADD created DATETIME NOT NULL DEFAULT now(), 
    ADD updated DATETIME NOT NULL DEFAULT now(), 
    CHANGE title title VARCHAR(255) DEFAULT NULL, 
    CHANGE issn issn VARCHAR(9) DEFAULT NULL, 
    CHANGE email email VARCHAR(255) DEFAULT NULL, 
    CHANGE publisher_name publisher_name VARCHAR(255) DEFAULT NULL;

CREATE FULLTEXT INDEX IDX_C1A7E74DD17F50A62B36786B9FC5D7F6F47645AEE7927C74BF3AAE51E33 
    ON journal (uuid, title, issn, url, email, publisher_name, publisher_url);

ALTER TABLE deposit 
    RENAME COLUMN received TO created, 
    ADD updated DATETIME NOT NULL DEFAULT now(), 
    DROP package_path, 
    DROP error_count, 
    CHANGE file_type file_type VARCHAR(255) DEFAULT NULL, 
    CHANGE deposit_receipt deposit_receipt VARCHAR(2048) DEFAULT NULL;
    
CREATE FULLTEXT INDEX IDX_95DB9D39E2AB67BDF47645AE 
    ON deposit (deposit_uuid, url);

UPDATE deposit 
    SET updated = created;

ALTER TABLE document 
    ADD created DATETIME NOT NULL DEFAULT now();

ALTER TABLE term_of_use 
    DROP lang_code;

ALTER TABLE whitelist 
    ADD updated DATETIME NOT NULL DEFAULT now();
        
CREATE FULLTEXT INDEX IDX_CB069864D17F50A6 
    ON whitelist (uuid);

UPDATE whitelist SET
    updated = created;

ALTER TABLE term_of_use_history 
    ADD updated DATETIME NOT NULL DEFAULT now();

UPDATE term_of_use_history
    SET updated = created;

ENDSQL;
        $this->addSql($sql);
    }

    /**
     * @param Schema $schema
     *
     * @throws IrreversibleMigrationException
     */
    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
