<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241117161000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE curso (id INT AUTO_INCREMENT NOT NULL, codigo_curso VARCHAR(10) NOT NULL, nombre_curso VARCHAR(255) NOT NULL, horas SMALLINT NOT NULL, objetivos LONGTEXT DEFAULT NULL, contenidos LONGTEXT DEFAULT NULL, destinatarios LONGTEXT DEFAULT NULL, requisitos LONGTEXT DEFAULT NULL, justificacion LONGTEXT DEFAULT NULL, coordinador VARCHAR(255) DEFAULT NULL, participantes_edicion SMALLINT NOT NULL, ediciones_estimadas SMALLINT NOT NULL, plazo_solicitud VARCHAR(255) DEFAULT NULL, observaciones LONGTEXT DEFAULT NULL, visible_web TINYINT(1) NOT NULL, horas_virtuales SMALLINT NOT NULL, calificable TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_CA3B40EC99E811E1 (codigo_curso), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE edicion (id INT AUTO_INCREMENT NOT NULL, codigo_edicion VARCHAR(10) NOT NULL, fecha_inicio DATE DEFAULT NULL, fecha_fin DATE DEFAULT NULL, calendario LONGTEXT DEFAULT NULL, horario VARCHAR(255) DEFAULT NULL, lugar LONGTEXT DEFAULT NULL, estado SMALLINT NOT NULL, sesiones SMALLINT NOT NULL, max_participantes SMALLINT NOT NULL, curso_id INT NOT NULL, UNIQUE INDEX UNIQ_655F7739CC9D3BC5 (codigo_edicion), INDEX IDX_655F773987CB4A1F (curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE formador (id INT AUTO_INCREMENT NOT NULL, nif VARCHAR(9) NOT NULL, apellidos VARCHAR(50) NOT NULL, nombre VARCHAR(50) NOT NULL, organizacion VARCHAR(100) NOT NULL, correo VARCHAR(50) DEFAULT NULL, telefono VARCHAR(30) DEFAULT NULL, observaciones LONGTEXT DEFAULT NULL, formador_rj SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_B93AC18DADE62BBB (nif), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE formador_edicion (id INT AUTO_INCREMENT NOT NULL, horas_impartidas NUMERIC(5, 2) DEFAULT NULL, retrib_prevista NUMERIC(7, 2) DEFAULT NULL, retrib_ejecutada NUMERIC(7, 2) DEFAULT NULL, observaciones LONGTEXT DEFAULT NULL, fedap TINYINT(1) DEFAULT NULL, evaluacion TINYINT(1) DEFAULT NULL, hoja_firma DATE DEFAULT NULL, datos_banco DATE DEFAULT NULL, incompatibilidad DATE DEFAULT NULL, grabado_sorolla VARCHAR(30) DEFAULT NULL, sin_coste TINYINT(1) DEFAULT NULL, coincide_turno SMALLINT DEFAULT NULL, coincide_turno_observaciones VARCHAR(255) DEFAULT NULL, control_personal_enviado DATE DEFAULT NULL, control_personal_recibido DATE DEFAULT NULL, formador_id INT NOT NULL, edicion_id INT NOT NULL, INDEX IDX_B75DCDE9B4172E7 (formador_id), INDEX IDX_B75DCDED651B81E (edicion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE participante (id INT AUTO_INCREMENT NOT NULL, nif VARCHAR(9) NOT NULL, apellidos VARCHAR(50) NOT NULL, nombre VARCHAR(50) NOT NULL, descripcion_cce VARCHAR(50) DEFAULT NULL, codigo_cce VARCHAR(5) DEFAULT NULL, grupo VARCHAR(2) DEFAULT NULL, nivel SMALLINT DEFAULT NULL, puesto_trabajo VARCHAR(75) DEFAULT NULL, subunidad VARCHAR(50) DEFAULT NULL, unidad VARCHAR(50) DEFAULT NULL, centro_destino VARCHAR(50) DEFAULT NULL, t_r_juridico VARCHAR(2) DEFAULT NULL, situacion_admin VARCHAR(75) DEFAULT NULL, codigo_plaza VARCHAR(8) DEFAULT NULL, telefono_trabajo VARCHAR(30) DEFAULT NULL, correo_aux VARCHAR(50) DEFAULT NULL, codigo_rpt VARCHAR(16) DEFAULT NULL, organizacion VARCHAR(100) DEFAULT NULL, turno VARCHAR(50) DEFAULT NULL, telefono_particular VARCHAR(9) DEFAULT NULL, telefono_movil VARCHAR(9) DEFAULT NULL, fecha_nacimiento DATE DEFAULT NULL, titulacion_nivel SMALLINT DEFAULT NULL, titulacion_fecha DATE DEFAULT NULL, titulacion VARCHAR(75) DEFAULT NULL, dni_sin_letra VARCHAR(8) DEFAULT NULL, uvus VARCHAR(25) DEFAULT NULL, sexo VARCHAR(1) DEFAULT NULL, UNIQUE INDEX UNIQ_85BDC5C3ADE62BBB (nif), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE participante_edicion (id INT AUTO_INCREMENT NOT NULL, fecha_solicitud DATETIME NOT NULL, baja_justificada DATETIME DEFAULT NULL, prueba_final NUMERIC(4, 2) DEFAULT NULL, certificado VARCHAR(1) DEFAULT NULL, libro SMALLINT DEFAULT NULL, numero_titulo INT DEFAULT NULL, observaciones LONGTEXT DEFAULT NULL, apto SMALLINT DEFAULT NULL, direccion VARCHAR(30) DEFAULT NULL, participante_id INT NOT NULL, edicion_id INT NOT NULL, INDEX IDX_A44959F5F6F50196 (participante_id), INDEX IDX_A44959F5D651B81E (edicion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE edicion ADD CONSTRAINT FK_655F773987CB4A1F FOREIGN KEY (curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE formador_edicion ADD CONSTRAINT FK_B75DCDE9B4172E7 FOREIGN KEY (formador_id) REFERENCES formador (id)');
        $this->addSql('ALTER TABLE formador_edicion ADD CONSTRAINT FK_B75DCDED651B81E FOREIGN KEY (edicion_id) REFERENCES edicion (id)');
        $this->addSql('ALTER TABLE participante_edicion ADD CONSTRAINT FK_A44959F5F6F50196 FOREIGN KEY (participante_id) REFERENCES participante (id)');
        $this->addSql('ALTER TABLE participante_edicion ADD CONSTRAINT FK_A44959F5D651B81E FOREIGN KEY (edicion_id) REFERENCES edicion (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE edicion DROP FOREIGN KEY FK_655F773987CB4A1F');
        $this->addSql('ALTER TABLE formador_edicion DROP FOREIGN KEY FK_B75DCDE9B4172E7');
        $this->addSql('ALTER TABLE formador_edicion DROP FOREIGN KEY FK_B75DCDED651B81E');
        $this->addSql('ALTER TABLE participante_edicion DROP FOREIGN KEY FK_A44959F5F6F50196');
        $this->addSql('ALTER TABLE participante_edicion DROP FOREIGN KEY FK_A44959F5D651B81E');
        $this->addSql('DROP TABLE curso');
        $this->addSql('DROP TABLE edicion');
        $this->addSql('DROP TABLE formador');
        $this->addSql('DROP TABLE formador_edicion');
        $this->addSql('DROP TABLE participante');
        $this->addSql('DROP TABLE participante_edicion');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
