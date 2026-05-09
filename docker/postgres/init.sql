DROP TABLE IF EXISTS conversation_tags CASCADE;
DROP TABLE IF EXISTS reports CASCADE;
DROP TABLE IF EXISTS subscriptions CASCADE;
DROP TABLE IF EXISTS plans CASCADE;
DROP TABLE IF EXISTS topics_comments CASCADE;
DROP TABLE IF EXISTS topics CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS contacts CASCADE;
DROP TABLE IF EXISTS invoices CASCADE;
DROP TABLE IF EXISTS payments CASCADE;
DROP TABLE IF EXISTS likes CASCADE;
DROP TABLE IF EXISTS favorites CASCADE;
DROP TABLE IF EXISTS comments CASCADE;
DROP TABLE IF EXISTS conversations CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS tags CASCADE;
DROP TABLE IF EXISTS accounts CASCADE;

DROP TYPE IF EXISTS conversation_status CASCADE;
DROP TYPE IF EXISTS subscription_status CASCADE;
DROP TYPE IF EXISTS payment_status CASCADE;
DROP TYPE IF EXISTS report_status_enum CASCADE;

CREATE TYPE conversation_status AS ENUM ('draft', 'published');
CREATE TYPE subscription_status AS ENUM ('active', 'expired', 'canceled');
CREATE TYPE payment_status AS ENUM ('pending', 'completed', 'failed');
CREATE TYPE report_status_enum AS ENUM ('pending', 'resolved', 'rejected', 'in_progress', 'closed');

CREATE TABLE accounts (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  username VARCHAR(30) NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  avatar_url VARCHAR(255) NULL,
  bio TEXT NULL,
  role JSON NOT NULL,
  is_verified BOOLEAN NOT NULL DEFAULT FALSE,
  reset_password_token VARCHAR(255) NULL,
  reset_token_expires_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL
);

CREATE TABLE categories (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  created_at TIMESTAMP NOT NULL
);


CREATE TABLE conversations (
  id SERIAL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  description TEXT NULL,
  category_id INT NOT NULL REFERENCES categories(id),
  creator_id INT NOT NULL REFERENCES accounts(id),
  created_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  views INT NOT NULL DEFAULT 0,
  is_public BOOLEAN NOT NULL DEFAULT TRUE,
  status conversation_status NOT NULL,
  content JSON NOT NULL
);


CREATE TABLE comments (
  id SERIAL PRIMARY KEY,
  conversation_id INT NOT NULL REFERENCES conversations(id),
  user_id INT NOT NULL REFERENCES accounts(id),
  content TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL,
  parent_comment_id INT REFERENCES comments(id)
);

CREATE TABLE favorites (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES accounts(id),
  conversation_id INT NOT NULL REFERENCES conversations(id)
);


CREATE TABLE payments (
  id SERIAL PRIMARY KEY,
  subscription_id INT NOT NULL REFERENCES subscriptions(id),
  amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  status payment_status NOT NULL,
  created_at TIMESTAMP NOT NULL
);

CREATE TABLE invoices (
  id SERIAL PRIMARY KEY,
  subscription_id INT NOT NULL REFERENCES subscriptions(id),
  invoice_number VARCHAR(255) NOT NULL,
  issued_date TIMESTAMP NOT NULL
);

CREATE TABLE contacts (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES accounts(id),
  email VARCHAR(100) NOT NULL,
  subject VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL
);

CREATE TABLE notifications (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES accounts(id),
  type VARCHAR(50) NOT NULL,
  message TEXT NOT NULL,
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NOT NULL
);

CREATE TABLE topics (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES accounts(id),
  title VARCHAR(100) NOT NULL,
  content TEXT NOT NULL,
  image_url VARCHAR(255),
  conversation_id INT NOT NULL REFERENCES conversations(id),
  created_at TIMESTAMP NOT NULL
);

CREATE TABLE topics_comments (
  id SERIAL PRIMARY KEY,
  topics_id INT NOT NULL REFERENCES topics(id),
  user_id INT NOT NULL REFERENCES accounts(id),
  content TEXT NOT NULL,
  parent_comment_id INT REFERENCES topics_comments(id),
  created_at TIMESTAMP NOT NULL
);

CREATE TABLE plans (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description TEXT NOT NULL
);

CREATE TABLE subscriptions (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES accounts(id),
  plan_id INT NOT NULL REFERENCES plans(id),
  duration TIMESTAMP NOT NULL,
  status subscription_status NOT NULL
);

CREATE TABLE subscription_history (
    id SERIAL PRIMARY KEY, 
    start_at TIMESTAMP NOT NULL,
    end_at TIMESTAMP NOT NULL, 
    subscriber_id INT NOT NULL,
    subscription_id INT NOT NULL,
    CONSTRAINT fk_subscriber FOREIGN KEY (subscriber_id) REFERENCES accounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
);

CREATE TABLE tags (
  id SERIAL PRIMARY KEY,
  name VARCHAR(30) NOT NULL
);

CREATE TABLE conversation_tags (
  conversation_id INT NOT NULL REFERENCES conversations(id),
  tag_id INT NOT NULL REFERENCES tags(id),
  PRIMARY KEY (conversation_id, tag_id)
);

CREATE TABLE reports (
  id SERIAL PRIMARY KEY,
  reported_by_user_id INT NOT NULL REFERENCES accounts(id),
  reason TEXT NOT NULL,
  status report_status_enum NOT NULL,
  created_at TIMESTAMP NOT NULL,
  conversation_id INT NOT NULL REFERENCES conversations(id)
);
