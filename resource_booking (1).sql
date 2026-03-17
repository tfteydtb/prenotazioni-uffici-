-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Mar 17, 2026 alle 11:49
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resource_booking`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `resource_id`, `start_time`, `end_time`, `status`) VALUES
(1, 1, 3, '2026-03-13 14:00:00', '2026-03-13 15:00:00', 'cancellata'),
(2, 1, 1, '2026-03-13 12:00:00', '2026-03-13 13:00:00', 'attiva'),
(3, 1, 3, '2026-03-13 16:00:00', '2026-03-13 17:00:00', 'attiva'),
(4, 1, 1, '2026-03-13 09:00:00', '2026-03-13 10:00:00', 'attiva');

-- --------------------------------------------------------

--
-- Struttura della tabella `locks`
--

CREATE TABLE `locks` (
  `id` int(11) NOT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `expiration_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `locks`
--

INSERT INTO `locks` (`id`, `resource_id`, `user_id`, `start_time`, `expiration_time`) VALUES
(10, 3, 1, '2026-03-13 13:00:00', '2026-03-13 09:03:13');

-- --------------------------------------------------------

--
-- Struttura della tabella `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `stato` varchar(20) DEFAULT 'attiva'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `resources`
--

INSERT INTO `resources` (`id`, `nome`, `tipo`, `descrizione`, `stato`) VALUES
(1, 'Sala Riunioni A', 'sala', 'Sala con proiettore', 'attiva'),
(2, 'Postazione 1', 'desk', 'Postazione coworking', 'attiva'),
(3, 'Proiettore', 'attrezzatura', 'Proiettore portatile', 'attiva');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `nome`, `email`, `password`) VALUES
(1, 'Demo User', 'demo@test.com', '1234');

-- --------------------------------------------------------

--
-- Struttura della tabella `waitlist`
--

CREATE TABLE `waitlist` (
  `id` int(11) NOT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `request_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indici per le tabelle `locks`
--
ALTER TABLE `locks`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `waitlist`
--
ALTER TABLE `waitlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `locks`
--
ALTER TABLE `locks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `waitlist`
--
ALTER TABLE `waitlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
