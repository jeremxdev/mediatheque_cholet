-- ============================================================
-- EVOLUTION DU SCHEMA (cahier des charges)
-- Ajoute : code exemplaire, type de support, livres numeriques,
-- telechargements, code/ddate adhesion adherent.
-- ============================================================

-- 1. Exemplaire : code unique construit a partir de la classification
--    Dewey et du rang d'arrivee. Le rang = rang d'insertion (Id_Exemplaire).
ALTER TABLE `exemplaire`
  ADD COLUMN `code_exemplaire` VARCHAR(50) NULL AFTER `Id_Exemplaire`,
  ADD UNIQUE KEY `uq_code_exemplaire` (`code_exemplaire`);

-- 2. Livre : type de support (papier et/ou numerique)
ALTER TABLE `livre`
  ADD COLUMN `type_support` ENUM('papier','numerique','les deux') NOT NULL DEFAULT 'papier' AFTER `titre`;

-- 3. Livre numerique : disponibilite en ligne d'un livre
CREATE TABLE IF NOT EXISTS `livre_num` (
  `Id_Livre` int NOT NULL,
  `datedebutacces` date DEFAULT NULL,
  `datefinacces` date DEFAULT NULL,
  PRIMARY KEY (`Id_Livre`),
  CONSTRAINT `livre_num_ibfk_1` FOREIGN KEY (`Id_Livre`) REFERENCES `livre` (`Id_Livre`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Telechargement d'un livre numerique par un adherent
CREATE TABLE IF NOT EXISTS `telechargement` (
  `Id_Telechargement` int NOT NULL AUTO_INCREMENT,
  `Id_Livre` int NOT NULL,
  `Id_Adherent` int NOT NULL,
  `datetelechargement` datetime DEFAULT NULL,
  PRIMARY KEY (`Id_Telechargement`),
  KEY `Id_Livre` (`Id_Livre`),
  KEY `Id_Adherent` (`Id_Adherent`),
  CONSTRAINT `telechargement_ibfk_1` FOREIGN KEY (`Id_Livre`) REFERENCES `livre` (`Id_Livre`) ON DELETE CASCADE,
  CONSTRAINT `telechargement_ibfk_2` FOREIGN KEY (`Id_Adherent`) REFERENCES `adherent` (`Id_Adherent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Adherent : code unique (rang d'inscription) et date d'adhesion
ALTER TABLE `adherent`
  ADD COLUMN `code_adherent` VARCHAR(20) NULL AFTER `Id_Adherent`,
  ADD COLUMN `dateadhesion` date DEFAULT NULL AFTER `telephoneadh`;