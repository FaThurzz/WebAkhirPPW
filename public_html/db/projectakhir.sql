-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Jun 2026 pada 09.13
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
(2, 1, 5, '21321312312', 'Test', 'test aja', 'Asia', 'assets/uploads/listings/listing_1_1781086829_db29da14.jpg', 100000.00, 60, '-', 'Email', 'ready', '2026-06-10 10:20:29', '2026-06-10 10:20:29');

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
(1, 'Valorant', 'https://i.pinimg.com/736x/cf/ae/88/cfae886e263126f685510e2f45b82970.jpg', 'Tactical Shooter', 'PC', 1200, '2026-06-04 14:12:08'),
(2, 'Mobile Legends', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQf8Nd_t-goiTMb9piIKbt8MwubxvGgCY3QyQ&s', 'MOBA', 'Mobile', 3400, '2026-06-04 14:12:08'),
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
(1, 'fathur', 'faturyk65@gmail.com', '$2y$10$v.QPyCofLeus9w1D/M0Ol.EpjdUWh486rsu3RBxIiYlix.YFZ/G5C', 'Faturrohim Agni Darma', '089637186819', 'user', 'active', '2026-06-06 09:58:12'),
(2, 'Thurz', 'faturyk833@gmail.com', '$2y$10$lRnX/jNsJNsINqZD9OZ2MubQUYpK63TLoKOvOzLkTSSh8e3889xje', 'Thurz', '81111111111', 'admin', 'active', '2026-06-10 16:58:37');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT untuk tabel `account_listing`
--
ALTER TABLE `account_listing`
  MODIFY `listing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `ID_User` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

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
