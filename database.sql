-- =====================================================
-- ONLINE APPOINTMENT MANAGEMENT SYSTEM
-- MySQL Database
-- =====================================================

CREATE DATABASE IF NOT EXISTS online_appointment_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE online_appointment_db;

DROP TABLE IF EXISTS appointment;
DROP TABLE IF EXISTS client;
DROP TABLE IF EXISTS status;
DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE client (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_client_user
        FOREIGN KEY (user_id)
        REFERENCES `user`(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE status (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE appointment (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    status_id INT NOT NULL,
    service VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_appointment_client
        FOREIGN KEY (client_id)
        REFERENCES client(client_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_appointment_status
        FOREIGN KEY (status_id)
        REFERENCES status(status_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO status (status_name) VALUES
('Pending'),
('Confirmed'),
('Completed');

-- A sample login is created by create_admin.php so the password
-- is stored using PHP's password_hash() instead of a plain password.
