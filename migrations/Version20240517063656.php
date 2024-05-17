<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517063656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE adverts');
        $this->addSql('DROP TABLE news');
        $this->addSql('DROP INDEX FK_entity ON entity_view_counts');
        $this->addSql('ALTER TABLE entity_view_counts CHANGE entity entity VARCHAR(255) DEFAULT NULL, CHANGE entity_id entity_id INT DEFAULT NULL, CHANGE page_views page_views INT DEFAULT NULL, CHANGE phone_views phone_views INT DEFAULT NULL, CHANGE date date DATE DEFAULT NULL, CHANGE project project VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adverts (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, description TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, price NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE news (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, content TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, published_at DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE entity_view_counts CHANGE entity_id entity_id INT NOT NULL, CHANGE entity entity VARCHAR(255) NOT NULL, CHANGE page_views page_views INT DEFAULT 0 NOT NULL, CHANGE phone_views phone_views INT DEFAULT 0 NOT NULL, CHANGE date date DATE NOT NULL, CHANGE project project VARCHAR(255) DEFAULT \'project\' NOT NULL');
        $this->addSql('CREATE INDEX FK_entity ON entity_view_counts (entity_id, entity)');
    }
}
