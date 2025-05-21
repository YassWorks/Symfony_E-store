<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519172443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES `product` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD shop_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04AD4D16C4DD FOREIGN KEY (shop_id) REFERENCES `shop` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D34A04AD4D16C4DD ON product (shop_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE shop ADD owner_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE shop ADD CONSTRAINT FK_AC6A4CA27E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AC6A4CA27E3C61F9 ON shop (owner_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `cart_item` DROP FOREIGN KEY FK_F0FE25274584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `cart_item` ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `product` DROP FOREIGN KEY FK_D34A04AD4D16C4DD
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D34A04AD4D16C4DD ON `product`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `product` DROP shop_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `shop` DROP FOREIGN KEY FK_AC6A4CA27E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_AC6A4CA27E3C61F9 ON `shop`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `shop` DROP owner_id
        SQL);
    }
}
