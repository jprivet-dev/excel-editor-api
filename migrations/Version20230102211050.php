<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230102211050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE file_upload_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE data (id INT NOT NULL, nom_du_groupe VARCHAR(255) NOT NULL, origine VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, annee_debut TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, annee_separation TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, fondateurs VARCHAR(255) DEFAULT NULL, membres INT DEFAULT NULL, courant_musical VARCHAR(255) DEFAULT NULL, presentation TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE file_upload (id INT NOT NULL, filename VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN file_upload.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE data_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE file_upload_id_seq CASCADE');
        $this->addSql('DROP TABLE data');
        $this->addSql('DROP TABLE file_upload');
    }
}
