-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 11, 2026 at 08:36 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mediateque`
--

-- --------------------------------------------------------

--
-- Table structure for table `adherent`
--

CREATE TABLE `adherent` (
  `Id_Adherent` int NOT NULL,
  `nomadh` varchar(50) DEFAULT NULL,
  `prenomadh` varchar(50) DEFAULT NULL,
  `datenaissance` date DEFAULT NULL,
  `adresseadh` varchar(50) DEFAULT NULL,
  `mailadh` varchar(50) DEFAULT NULL,
  `telephoneadh` varchar(50) DEFAULT NULL,
  `Id_Adherent_1` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `adherent`
--

INSERT INTO `adherent` (`Id_Adherent`, `nomadh`, `prenomadh`, `datenaissance`, `adresseadh`, `mailadh`, `telephoneadh`, `Id_Adherent_1`) VALUES
(1, 'Dupont', 'Jean', '1998-04-12', '12 rue Victor Hugo', 'jean.dupont@email.fr', '0612345678', NULL),
(2, 'Martin', 'Sophie', '2001-09-25', '8 rue de Paris', 'sophie.martin@email.fr', '0623456789', NULL),
(3, 'Bernard', 'Lucas', '1995-02-18', '25 avenue de la République', 'lucas.bernard@email.fr', '0634567890', NULL),
(4, 'Robert', 'Emma', '2003-07-30', '4 rue Pasteur', 'emma.robert@email.fr', '0645678901', NULL),
(5, 'Petit', 'Thomas', '1999-11-05', '17 rue Victor Hugo', 'thomas.petit@email.fr', '0656789012', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `carte`
--

CREATE TABLE `carte` (
  `Id_Carte` int NOT NULL,
  `dateemission` date DEFAULT NULL,
  `Id_Adherent` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `codedewey`
--

CREATE TABLE `codedewey` (
  `codewey` varchar(50) NOT NULL,
  `libelledewey` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `codedewey`
--

INSERT INTO `codedewey` (`codewey`, `libelledewey`) VALUES
('000', 'Informatique et information'),
('100', 'Philosophie'),
('200', 'Religion'),
('300', 'Sciences sociales'),
('500', 'Sciences'),
('600', 'Technologie'),
('800', 'Littérature'),
('900', 'Histoire et géographie');

-- --------------------------------------------------------

--
-- Table structure for table `emprunt`
--

CREATE TABLE `emprunt` (
  `Id_Emprunt` int NOT NULL,
  `dateemprrunt` date DEFAULT NULL,
  `dateretour` date DEFAULT NULL,
  `Id_Adherent` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emprunter`
--

CREATE TABLE `emprunter` (
  `Id_Exemplaire` int NOT NULL,
  `Id_Emprunt` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exemplaire`
--

CREATE TABLE `exemplaire` (
  `Id_Exemplaire` int NOT NULL,
  `etat` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exemplaire`
--

INSERT INTO `exemplaire` (`Id_Exemplaire`, `etat`) VALUES
(1, 'Bon état'),
(2, 'Très bon état'),
(3, 'Bon état'),
(4, 'Neuf'),
(5, 'Abîmé'),
(6, 'Très bon état'),
(7, 'Bon état');

-- --------------------------------------------------------

--
-- Table structure for table `livre`
--

CREATE TABLE `livre` (
  `Id_Livre` int NOT NULL,
  `titre` varchar(50) DEFAULT NULL,
  `datesortie` date DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `dateachat` date DEFAULT NULL,
  `codewey` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posseder`
--

CREATE TABLE `posseder` (
  `Id_Livre` int NOT NULL,
  `Id_Exemplaire` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adherent`
--
ALTER TABLE `adherent`
  ADD PRIMARY KEY (`Id_Adherent`),
  ADD KEY `Id_Adherent_1` (`Id_Adherent_1`);

--
-- Indexes for table `carte`
--
ALTER TABLE `carte`
  ADD PRIMARY KEY (`Id_Carte`),
  ADD KEY `Id_Adherent` (`Id_Adherent`);

--
-- Indexes for table `codedewey`
--
ALTER TABLE `codedewey`
  ADD PRIMARY KEY (`codewey`);

--
-- Indexes for table `emprunt`
--
ALTER TABLE `emprunt`
  ADD PRIMARY KEY (`Id_Emprunt`),
  ADD KEY `Id_Adherent` (`Id_Adherent`);

--
-- Indexes for table `emprunter`
--
ALTER TABLE `emprunter`
  ADD PRIMARY KEY (`Id_Exemplaire`,`Id_Emprunt`),
  ADD KEY `Id_Emprunt` (`Id_Emprunt`);

--
-- Indexes for table `exemplaire`
--
ALTER TABLE `exemplaire`
  ADD PRIMARY KEY (`Id_Exemplaire`);

--
-- Indexes for table `livre`
--
ALTER TABLE `livre`
  ADD PRIMARY KEY (`Id_Livre`),
  ADD KEY `codewey` (`codewey`);

--
-- Indexes for table `posseder`
--
ALTER TABLE `posseder`
  ADD PRIMARY KEY (`Id_Livre`,`Id_Exemplaire`),
  ADD KEY `Id_Exemplaire` (`Id_Exemplaire`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adherent`
--
ALTER TABLE `adherent`
  MODIFY `Id_Adherent` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `carte`
--
ALTER TABLE `carte`
  MODIFY `Id_Carte` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emprunt`
--
ALTER TABLE `emprunt`
  MODIFY `Id_Emprunt` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exemplaire`
--
ALTER TABLE `exemplaire`
  MODIFY `Id_Exemplaire` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `livre`
--
ALTER TABLE `livre`
  MODIFY `Id_Livre` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adherent`
--
ALTER TABLE `adherent`
  ADD CONSTRAINT `adherent_ibfk_1` FOREIGN KEY (`Id_Adherent_1`) REFERENCES `adherent` (`Id_Adherent`);

--
-- Constraints for table `carte`
--
ALTER TABLE `carte`
  ADD CONSTRAINT `carte_ibfk_1` FOREIGN KEY (`Id_Adherent`) REFERENCES `adherent` (`Id_Adherent`);

--
-- Constraints for table `emprunt`
--
ALTER TABLE `emprunt`
  ADD CONSTRAINT `emprunt_ibfk_1` FOREIGN KEY (`Id_Adherent`) REFERENCES `adherent` (`Id_Adherent`);

--
-- Constraints for table `emprunter`
--
ALTER TABLE `emprunter`
  ADD CONSTRAINT `emprunter_ibfk_1` FOREIGN KEY (`Id_Exemplaire`) REFERENCES `exemplaire` (`Id_Exemplaire`),
  ADD CONSTRAINT `emprunter_ibfk_2` FOREIGN KEY (`Id_Emprunt`) REFERENCES `emprunt` (`Id_Emprunt`);

--
-- Constraints for table `livre`
--
ALTER TABLE `livre`
  ADD CONSTRAINT `livre_ibfk_1` FOREIGN KEY (`codewey`) REFERENCES `codedewey` (`codewey`);

--
-- Constraints for table `posseder`
--
ALTER TABLE `posseder`
  ADD CONSTRAINT `posseder_ibfk_1` FOREIGN KEY (`Id_Livre`) REFERENCES `livre` (`Id_Livre`),
  ADD CONSTRAINT `posseder_ibfk_2` FOREIGN KEY (`Id_Exemplaire`) REFERENCES `exemplaire` (`Id_Exemplaire`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
