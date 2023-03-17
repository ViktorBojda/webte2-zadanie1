-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+jammy2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 17, 2023 at 10:54 AM
-- Server version: 8.0.32-0ubuntu0.22.04.2
-- PHP Version: 8.2.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zadanie1`
--

-- --------------------------------------------------------

--
-- Table structure for table `game`
--

CREATE TABLE `game` (
  `id` int UNSIGNED NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_slovak_ci NOT NULL,
  `year` smallint NOT NULL,
  `game_order` tinyint UNSIGNED NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_slovak_ci NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_slovak_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovak_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_session`
--

CREATE TABLE `login_session` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(128) COLLATE utf8mb4_slovak_ci NOT NULL,
  `login` varchar(32) COLLATE utf8mb4_slovak_ci DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source` varchar(32) COLLATE utf8mb4_slovak_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovak_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person`
--

CREATE TABLE `person` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_slovak_ci NOT NULL,
  `surname` varchar(100) COLLATE utf8mb4_slovak_ci NOT NULL,
  `birth_day` date NOT NULL,
  `birth_place` varchar(100) COLLATE utf8mb4_slovak_ci NOT NULL,
  `birth_country` varchar(100) COLLATE utf8mb4_slovak_ci NOT NULL,
  `death_day` date DEFAULT NULL,
  `death_place` varchar(100) COLLATE utf8mb4_slovak_ci DEFAULT NULL,
  `death_country` varchar(100) COLLATE utf8mb4_slovak_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovak_ci;

-- --------------------------------------------------------

--
-- Table structure for table `placement`
--

CREATE TABLE `placement` (
  `id` int UNSIGNED NOT NULL,
  `person_id` int UNSIGNED NOT NULL,
  `game_id` int UNSIGNED NOT NULL,
  `placing` int NOT NULL,
  `discipline` varchar(200) COLLATE utf8mb4_slovak_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovak_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int UNSIGNED NOT NULL,
  `full_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_slovak_ci NOT NULL,
  `email` varchar(128) COLLATE utf8mb4_slovak_ci NOT NULL,
  `login` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_slovak_ci DEFAULT NULL,
  `password` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_slovak_ci DEFAULT NULL,
  `2fa_code` varchar(32) COLLATE utf8mb4_slovak_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `external_type` varchar(16) COLLATE utf8mb4_slovak_ci DEFAULT NULL,
  `external_id` varchar(64) COLLATE utf8mb4_slovak_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovak_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_action`
--

CREATE TABLE `user_action` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `login_session_id` int UNSIGNED NOT NULL,
  `action` varchar(32) COLLATE utf8mb4_slovak_ci NOT NULL,
  `table_name` varchar(32) COLLATE utf8mb4_slovak_ci NOT NULL,
  `record_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_slovak_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `game`
--
ALTER TABLE `game`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_session`
--
ALTER TABLE `login_session`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `placement`
--
ALTER TABLE `placement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `person_id` (`person_id`),
  ADD KEY `oh_id` (`game_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `external_id` (`external_id`);

--
-- Indexes for table `user_action`
--
ALTER TABLE `user_action`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_action_ibfk_1` (`user_id`),
  ADD KEY `login_session_id` (`login_session_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `game`
--
ALTER TABLE `game`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_session`
--
ALTER TABLE `login_session`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `person`
--
ALTER TABLE `person`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `placement`
--
ALTER TABLE `placement`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_action`
--
ALTER TABLE `user_action`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `placement`
--
ALTER TABLE `placement`
  ADD CONSTRAINT `placement_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `placement_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `game` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `user_action`
--
ALTER TABLE `user_action`
  ADD CONSTRAINT `user_action_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_action_ibfk_2` FOREIGN KEY (`login_session_id`) REFERENCES `login_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
