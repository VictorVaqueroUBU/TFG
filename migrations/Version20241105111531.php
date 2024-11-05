<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241105111531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participante (id INT AUTO_INCREMENT NOT NULL, nif VARCHAR(9) NOT NULL, apellidos VARCHAR(50) NOT NULL, nombre VARCHAR(50) NOT NULL, descripcion_cce VARCHAR(50) DEFAULT NULL, codigo_cce VARCHAR(5) DEFAULT NULL, grupo VARCHAR(2) DEFAULT NULL, nivel SMALLINT DEFAULT NULL, puesto_trabajo VARCHAR(75) DEFAULT NULL, subunidad VARCHAR(50) DEFAULT NULL, unidad VARCHAR(50) DEFAULT NULL, centro_destino VARCHAR(50) DEFAULT NULL, t_r_juridico VARCHAR(2) DEFAULT NULL, situacion_admin VARCHAR(75) DEFAULT NULL, codigo_plaza VARCHAR(8) DEFAULT NULL, telefono_trabajo VARCHAR(30) DEFAULT NULL, correo_aux VARCHAR(50) DEFAULT NULL, codigo_rpt VARCHAR(16) DEFAULT NULL, organizacion VARCHAR(100) DEFAULT NULL, turno VARCHAR(50) DEFAULT NULL, telefono_particular VARCHAR(9) DEFAULT NULL, telefono_movil VARCHAR(9) DEFAULT NULL, fecha_nacimiento DATE DEFAULT NULL, titulacion_nivel SMALLINT DEFAULT NULL, titulacion_fecha DATE DEFAULT NULL, titulacion VARCHAR(75) DEFAULT NULL, dni_sin_letra VARCHAR(8) DEFAULT NULL, uvus VARCHAR(25) DEFAULT NULL, sexo VARCHAR(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE participante');
    }
}
