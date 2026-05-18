<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260518211635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, personne_min INT NOT NULL, prix_personne NUMERIC(5, 2) NOT NULL, description LONGTEXT NOT NULL, quantite_restante INT NOT NULL, regime_id INT NOT NULL, theme_id INT NOT NULL, INDEX IDX_7D053A9335E7D534 (regime_id), INDEX IDX_7D053A9359027487 (theme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu_plats (menu_id INT NOT NULL, plats_id INT NOT NULL, INDEX IDX_14E6416DCCD7E912 (menu_id), INDEX IDX_14E6416DAA14E1C8 (plats_id), PRIMARY KEY(menu_id, plats_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE regime (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, theme VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A9335E7D534 FOREIGN KEY (regime_id) REFERENCES regime (id)');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A9359027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE menu_plats ADD CONSTRAINT FK_14E6416DCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_plats ADD CONSTRAINT FK_14E6416DAA14E1C8 FOREIGN KEY (plats_id) REFERENCES plats (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A9335E7D534');
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A9359027487');
        $this->addSql('ALTER TABLE menu_plats DROP FOREIGN KEY FK_14E6416DCCD7E912');
        $this->addSql('ALTER TABLE menu_plats DROP FOREIGN KEY FK_14E6416DAA14E1C8');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_plats');
        $this->addSql('DROP TABLE regime');
        $this->addSql('DROP TABLE theme');
    }
}
