<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Repository\ProductCategoryRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231223083312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO product_category (id, name) values (' . ProductCategoryRepository::DEFAULT_ID . ', "Default")');

        //$this->addSql('ALTER TABLE product ADD COLUMN category_id INTEGER NOT NULL');
        //$this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price INTEGER NOT NULL, description VARCHAR(255) DEFAULT NULL, category_id INTEGER NOT NULL)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, name, price, description FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, price INTEGER NOT NULL, description VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES product_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        $this->addSql('INSERT INTO product (id, category_id, name, price, description) SELECT id, ' . ProductCategoryRepository::DEFAULT_ID . ', name, price, description FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, name, price, description FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price INTEGER NOT NULL, description VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO product (id, name, price, description) SELECT id, name, price, description FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');

        $this->addSql('DELETE FROM product_category where id = ' . ProductCategoryRepository::DEFAULT_ID);
        $this->addSql('DROP TABLE product_category');
    }
}
