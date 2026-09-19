-- ============================================================
-- TASK-3 · Backend Development & Database Integration
-- Schema: roles (lookup table) + users (3NF: role stored as FK,
-- not repeated as a string on every row).
-- ============================================================

CREATE DATABASE IF NOT EXISTS apexplanet_task3
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE apexplanet_task3;

CREATE TABLE roles (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO roles (id, name) VALUES (1, 'admin'), (2, 'user');

CREATE TABLE users (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  username         VARCHAR(50)  NOT NULL UNIQUE,
  email            VARCHAR(100) NOT NULL UNIQUE,
  password_hash    VARCHAR(255) NOT NULL,
  role_id          INT NOT NULL DEFAULT 2,
  profile_picture  VARCHAR(255) DEFAULT NULL,
  bio              VARCHAR(255) DEFAULT NULL,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Everyone registers as a plain 'user' (role_id = 2) through
-- register.php. To make yourself an admin after signing up,
-- run this once with your own username:
--
--   UPDATE users SET role_id = 1 WHERE username = 'your_username';
-- ------------------------------------------------------------
