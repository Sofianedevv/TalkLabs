<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250506174702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE accounts (id SERIAL NOT NULL, current_subscription_id INT NOT NULL, name VARCHAR(50) NOT NULL, username VARCHAR(30) DEFAULT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, avatar_url VARCHAR(255) DEFAULT NULL, bio TEXT DEFAULT NULL, role JSON NOT NULL, is_verified BOOLEAN NOT NULL, reset_password_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CAC89EACDDE45DDE ON accounts (current_subscription_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN accounts.reset_token_expires_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN accounts.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN category.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE comment (id SERIAL NOT NULL, conversation_id INT NOT NULL, publisher_id INT NOT NULL, parent_comment_id INT DEFAULT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9474526C9AC0396 ON comment (conversation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9474526C40C86FCE ON comment (publisher_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9474526CBF2AF943 ON comment (parent_comment_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN comment.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE contact (id SERIAL NOT NULL, users_id INT DEFAULT NULL, email VARCHAR(100) NOT NULL, subject VARCHAR(100) NOT NULL, message TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4C62E63867B3B43D ON contact (users_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN contact.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE conversation (id SERIAL NOT NULL, creator_id INT NOT NULL, title VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_public BOOLEAN NOT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, content JSON NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8A8E26E961220EA6 ON conversation (creator_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN conversation.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN conversation.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE conversation_category (conversation_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(conversation_id, category_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E602A6D19AC0396 ON conversation_category (conversation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E602A6D112469DE2 ON conversation_category (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE favorite (id SERIAL NOT NULL, account_user_id INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_68C58ED96E45C7DD ON favorite (account_user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE favorite_conversation (favorite_id INT NOT NULL, conversation_id INT NOT NULL, PRIMARY KEY(favorite_id, conversation_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_65982486AA17481D ON favorite_conversation (favorite_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_659824869AC0396 ON favorite_conversation (conversation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE invoice (id SERIAL NOT NULL, subscription_id INT NOT NULL, invoice_number VARCHAR(255) NOT NULL, issued_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_906517449A1887DC ON invoice (subscription_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice.issued_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification (id SERIAL NOT NULL, users_id INT NOT NULL, type VARCHAR(50) NOT NULL, message TEXT NOT NULL, is_read BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BF5476CA67B3B43D ON notification (users_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN notification.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment (id SERIAL NOT NULL, subscription_id INT NOT NULL, amount NUMERIC(5, 2) NOT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, payment_method VARCHAR(50) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6D28840D9A1887DC ON payment (subscription_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN payment.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE plan (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, price NUMERIC(5, 2) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE report (id SERIAL NOT NULL, reported_by_user_id INT NOT NULL, conversation_id INT NOT NULL, reason TEXT NOT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F7784B79DEF28 ON report (reported_by_user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C42F77849AC0396 ON report (conversation_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN report.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE subscription (id SERIAL NOT NULL, plan_id INT NOT NULL, duration INT NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A3C664D3E899029B ON subscription (plan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE subscription_history (id SERIAL NOT NULL, subscriber_id INT NOT NULL, subscription_id INT NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_54AF90D07808B1AD ON subscription_history (subscriber_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_54AF90D09A1887DC ON subscription_history (subscription_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN subscription_history.start_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN subscription_history.end_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tag (id SERIAL NOT NULL, name VARCHAR(30) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tag_conversation (tag_id INT NOT NULL, conversation_id INT NOT NULL, PRIMARY KEY(tag_id, conversation_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2A980DEBBAD26311 ON tag_conversation (tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2A980DEB9AC0396 ON tag_conversation (conversation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE accounts ADD CONSTRAINT FK_CAC89EACDDE45DDE FOREIGN KEY (current_subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C40C86FCE FOREIGN KEY (publisher_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526CBF2AF943 FOREIGN KEY (parent_comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD CONSTRAINT FK_4C62E63867B3B43D FOREIGN KEY (users_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E961220EA6 FOREIGN KEY (creator_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_category ADD CONSTRAINT FK_E602A6D19AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_category ADD CONSTRAINT FK_E602A6D112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED96E45C7DD FOREIGN KEY (account_user_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite_conversation ADD CONSTRAINT FK_65982486AA17481D FOREIGN KEY (favorite_id) REFERENCES favorite (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite_conversation ADD CONSTRAINT FK_659824869AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD CONSTRAINT FK_906517449A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA67B3B43D FOREIGN KEY (users_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F7784B79DEF28 FOREIGN KEY (reported_by_user_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report ADD CONSTRAINT FK_C42F77849AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3E899029B FOREIGN KEY (plan_id) REFERENCES plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription_history ADD CONSTRAINT FK_54AF90D07808B1AD FOREIGN KEY (subscriber_id) REFERENCES accounts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription_history ADD CONSTRAINT FK_54AF90D09A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_conversation ADD CONSTRAINT FK_2A980DEBBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_conversation ADD CONSTRAINT FK_2A980DEB9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE accounts DROP CONSTRAINT FK_CAC89EACDDE45DDE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP CONSTRAINT FK_9474526C9AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP CONSTRAINT FK_9474526C40C86FCE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP CONSTRAINT FK_9474526CBF2AF943
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP CONSTRAINT FK_4C62E63867B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation DROP CONSTRAINT FK_8A8E26E961220EA6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_category DROP CONSTRAINT FK_E602A6D19AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation_category DROP CONSTRAINT FK_E602A6D112469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite DROP CONSTRAINT FK_68C58ED96E45C7DD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite_conversation DROP CONSTRAINT FK_65982486AA17481D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorite_conversation DROP CONSTRAINT FK_659824869AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP CONSTRAINT FK_906517449A1887DC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA67B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP CONSTRAINT FK_6D28840D9A1887DC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F7784B79DEF28
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report DROP CONSTRAINT FK_C42F77849AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D3E899029B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription_history DROP CONSTRAINT FK_54AF90D07808B1AD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription_history DROP CONSTRAINT FK_54AF90D09A1887DC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_conversation DROP CONSTRAINT FK_2A980DEBBAD26311
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_conversation DROP CONSTRAINT FK_2A980DEB9AC0396
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE accounts
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE comment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE contact
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE conversation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE conversation_category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE favorite
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE favorite_conversation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE invoice
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE plan
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE report
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE subscription
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE subscription_history
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tag_conversation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
