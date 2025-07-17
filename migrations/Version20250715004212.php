<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250715004212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE conversation_like (id SERIAL NOT NULL, account_id INT NOT NULL, conversation_id INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7B1C23AF9B6B5FBA ON conversation_like (account_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7B1C23AF9AC0396 ON conversation_like (conversation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_like ADD CONSTRAINT FK_7B1C23AF9B6B5FBA FOREIGN KEY (account_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_like ADD CONSTRAINT FK_7B1C23AF9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_like DROP CONSTRAINT FK_7B1C23AF9B6B5FBA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_like DROP CONSTRAINT FK_7B1C23AF9AC0396
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE conversation_like
        SQL);
    }
}
