CREATE DATABASE IF NOT EXISTS mediatheque_cholet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mediatheque_cholet;

CREATE TABLE CODEDEWEY(
   codewey VARCHAR(50),
   libelledewey VARCHAR(50),
   PRIMARY KEY(codewey)
);

CREATE TABLE Livre(
   Id_Livre INT AUTO_INCREMENT,
   titre VARCHAR(50),
   datesortie DATE,
   isbn VARCHAR(50),
   dateachat DATE,
   codewey VARCHAR(50) NOT NULL,
   PRIMARY KEY(Id_Livre),
   FOREIGN KEY(codewey) REFERENCES CODEDEWEY(codewey)
);

CREATE TABLE Exemplaire(
   Id_Exemplaire INT AUTO_INCREMENT,
   etat VARCHAR(50),
   PRIMARY KEY(Id_Exemplaire)
);

CREATE TABLE Adherent(
   Id_Adherent INT AUTO_INCREMENT,
   nomadh VARCHAR(50),
   prenomadh VARCHAR(50),
   datenaissance DATE,
   adresseadh VARCHAR(50),
   mailadh VARCHAR(50),
   telephoneadh VARCHAR(50),
   Id_Adherent_1 INT,
   PRIMARY KEY(Id_Adherent),
   FOREIGN KEY(Id_Adherent_1) REFERENCES Adherent(Id_Adherent)
);

CREATE TABLE Carte(
   Id_Carte INT AUTO_INCREMENT,
   dateemission DATE,
   Id_Adherent INT NOT NULL,
   PRIMARY KEY(Id_Carte),
   FOREIGN KEY(Id_Adherent) REFERENCES Adherent(Id_Adherent)
);

CREATE TABLE Emprunt(
   Id_Emprunt INT AUTO_INCREMENT,
   dateemprrunt DATE,
   dateretour DATE,
   Id_Adherent INT NOT NULL,
   PRIMARY KEY(Id_Emprunt),
   FOREIGN KEY(Id_Adherent) REFERENCES Adherent(Id_Adherent)
);

CREATE TABLE Emprunter(
   Id_Exemplaire INT,
   Id_Emprunt INT,
   PRIMARY KEY(Id_Exemplaire, Id_Emprunt),
   FOREIGN KEY(Id_Exemplaire) REFERENCES Exemplaire(Id_Exemplaire),
   FOREIGN KEY(Id_Emprunt) REFERENCES Emprunt(Id_Emprunt)
);

CREATE TABLE Posseder(
   Id_Livre INT,
   Id_Exemplaire INT,
   PRIMARY KEY(Id_Livre, Id_Exemplaire),
   FOREIGN KEY(Id_Livre) REFERENCES Livre(Id_Livre),
   FOREIGN KEY(Id_Exemplaire) REFERENCES Exemplaire(Id_Exemplaire)
);
