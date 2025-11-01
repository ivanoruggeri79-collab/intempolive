CREATE DATABASE IF NOT EXISTS comune_segnalazioni CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE comune_segnalazioni;

CREATE TABLE IF NOT EXISTS segnalazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255) NOT NULL,
    descrizione TEXT NOT NULL,
    luogo VARCHAR(500) NOT NULL,
    tipo ENUM('strade', 'illuminazione', 'verde', 'spazzatura', 'sicurezza', 'altro') NOT NULL,
    media_path VARCHAR(500),
    nome_utente VARCHAR(100),
    email_utente VARCHAR(255),
    latitudine DECIMAL(10,8) DEFAULT NULL,
    longitudine DECIMAL(11,8) DEFAULT NULL,
    data_invio DATETIME DEFAULT CURRENT_TIMESTAMP,
    stato ENUM('in_attesa', 'approvata', 'rifiutata') DEFAULT 'in_attesa',
    data_approvazione DATETIME NULL
);