-- ============================================================
-- AUTENTHENTIFICATION (comptes de connexion)
-- Creer la table utilisateur et les comptes admin + adherents
-- ============================================================

CREATE TABLE IF NOT EXISTS `utilisateur` (
  `Id_Utilisateur` int NOT NULL AUTO_INCREMENT,
  `identifiant` varchar(100) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `role` enum('admin','adherent') NOT NULL DEFAULT 'adherent',
  `Id_Adherent` int DEFAULT NULL,
  PRIMARY KEY (`Id_Utilisateur`),
  UNIQUE KEY `identifiant` (`identifiant`),
  KEY `Id_Adherent` (`Id_Adherent`),
  CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`Id_Adherent`) REFERENCES `adherent` (`Id_Adherent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nettoye les comptes existants puis reinsere (mot de passe hashé)
DELETE FROM `utilisateur`;

INSERT INTO `utilisateur` (`identifiant`, `motdepasse`, `role`, `Id_Adherent`) VALUES
('admin', '$2y$10$r17BBtIv/JfS4riTIT8h.Os0XeJIfDTVt3KyxoGD5FQuvaqQpBtCi', 'admin', NULL),
('jean.dupont@email.fr', '$2y$10$rdGW62lWBR62E9zubBbQxODrSd29C5wzld3iFB1LV6mc6zAX6E2.W', 'adherent', 1),
('sophie.martin@email.fr', '$2y$10$.Hu7DAusCe/e3cD6L4BsC.lqUz87HWkl7IS03KFlH0fYEh6mauY4y', 'adherent', 2),
('lucas.bernard@email.fr', '$2y$10$GCI4iIq8cVL48VPGizd6seNrEue76Stmy4gdp2iwKNJT1PXEO.9va', 'adherent', 3),
('emma.robert@email.fr', '$2y$10$DXLsw5oMEwwR96yeedBJR.uLGKMoNKIXclPLoEFOwRkdggIADgUz2', 'adherent', 4),
('thomas.petit@email.fr', '$2y$10$fBeiBjTroKd8jcnBZ.iEf.BFG3EgWet5t1XHKbGPmDl9Jd4G/o01C', 'adherent', 5);
