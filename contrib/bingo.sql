SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `cards` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `gameName` varchar(32) NOT NULL,
  `grid` text NOT NULL,
  `marked` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `gameName` varchar(32) NOT NULL,
  `balls` text NOT NULL,
  `called` text NOT NULL,
  `autoCall` int(11) NOT NULL DEFAULT 0,
  `ended` tinyint(1) NOT NULL DEFAULT 0,
  `winner` int(11) DEFAULT NULL,
  `winnerName` varchar(32) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `gameToken` varchar(64) NOT NULL,
  `twitchId` int(11) DEFAULT NULL,
  `accessToken` varchar(64) NOT NULL,
  `refreshToken` varchar(64) NOT NULL,
  `host` tinyint(1) NOT NULL DEFAULT 0,
  `created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userId_2` (`userId`,`gameName`),
  ADD KEY `gameName` (`gameName`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gameName` (`gameName`),
  ADD KEY `userId` (`userId`),
  ADD KEY `winner` (`winner`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gameToken` (`gameToken`),
  ADD UNIQUE KEY `twitchId` (`twitchId`),
  ADD KEY `name` (`name`);


ALTER TABLE `cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `cards`
  ADD CONSTRAINT `cards_ibfk_1` FOREIGN KEY (`gameName`) REFERENCES `games` (`gameName`) ON DELETE CASCADE,
  ADD CONSTRAINT `cards_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_2` FOREIGN KEY (`winner`) REFERENCES `cards` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `games_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
