<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519173251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE `cart` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_BA388B7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `cart_item` (id INT AUTO_INCREMENT NOT NULL, cart_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_F0FE25271AD5CDBF (cart_id), INDEX IDX_F0FE25274584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `image` (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, filename VARCHAR(255) NOT NULL, INDEX IDX_C53D045F4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `product` (id INT AUTO_INCREMENT NOT NULL, shop_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, price NUMERIC(10, 2) NOT NULL, stock_quantity INT NOT NULL, category VARCHAR(255) NOT NULL, INDEX IDX_D34A04AD4D16C4DD (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `shop` (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(50) NOT NULL, categories JSON NOT NULL, website VARCHAR(255) NOT NULL, email VARCHAR(100) NOT NULL, logo_url VARCHAR(255) NOT NULL, INDEX IDX_AC6A4CA27E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(100) NOT NULL, name VARCHAR(50) NOT NULL, roles JSON NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `cart` ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `cart_item` ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES `cart` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `cart_item` ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES `product` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `image` ADD CONSTRAINT FK_C53D045F4584665A FOREIGN KEY (product_id) REFERENCES `product` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `product` ADD CONSTRAINT FK_D34A04AD4D16C4DD FOREIGN KEY (shop_id) REFERENCES `shop` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `shop` ADD CONSTRAINT FK_AC6A4CA27E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `cart` DROP FOREIGN KEY FK_BA388B7A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `cart_item` DROP FOREIGN KEY FK_F0FE25271AD5CDBF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `cart_item` DROP FOREIGN KEY FK_F0FE25274584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `image` DROP FOREIGN KEY FK_C53D045F4584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `product` DROP FOREIGN KEY FK_D34A04AD4D16C4DD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `shop` DROP FOREIGN KEY FK_AC6A4CA27E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `cart`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `cart_item`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `image`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `product`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `shop`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `user`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
