CREATE TABLE IF NOT EXISTS messages (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_sender INT NOT NULL,
    id_receiver INT NOT NULL, 
    sender_type ENUM('user', 'sponsor') NOT NULL,
    message_content TEXT NOT NULL,
    date_sent DATETIME NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    INDEX (id_sender),
    INDEX (id_receiver),
    INDEX (date_sent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 