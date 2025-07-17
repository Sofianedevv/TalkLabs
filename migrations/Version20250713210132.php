<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250713210132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD stripe_payment_intent_id VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan ADD stripe_product_id VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan ADD stripe_price_id VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription ADD stripe_subscription_id VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription ADD stripe_customer_id VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan DROP stripe_product_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan DROP stripe_price_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription DROP stripe_subscription_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription DROP stripe_customer_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP stripe_payment_intent_id
        SQL);
    }
}
