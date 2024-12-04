<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241204070437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE asistencia (id INT AUTO_INCREMENT NOT NULL, asiste TINYINT(1) NOT NULL, justifica TINYINT(1) NOT NULL, observaciones LONGTEXT DEFAULT NULL, participante_id INT NOT NULL, formador_id INT NOT NULL, sesion_id INT NOT NULL, INDEX IDX_D8264A8DF6F50196 (participante_id), INDEX IDX_D8264A8D9B4172E7 (formador_id), INDEX IDX_D8264A8D1CCCADCB (sesion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE asistencia ADD CONSTRAINT FK_D8264A8DF6F50196 FOREIGN KEY (participante_id) REFERENCES participante (id)');
        $this->addSql('ALTER TABLE asistencia ADD CONSTRAINT FK_D8264A8D9B4172E7 FOREIGN KEY (formador_id) REFERENCES formador (id)');
        $this->addSql('ALTER TABLE asistencia ADD CONSTRAINT FK_D8264A8D1CCCADCB FOREIGN KEY (sesion_id) REFERENCES sesion (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asistencia DROP FOREIGN KEY FK_D8264A8DF6F50196');
        $this->addSql('ALTER TABLE asistencia DROP FOREIGN KEY FK_D8264A8D9B4172E7');
        $this->addSql('ALTER TABLE asistencia DROP FOREIGN KEY FK_D8264A8D1CCCADCB');
        $this->addSql('DROP TABLE asistencia');
    }
}
