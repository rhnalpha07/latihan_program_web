-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS db_form;

-- Use the database
USE db_form;

-- Create the kontak table
CREATE TABLE IF NOT EXISTS kontak (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    pesan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO kontak (nama, email, pesan) VALUES
('Andi', 'andi@example.com', 'Halo, ini pesan pertama.');
