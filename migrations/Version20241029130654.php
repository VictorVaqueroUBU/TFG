<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241029130654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE edicion (id INT AUTO_INCREMENT NOT NULL, codigo_edicion VARCHAR(10) NOT NULL, fecha_inicio DATETIME DEFAULT NULL, fecha_fin DATETIME DEFAULT NULL, calendario LONGTEXT DEFAULT NULL, horario VARCHAR(255) DEFAULT NULL, lugar LONGTEXT DEFAULT NULL, estado SMALLINT NOT NULL, sesiones SMALLINT NOT NULL, max_participantes SMALLINT NOT NULL, curso_id INT NOT NULL, INDEX IDX_655F773987CB4A1F (curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE edicion ADD CONSTRAINT FK_655F773987CB4A1F FOREIGN KEY (curso_id) REFERENCES curso (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE edicion DROP FOREIGN KEY FK_655F773987CB4A1F');
        $this->addSql('DROP TABLE edicion');
    }
}
