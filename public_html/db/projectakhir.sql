-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 13 Jun 2026 pada 15.10
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projectakhir`
--
USE `u169077025_db_fathurshop`;
DELIMITER $$
--
-- Fungsi
--
CREATE FUNCTION `fn_format_rupiah` (`amount` DECIMAL(15,2)) RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    RETURN CONCAT('Rp ', FORMAT(amount, 0, 'de_DE'));
END$$

CREATE FUNCTION `fn_user_total_belanja` (`p_user_id` INT) RETURNS DECIMAL(15,2) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE total DECIMAL(15,2);

    SELECT COALESCE(SUM(total_price), 0)
    INTO total
    FROM orders
    WHERE user_id = p_user_id
      AND order_status IN ('paid', 'confirmed');

    RETURN total;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_credentials`
--

CREATE TABLE `account_credentials` (
  `credential_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `account_email` varchar(255) NOT NULL,
  `account_password` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `account_credentials`
--

INSERT INTO `account_credentials` (`credential_id`, `listing_id`, `account_email`, `account_password`, `notes`, `created_at`) VALUES
(1, 3, 'faturyk65@gmail.com', 'Thurz', '1', '2026-06-13 05:35:38'),
(11, 4, 'aku123@gmail.com', 'aku123', '123', '2026-06-13 11:59:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_listing`
--

CREATE TABLE `account_listing` (
  `listing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `id` varchar(100) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `server` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `level` int(11) DEFAULT NULL,
  `rank` varchar(50) DEFAULT NULL,
  `account_login_type` varchar(50) DEFAULT NULL,
  `status` enum('ready','sold') NOT NULL DEFAULT 'ready',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `account_listing`
--

INSERT INTO `account_listing` (`listing_id`, `user_id`, `game_id`, `id`, `title`, `description`, `server`, `image_url`, `price`, `level`, `rank`, `account_login_type`, `status`, `created_at`, `updated_at`) VALUES
(3, 1, 2, '3131231223', 'Test', 'Testting', 'ASIA', 'assets/uploads/listings/listing_1_1781328938_d3724032.jpg', 200000.00, 60, 'Mytic', 'Email', 'sold', '2026-06-13 05:35:38', '2026-06-13 05:44:29'),
(4, 3, 1, '21321312312', 'Test', 'testtt', 'ASIA', 'assets/uploads/listings/listing_3_1781351965_a87e93fa.jpg', 800000.00, 121, 'Immortal', 'Email', 'sold', '2026-06-13 11:59:25', '2026-06-13 12:06:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` text NOT NULL,
  `genre` varchar(50) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `listing_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `games`
--

INSERT INTO `games` (`id`, `name`, `image_url`, `genre`, `platform`, `listing_count`, `created_at`) VALUES
(1, 'Valorant', 'https://i.pinimg.com/736x/cf/ae/88/cfae886e263126f685510e2f45b82970.jpg', 'Tactical Shooter', 'PC', 1201, '2026-06-04 14:12:08'),
(2, 'Mobile Legends', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQf8Nd_t-goiTMb9piIKbt8MwubxvGgCY3QyQ&s', 'MOBA', 'Mobile', 3401, '2026-06-04 14:12:08'),
(3, 'Free Fire Max', 'https://play-lh.googleusercontent.com/EJ83sg58Oo2gAjMHFxFVLM6Z53kuH4_R0M7Yq7gts5fWSIlFchUlmskG1vJKMoncmfOxBXcgJyIaO-nak6sO-MM', 'Battle Royale', 'Mobile', 2101, '2026-06-04 14:12:08'),
(4, 'PUBG Mobile', 'https://upload.wikimedia.org/wikipedia/en/4/44/PlayerUnknown%27s_Battlegrounds_Mobile.webp', 'Battle Royale', 'Mobile', 1800, '2026-06-04 14:12:08'),
(5, 'Genshin Impact', 'https://static.wikia.nocookie.net/logopedia/images/b/bc/Genshin_Impact_Icon_Version_2.5.png/revision/latest/scale-to-width-down/250?cb=20240613170351', 'RPG', 'PC/Mobile', 981, '2026-06-04 14:12:08'),
(6, 'Star Rail', 'https://static.wikia.nocookie.net/houkai-star-rail/images/8/84/Honkai_Star_Rail_App.png/revision/latest/scale-to-width/360?cb=20260313085854', 'Turn-based RPG', 'PC/Mobile', 760, '2026-06-04 14:12:08'),
(7, 'Wuthering Waves', 'https://pbs.twimg.com/profile_images/2060328572104093696/PG5CnngX_400x400.jpg', 'RPG', 'PC/Mobile', 800, '2026-06-06 10:12:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL DEFAULT 0,
  `listing_id` int(11) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `order_status` enum('pending','paid','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `seller_id`, `listing_id`, `total_price`, `order_status`, `created_at`) VALUES
(3, 3, 1, 3, 200000.00, 'confirmed', '2026-06-13 05:44:29'),
(4, 1, 3, 4, 800000.00, 'cancelled', '2026-06-13 12:00:41'),
(5, 1, 3, 4, 800000.00, 'pending', '2026-06-13 12:06:20');

--
-- Trigger `orders`
--
DELIMITER $$
CREATE TRIGGER `trg_orders_after_cancel` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
    IF NEW.order_status = 'cancelled' AND OLD.order_status <> 'cancelled' THEN
        UPDATE account_listing
        SET status = 'ready'
        WHERE listing_id = NEW.listing_id
          AND status = 'sold';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_orders_after_insert` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    UPDATE account_listing
    SET status = 'sold'
    WHERE listing_id = NEW.listing_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'pending',
  `payment_proof` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `amount`, `payment_method`, `payment_status`, `payment_proof`, `paid_at`, `updated_at`) VALUES
(3, 3, 200000.00, 'QRIS', 'confirmed', 'assets/uploads/payments/proof_3_3_1781329487.jpg', '2026-06-13 05:45:10', '2026-06-13 05:45:10'),
(4, 4, 800000.00, 'QRIS', 'cancelled', NULL, NULL, '2026-06-13 12:05:41'),
(5, 5, 800000.00, 'QRIS', 'pending', NULL, NULL, '2026-06-13 12:06:20');

--
-- Trigger `payment`
--
DELIMITER $$
CREATE TRIGGER `trg_payment_after_update` AFTER UPDATE ON `payment` FOR EACH ROW BEGIN
    IF NEW.payment_status = 'paid' AND OLD.payment_status <> 'paid' THEN
        UPDATE orders
        SET order_status = 'paid'
        WHERE order_id = NEW.order_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `ID_User` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','banned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`ID_User`, `username`, `email`, `password`, `full_name`, `phone_number`, `role`, `status`, `created_at`) VALUES
(1, 'fathur', 'faturyk65@gmail.com', '$2y$10$v.QPyCofLeus9w1D/M0Ol.EpjdUWh486rsu3RBxIiYlix.YFZ/G5C', 'Faturrohim Agni Darma', '089637186819', 'user', 'banned', '2026-06-06 09:58:12'),
(2, 'Thurz', 'faturyk833@gmail.com', '$2y$10$lRnX/jNsJNsINqZD9OZ2MubQUYpK63TLoKOvOzLkTSSh8e3889xje', 'Thurz', '81111111111', 'admin', 'active', '2026-06-10 16:58:37'),
(3, 'Thurzz', 'faturyk8374@gmail.com', '$2y$10$pdIWr5sW6km0741IfalRcePAN84qYXBZ8qHmACWkTlaU85MjnlE56', 'Thurzzasd', '089637186819', 'user', 'active', '2026-06-12 07:25:35');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_listing_marketplace`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_listing_marketplace` (
`listing_id` int(11)
,`title` varchar(150)
,`description` text
,`server` varchar(255)
,`image_url` varchar(255)
,`price` decimal(15,2)
,`level` int(11)
,`rank` varchar(50)
,`account_login_type` varchar(50)
,`status` enum('ready','sold')
,`created_at` timestamp
,`game_id` int(11)
,`game_name` varchar(100)
,`game_genre` varchar(50)
,`game_platform` varchar(50)
,`seller_id` int(11)
,`seller_name` varchar(50)
,`seller_status` enum('active','banned')
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_order_detail`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_order_detail` (
`order_id` int(11)
,`total_price` decimal(15,2)
,`order_status` enum('pending','paid','confirmed','cancelled')
,`order_created_at` timestamp
,`listing_id` int(11)
,`listing_title` varchar(150)
,`game_name` varchar(100)
,`buyer_id` int(11)
,`buyer_username` varchar(50)
,`seller_id` int(11)
,`seller_username` varchar(50)
,`payment_id` int(11)
,`payment_method` varchar(50)
,`payment_status` varchar(50)
,`payment_proof` varchar(255)
,`paid_at` timestamp
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_listing_marketplace`
--
DROP TABLE IF EXISTS `v_listing_marketplace`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_listing_marketplace`  AS SELECT `al`.`listing_id` AS `listing_id`, `al`.`title` AS `title`, `al`.`description` AS `description`, `al`.`server` AS `server`, `al`.`image_url` AS `image_url`, `al`.`price` AS `price`, `al`.`level` AS `level`, `al`.`rank` AS `rank`, `al`.`account_login_type` AS `account_login_type`, `al`.`status` AS `status`, `al`.`created_at` AS `created_at`, `g`.`id` AS `game_id`, `g`.`name` AS `game_name`, `g`.`genre` AS `game_genre`, `g`.`platform` AS `game_platform`, `u`.`ID_User` AS `seller_id`, `u`.`username` AS `seller_name`, `u`.`status` AS `seller_status` FROM ((`account_listing` `al` join `games` `g` on(`g`.`id` = `al`.`game_id`)) join `users` `u` on(`u`.`ID_User` = `al`.`user_id`)) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_order_detail`
--
DROP TABLE IF EXISTS `v_order_detail`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_order_detail`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`total_price` AS `total_price`, `o`.`order_status` AS `order_status`, `o`.`created_at` AS `order_created_at`, `al`.`listing_id` AS `listing_id`, `al`.`title` AS `listing_title`, `g`.`name` AS `game_name`, `buyer`.`ID_User` AS `buyer_id`, `buyer`.`username` AS `buyer_username`, `seller`.`ID_User` AS `seller_id`, `seller`.`username` AS `seller_username`, `p`.`payment_id` AS `payment_id`, `p`.`payment_method` AS `payment_method`, `p`.`payment_status` AS `payment_status`, `p`.`payment_proof` AS `payment_proof`, `p`.`paid_at` AS `paid_at` FROM (((((`orders` `o` join `account_listing` `al` on(`al`.`listing_id` = `o`.`listing_id`)) join `games` `g` on(`g`.`id` = `al`.`game_id`)) join `users` `buyer` on(`buyer`.`ID_User` = `o`.`user_id`)) join `users` `seller` on(`seller`.`ID_User` = `o`.`seller_id`)) left join `payment` `p` on(`p`.`order_id` = `o`.`order_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `account_credentials`
--
ALTER TABLE `account_credentials`
  ADD PRIMARY KEY (`credential_id`),
  ADD UNIQUE KEY `uq_listing` (`listing_id`);

--
-- Indeks untuk tabel `account_listing`
--
ALTER TABLE `account_listing`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indeks untuk tabel `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indeks untuk tabel `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID_User`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `account_credentials`
--
ALTER TABLE `account_credentials`
  MODIFY `credential_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `account_listing`
--
ALTER TABLE `account_listing`
  MODIFY `listing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `ID_User` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `account_credentials`
--
ALTER TABLE `account_credentials`
  ADD CONSTRAINT `fk_cred_listing` FOREIGN KEY (`listing_id`) REFERENCES `account_listing` (`listing_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_listing`
--
ALTER TABLE `account_listing`
  ADD CONSTRAINT `account_listing_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID_User`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_listing_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID_User`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `account_listing` (`listing_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
