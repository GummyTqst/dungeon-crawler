-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Vært: localhost:8889
-- Genereringstid: 27. 02 2026 kl. 11:45:45
-- Serverversion: 8.0.44
-- PHP-version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dungeon-crawler`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `armor`
--

CREATE TABLE `armor` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `rarity` enum('common','rare','epic','unique') NOT NULL,
  `defense` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `armor`
--

INSERT INTO `armor` (`id`, `name`, `rarity`, `defense`) VALUES
(2, 'BattleRobe', 'rare', 15),
(3, 'Climbing Gear', 'rare', 25),
(4, 'Climbing Gear', 'rare', 25),
(6, 'Splendid Robe', 'unique', 30),
(7, 'Evocation Robe', 'epic', 25),
(8, 'Reinforced Mail', 'common', 15),
(9, 'Turtle Armor', 'rare', 15),
(10, 'Ender Armor', 'unique', 50);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `armor_properties`
--

CREATE TABLE `armor_properties` (
  `id` int NOT NULL,
  `armor_id` int NOT NULL,
  `property_name` varchar(50) NOT NULL,
  `value` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `armor_properties`
--

INSERT INTO `armor_properties` (`id`, `armor_id`, `property_name`, `value`) VALUES
(1, 4, 'artifact cooldown', '-40'),
(2, 4, 'Pushback resistance', '75'),
(5, 6, 'artifact damage', '50%'),
(6, 6, 'artifact cooldown', '-40%'),
(7, 6, 'melee damage', '30%'),
(8, 7, 'artifact cooldown', '-40%'),
(9, 7, 'movespeed', '+15%'),
(10, 8, 'Damage. reduction', '35%'),
(11, 8, 'Chance to negate hits', '30%'),
(12, 8, 'longer roll cooldown', '100%'),
(13, 9, 'Damage reduction', '35%'),
(14, 9, 'healing boost', '25%'),
(15, 10, 'chance to teleport away when hit', '5%'),
(16, 10, 'artifact cooldown', '25%'),
(17, 10, 'arrows per bundle', '+10');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `enchantments`
--

CREATE TABLE `enchantments` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `equipment_type` enum('weapon','armor','both') NOT NULL DEFAULT 'weapon',
  `enchantment_cost` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `enchantments`
--

INSERT INTO `enchantments` (`id`, `name`, `description`, `equipment_type`, `enchantment_cost`) VALUES
(2, 'Ambush', 'Attacks on mobs that are not actively targeting you deal more damage', 'weapon', 1),
(3, 'Anima Conduit', 'Each soul absorbed grants a small amount of health.', 'weapon', 2),
(4, 'Artifact Synergy', 'Using an artifact causes the next attack to deal bonus damage', 'weapon', 2),
(5, 'Busy Bee', 'Grants a chance for a bee to spawn after defeating a mob, with up to 3 bees joining the hero\'s side', 'weapon', 2),
(6, 'Chains', 'Has a 30% chance to chain a clister of mobs together and keep them bound for a short time', 'weapon', 2),
(7, 'Acrobat', 'Reduces the cooldown time between rolls', 'armor', 2),
(8, 'Chiling', 'Emits a blast every two seconds that reduces the movement and attack speed of nearby enemies for one second', 'armor', 2),
(9, 'Death Barter', 'The first set of Emeralds collected are stored and then spent to save the player from death', 'armor', 2),
(10, 'Deflect', 'Grants a small chance to deflect incoming projectiles', 'armor', 2),
(11, 'Luck of the Sea', 'Increase luck, boosting the chance of unique rare drops', 'armor', 2);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `enchantment_tiers`
--

CREATE TABLE `enchantment_tiers` (
  `id` int NOT NULL,
  `enchantment_id` int NOT NULL,
  `tier_level` int NOT NULL,
  `value` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `enchantment_tiers`
--

INSERT INTO `enchantment_tiers` (`id`, `enchantment_id`, `tier_level`, `value`) VALUES
(1, 3, 1, '3'),
(2, 3, 2, '6'),
(3, 3, 3, '9'),
(4, 2, 1, '20'),
(5, 2, 2, '40'),
(6, 2, 3, '60'),
(7, 4, 1, '40'),
(8, 4, 2, '60'),
(9, 4, 3, '80'),
(10, 5, 1, '20'),
(11, 5, 2, '30'),
(12, 5, 3, '40'),
(13, 6, 1, '1'),
(14, 6, 2, '2'),
(15, 6, 3, '3'),
(16, 7, 1, '15'),
(17, 7, 2, '30'),
(18, 7, 3, '45'),
(19, 8, 1, '-20'),
(20, 8, 2, '-40'),
(21, 8, 3, '-60'),
(22, 9, 1, '150'),
(23, 9, 2, '100'),
(24, 9, 3, '50'),
(25, 10, 1, '25'),
(26, 10, 2, '35'),
(27, 10, 3, '45'),
(28, 11, 1, '10'),
(29, 11, 2, '20'),
(30, 11, 3, '30');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `heroes`
--

CREATE TABLE `heroes` (
  `id` int NOT NULL,
  `name` varchar(15) NOT NULL,
  `level` int NOT NULL,
  `player_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `heroes`
--

INSERT INTO `heroes` (`id`, `name`, `level`, `player_id`) VALUES
(1, 'Steve', 15, 1),
(2, 'Alex', 35, 2);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `hero_armor`
--

CREATE TABLE `hero_armor` (
  `id` int NOT NULL,
  `hero_id` int NOT NULL,
  `armor_id` int NOT NULL,
  `is_equipped` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `hero_armor`
--

INSERT INTO `hero_armor` (`id`, `hero_id`, `armor_id`, `is_equipped`) VALUES
(2, 2, 6, 1),
(3, 2, 6, 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `hero_weapon`
--

CREATE TABLE `hero_weapon` (
  `id` int NOT NULL,
  `hero_id` int NOT NULL,
  `weapon_id` int NOT NULL,
  `is_equipped` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `hero_weapon`
--

INSERT INTO `hero_weapon` (`id`, `hero_id`, `weapon_id`, `is_equipped`) VALUES
(1, 1, 1, 1),
(2, 2, 2, 1),
(3, 1, 2, 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `players`
--

CREATE TABLE `players` (
  `id` int NOT NULL,
  `username` varchar(15) NOT NULL,
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `players`
--

INSERT INTO `players` (`id`, `username`, `created_at`) VALUES
(1, 'Test', '2026-02-19'),
(2, 'NoArm', '2026-02-18'),
(3, 'NoArm', '2026-02-18');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `weapons`
--

CREATE TABLE `weapons` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `rarity` enum('common','rare','epic','unique') NOT NULL,
  `power` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data dump for tabellen `weapons`
--

INSERT INTO `weapons` (`id`, `name`, `rarity`, `power`) VALUES
(1, 'Diamon Sword', 'common', 16),
(2, 'Anchor', 'rare', 100),
(5, 'Axe', 'rare', 100),
(6, 'Boneclub', 'rare', 30),
(7, 'Nameless Blade', 'unique', 100),
(8, 'Soul Knife', 'epic', 50);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `weapon_enchantments`
--

CREATE TABLE `weapon_enchantments` (
  `weapon_id` int NOT NULL,
  `enchantment_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `armor`
--
ALTER TABLE `armor`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `armor_properties`
--
ALTER TABLE `armor_properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `armor_id` (`armor_id`);

--
-- Indeks for tabel `enchantments`
--
ALTER TABLE `enchantments`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `enchantment_tiers`
--
ALTER TABLE `enchantment_tiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enchantment_id` (`enchantment_id`);

--
-- Indeks for tabel `heroes`
--
ALTER TABLE `heroes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_heroes_player` (`player_id`);

--
-- Indeks for tabel `hero_armor`
--
ALTER TABLE `hero_armor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hero_id` (`hero_id`),
  ADD KEY `armor_id` (`armor_id`);

--
-- Indeks for tabel `hero_weapon`
--
ALTER TABLE `hero_weapon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hero_id` (`hero_id`),
  ADD KEY `weapon_id` (`weapon_id`);

--
-- Indeks for tabel `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `weapons`
--
ALTER TABLE `weapons`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `weapon_enchantments`
--
ALTER TABLE `weapon_enchantments`
  ADD PRIMARY KEY (`weapon_id`),
  ADD KEY `fk_enchantments` (`enchantment_id`);

--
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `armor`
--
ALTER TABLE `armor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tilføj AUTO_INCREMENT i tabel `armor_properties`
--
ALTER TABLE `armor_properties`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tilføj AUTO_INCREMENT i tabel `enchantments`
--
ALTER TABLE `enchantments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Tilføj AUTO_INCREMENT i tabel `enchantment_tiers`
--
ALTER TABLE `enchantment_tiers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Tilføj AUTO_INCREMENT i tabel `heroes`
--
ALTER TABLE `heroes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tilføj AUTO_INCREMENT i tabel `hero_armor`
--
ALTER TABLE `hero_armor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tilføj AUTO_INCREMENT i tabel `hero_weapon`
--
ALTER TABLE `hero_weapon`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tilføj AUTO_INCREMENT i tabel `players`
--
ALTER TABLE `players`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tilføj AUTO_INCREMENT i tabel `weapons`
--
ALTER TABLE `weapons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tilføj AUTO_INCREMENT i tabel `weapon_enchantments`
--
ALTER TABLE `weapon_enchantments`
  MODIFY `weapon_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `armor_properties`
--
ALTER TABLE `armor_properties`
  ADD CONSTRAINT `armor_properties_ibfk_1` FOREIGN KEY (`armor_id`) REFERENCES `armor` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `enchantment_tiers`
--
ALTER TABLE `enchantment_tiers`
  ADD CONSTRAINT `enchantment_tiers_ibfk_1` FOREIGN KEY (`enchantment_id`) REFERENCES `enchantments` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `heroes`
--
ALTER TABLE `heroes`
  ADD CONSTRAINT `fk_heroes_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `hero_armor`
--
ALTER TABLE `hero_armor`
  ADD CONSTRAINT `hero_armor_ibfk_1` FOREIGN KEY (`hero_id`) REFERENCES `heroes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hero_armor_ibfk_2` FOREIGN KEY (`armor_id`) REFERENCES `armor` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `hero_weapon`
--
ALTER TABLE `hero_weapon`
  ADD CONSTRAINT `hero_weapon_ibfk_1` FOREIGN KEY (`hero_id`) REFERENCES `heroes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hero_weapon_ibfk_2` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `weapon_enchantments`
--
ALTER TABLE `weapon_enchantments`
  ADD CONSTRAINT `fk_enchantments` FOREIGN KEY (`enchantment_id`) REFERENCES `enchantments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_weapons_enchantments` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
