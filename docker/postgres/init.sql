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
DROP TABLE IF EXISTS messages CASCADE;
DROP TABLE IF EXISTS conversations CASCADE;
DROP TABLE IF EXISTS templates CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS tags CASCADE;
DROP TABLE IF EXISTS accounts CASCADE;

DROP TYPE IF EXISTS message_status CASCADE;
DROP TYPE IF EXISTS message_sender CASCADE;
DROP TYPE IF EXISTS message_type CASCADE;
DROP TYPE IF EXISTS conversation_status CASCADE;
DROP TYPE IF EXISTS subscription_status CASCADE;
DROP TYPE IF EXISTS payment_status CASCADE;


CREATE TYPE message_status AS ENUM ('draft', 'published');
CREATE TYPE message_sender AS ENUM ('user', 'interlocutor');
CREATE TYPE message_type AS ENUM ('text', 'image', 'video');
CREATE TYPE conversation_status AS ENUM ('draft', 'published');
CREATE TYPE subscription_status AS ENUM ('active', 'expired', 'canceled');
CREATE TYPE payment_status AS ENUM ('pending', 'completed', 'failed');


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

CREATE TABLE templates (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  type VARCHAR(50) NOT NULL,
  content TEXT NOT NULL,
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
  interlocutor_name VARCHAR(50) NOT NULL,
  interlocutor_username VARCHAR(30) NOT NULL,
  interlocutor_avatar_url VARCHAR(255) NULL,
  start_time TIMESTAMP NOT NULL,
  battery_level INT NOT NULL,
  network_type VARCHAR(10) NOT NULL,
  signal_quality VARCHAR(10) NOT NULL,
  template_id INT NOT NULL REFERENCES templates(id)
);

CREATE TABLE messages (
  id SERIAL PRIMARY KEY,
  conversation_id INT NOT NULL REFERENCES conversations(id),
  status message_status NOT NULL,
  sender message_sender NOT NULL,
  content JSON NOT NULL,
  type message_type NOT NULL,
  image_url BYTEA NULL,
  video_url BYTEA NULL,
  sent_at TIMESTAMP NOT NULL,
  updated_at TIMESTAMP NOT NULL,
  template_id INT NOT NULL REFERENCES templates(id)
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
  conversation_id INT NOT NULL REFERENCES conversations(id),
  created_at TIMESTAMP NOT NULL
);

CREATE TABLE likes_publication (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES accounts(id),
  conversation_id INT NOT NULL REFERENCES messages(id),
  created_at TIMESTAMP NOT NULL
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
  image_url BYTEA,
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
  features TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL
);

CREATE TABLE subscriptions (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES accounts(id),
  plan_id INT NOT NULL REFERENCES plans(id),
  start_date TIMESTAMP NOT NULL,
  end_date TIMESTAMP NOT NULL,
  status subscription_status NOT NULL
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
  target_type VARCHAR(20) NOT NULL,
  target_id INT NOT NULL,
  reason TEXT NOT NULL,
  status VARCHAR(20) NOT NULL,
  created_at TIMESTAMP NOT NULL
);
