<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241105115114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participante_edicion (participante_id INT NOT NULL, edicion_id INT NOT NULL, INDEX IDX_A44959F5F6F50196 (participante_id), INDEX IDX_A44959F5D651B81E (edicion_id), PRIMARY KEY(participante_id, edicion_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE participante_edicion ADD CONSTRAINT FK_A44959F5F6F50196 FOREIGN KEY (participante_id) REFERENCES participante (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participante_edicion ADD CONSTRAINT FK_A44959F5D651B81E FOREIGN KEY (edicion_id) REFERENCES edicion (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participante_edicion DROP FOREIGN KEY FK_A44959F5F6F50196');
        $this->addSql('ALTER TABLE participante_edicion DROP FOREIGN KEY FK_A44959F5D651B81E');
        $this->addSql('DROP TABLE participante_edicion');
    }
}
