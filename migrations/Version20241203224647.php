<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203224647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sesion (id INT AUTO_INCREMENT NOT NULL, fecha DATE NOT NULL, hora_inicio TIME NOT NULL, duracion NUMERIC(5, 2) NOT NULL, observaciones LONGTEXT DEFAULT NULL, tipo SMALLINT NOT NULL, edicion_id INT NOT NULL, formador_id INT NOT NULL, INDEX IDX_1B45E21BD651B81E (edicion_id), INDEX IDX_1B45E21B9B4172E7 (formador_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE sesion ADD CONSTRAINT FK_1B45E21BD651B81E FOREIGN KEY (edicion_id) REFERENCES edicion (id)');
        $this->addSql('ALTER TABLE sesion ADD CONSTRAINT FK_1B45E21B9B4172E7 FOREIGN KEY (formador_id) REFERENCES formador (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sesion DROP FOREIGN KEY FK_1B45E21BD651B81E');
        $this->addSql('ALTER TABLE sesion DROP FOREIGN KEY FK_1B45E21B9B4172E7');
        $this->addSql('DROP TABLE sesion');
    }
}
