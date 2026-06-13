-- ============================================================
-- Tambahan: tabel account_credentials
-- Jalankan query ini di phpMyAdmin atau MySQL CLI
-- ============================================================

CREATE TABLE IF NOT EXISTS `account_credentials` (
  `credential_id` int(11) NOT NULL AUTO_INCREMENT,
  `listing_id`    int(11) NOT NULL,
  `account_email` varchar(255) NOT NULL,
  `account_password` varchar(255) NOT NULL,
  `notes`         text DEFAULT NULL,
  `created_at`    timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`credential_id`),
  UNIQUE KEY `uq_listing` (`listing_id`),
  CONSTRAINT `fk_cred_listing` FOREIGN KEY (`listing_id`)
    REFERENCES `account_listing` (`listing_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
