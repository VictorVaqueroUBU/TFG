<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241008105600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE curso (id INT AUTO_INCREMENT NOT NULL, codigo_curso VARCHAR(10) NOT NULL, nombre_curso VARCHAR(255) NOT NULL, horas SMALLINT NOT NULL, objetivos LONGTEXT DEFAULT NULL, contenidos LONGTEXT DEFAULT NULL, destinatarios LONGTEXT DEFAULT NULL, requisitos LONGTEXT DEFAULT NULL, justificacion LONGTEXT DEFAULT NULL, coordinador VARCHAR(255) DEFAULT NULL, participantes_edicion SMALLINT NOT NULL, ediciones_estimadas SMALLINT NOT NULL, plazo_solicitud VARCHAR(255) DEFAULT NULL, observaciones LONGTEXT DEFAULT NULL, visible_web TINYINT(1) NOT NULL, id_programa INT NOT NULL, horas_virtuales SMALLINT NOT NULL, calificable TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE curso');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
