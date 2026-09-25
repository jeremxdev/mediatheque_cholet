# Mediatheque de Cholet

Application web de gestion de mediathequeDeveloppee en PHP/MySQL pour la ville de Cholet. Elle permet de gerer un catalogue de livres, leurs exemplaires physiques, les adherents inscrits, ainsi que les emprunts.

---

## Technologies

| Technologie | Version / Details |
|---|---|
| PHP | 8.1+ |
| MySQL | 8.0+ |
| HTML | 5 |
| CSS | Personnalise, responsive design |
| JS | Vanilla (menu burger mobile) |
| PDO | Connexion securisee a la base MySQL |

---

## Fonctionnalites

L'application est organisee en **5 modules CRUD** complets, avec un **tableau de bord** d'accueil :

| Module | Description | Operations |
|---|---|---|
| **Livres** | Catalogue des ouvrages (titre, ISBN, classification Dewey, dates, type de support) | Ajouter / Consulter / Modifier / Supprimer |
| **Numeriques** | Versions en ligne des livres (fenetre d'acces, telechargements) | Ajouter / Consulter / Modifier / Supprimer |
| **Exemplaires** | Copies physiques d'un livre (code Dewey+Rang, etat normalise, historique des emprunts) | Ajouter / Consulter / Modifier / Supprimer |
| **Adherents** | Inscription et suivi des membres (code, date d'adhesion, referent pour les mineurs) | Ajouter / Consulter / Modifier / Supprimer |
| **Emprunts** | Enregistrement, suivi et retour des prets (statut En cours / Rendu) | Enregistrer / Consulter / Modifier / Supprimer / Retourner |

### Regles de gestion

- **Code exemplaire** : chaque exemplaire porte le code `classement Dewey - rang d'arrivee` (ex. `800-1`).
- **Etat normalise** : un exemplaire est `Neuf`, `Bon état` ou `Usagé` (modifiable au retour).
- **Adherents mineurs** : un adherent de moins de 18 ans doit etre rattache a un **adulte referent** majeur.
- **Validite de l'adhesion** : une carte de pret est valable **1 an** a compter de sa date d'emission ; une adhesion expiree bloque tout nouveau preat et tout telechargement.
- **Disponibilite** : un exemplaire est disponible s'il n'a **aucun emprunt en cours** (retour non enregistre).
- **Retour des livres** : le retour **enregistre la date de restitution** et etat de l'exemplaire (aucune suppression).

Le **tableau de bord** (page d'accueil) affiche un compteur en temps reel pour chaque module.

### Connexion et roles

Le site est protege par une **authentification** par session :

| Role | Acces |
|---|---|
| **Administrateur** | Page de connexion -> tableau de bord -> tous les modules de gestion (ajouter, modifier, supprimer, enregistrer, retourner) |
| **Adherent** | Page de connexion -> espace personnel : sa fiche (code, adhesion), sa carte, emprunter un livre, le rendre (avec etat), consulter l'historique de ses emprunts et telecharger les livres numeriques |

Seul un **administrateur** peut ajouter, modifier ou supprimer des elements du site.

---

## Arborescence du projet

```
mediatheque/
|
|-- index.php                         # Page d'accueil (dashboard, admin uniquement)
|-- login.php                         # Page de connexion (admin / adherent)
|-- logout.php                        # Deconnexion (detruit la session)
|-- compte.php                        # Espace personnel de l'adherent
|
|-- config/
|   |-- database.php                  # Connexion PDO a la base de donnees
|
|-- includes/
|   |-- header.php                    # En-tete commun (logo + nav selon role + burger)
|   |-- footer.php                    # Pied de page commun
|   |-- auth.php                      # Session + fonctions d'acces (require_admin, etc.)
|   |-- functions.php                 # Regles de gestion (majorite, validite, disponibilite, etats)
|   |-- prefix.php                    # Calcul automatique de l'URL de base du projet
|
|-- livres/
|   |-- index.php                     # Liste des livres
|   |-- ajouter.php                   # Formulaire d'ajout
|   |-- consulter.php                 # Fiche detaillee d'un livre
|   |-- modifier.php                  # Formulaire de modification
|   |-- supprimer.php                 # Suppression (action)
|
|-- numeriques/
|   |-- index.php                     # Liste des livres numeriques (acces, telechargements)
|   |-- ajouter.php                   # Definition de la fenetre d'acces en ligne
|   |-- consulter.php                 # Fiche + historique des telechargements
|   |-- modifier.php                  # Modification de la fenetre d'acces
|   |-- supprimer.php                 # Suppression (action)
|
|-- exemplaires/
|   |-- index.php                     # Liste des exemplaires (code, etat, disponibilite)
|   |-- ajouter.php                   # Ajout d'un exemplaire + lien avec un livre
|   |-- consulter.php                 # Fiche + historique des emprunts
|   |-- modifier.php                  # Modification de l'etat / du livre lie
|   |-- supprimer.php                 # Suppression avec gestion des dependances
|
|-- adherents/
|   |-- index.php                     # Liste des adherents (code, majeur/mineur)
|   |-- ajouter.php                   # Inscription (referent obligatoire pour un mineur)
|   |-- consulter.php                 # Fiche detaillee (validite de l'adhesion)
|   |-- modifier.php                  # Mise a jour des informations
|   |-- supprimer.php                 # Suppression avec gestion des dependances
|
|-- emprunts/
|   |-- index.php                     # Liste des emprunts (statut En cours / Rendu)
|   |-- enregistrer.php               # Enregistrement d'un nouvel emprunt (adhesion + dispo)
|   |-- retour.php                    # Enregistrement du retour (date + etat)
|   |-- consulter.php                 # Fiche detaillee d'un emprunt
|   |-- modifier.php                  # Modification des dates / adherent
|   |-- supprimer.php                 # Suppression avec gestion des dependances
|
|-- css/
|   |-- style.css                     # Styles du site (responsive design)
|
|-- js/
|   |-- main.js                       # Script du menu burger (mobile)
|
|-- sql/
|   |-- mediateque.sql                # Dump de la base de donnees (donnees incluses)
|   |-- evolution.sql                 # Evolutions du schema (numerique, codes, adhesion)
|   |-- auth.sql                      # Table utilisateur + comptes de connexion
```

---

## Connexion (comptes de demonstration)

Tout le site est accessible apres connexion a `login.php`.

| Role | Identifiant | Mot de passe |
|---|---|---|
| **Administrateur** | `admin` | `admin123` |
| **Adherent** | `jean.dupont@email.fr` | `adherent123` |
| **Adherent** | `sophie.martin@email.fr` | `adherent123` |
| **Adherent** | `lucas.bernard@email.fr` | `adherent123` |
| **Adherent** | `emma.robert@email.fr` | `adherent123` |
| **Adherent** | `thomas.petit@email.fr` | `adherent123` |

Les mots de passe sont stockes **haches** en base (`password_hash` / `password_verify`).
Pour creer un nouveau compte, ajouter une ligne dans la table `utilisateur` avec un mot de passe hashé par `password_hash()`.

> Note : le compte `emma.robert@email.fr` possede une **carte expiree** (2025) afin de demontrer le blocage des emprunts et telechargements pour une adhesion non valide.

---

## Base de donnees

Nom de la base : **`mediateque`**

### Diagramme des entites

```
CODEDEWEY <---- codewey ---- LIVRE
                                |               |
                       Posseder (jonction)      | (livre_num)
                                |               v
                           EXEMPLAIRE      TELE. <-- ADHERENT
                                |           (telechargement)
                           Emprunter (jonction)
                                |
                            EMPRUNT -----> ADHERENT
                                               |
                                     ADHERENT (referent : auto-reference)
                                               |
                                           CARTE
```

### Description des tables

| Table | Description | Cles principales |
|---|---|---|
| `CODEDEWEY` | Classification Dewey des livres | `codewey` (PK) |
| `Livre` | Ouvrages du catalogue (type de support papier/numerique) | `Id_Livre` (PK), `codewey` (FK) |
| `livre_num` | Version numerique d'un livre (fenetre d'acces en ligne) | `Id_Livre` (PK/FK) |
| `telechargement` | Historique des telechargements des adherents | `Id_Telechargement` (PK), `Id_Livre`, `Id_Adherent` |
| `Exemplaire` | Copies physiques (code `Dewey-Rang`, etat normalise) | `Id_Exemplaire` (PK), `code_exemplaire` (UNIQUE) |
| `Adherent` | Membres inscrits (code, date d'adhesion, referent) | `Id_Adherent` (PK), `code_adherent` (UNIQUE), `Id_Adherent_1` (FK auto-ref) |
| `Carte` | Cartes de bibliotheque (valables 1 an) | `Id_Carte` (PK), `Id_Adherent` (FK) |
| `Emprunt` | Enregistrements de prets (dateemprrunt, dateretour) | `Id_Emprunt` (PK), `Id_Adherent` (FK) |
| `Emprunter` | Jonction exemplaire <-> emprunt | `(Id_Exemplaire, Id_Emprunt)` (PK composite) |
| `Posseder` | Jonction livre <-> exemplaire | `(Id_Livre, Id_Exemplaire)` (PK composite) |
| `utilisateur` | Comptes de connexion (admin/adherent) | `Id_Utilisateur` (PK), `Id_Adherent` (FK) |

---

## Installation

### 1. Importer la base de donnees

```bash
# Via la ligne de commande MySQL
mysql -u root mediateque < sql/mediateque.sql
mysql -u root mediateque < sql/auth.sql      # compte de connexion
```

Ou importer le fichier `sql/mediateque.sql` via **phpMyAdmin**.

### 2. Configurer la connexion

Editer `config/database.php` si necessaire :

```php
$host     = 'localhost';
$dbname   = 'mediateque';
$username = 'root';
$password = '';          // adapter selon votre configuration
```

### 3. Lancer le serveur

```bash
# Avec le serveur integre de PHP
php -S 127.0.0.1:8099 -t C:\laragon\www

# Puis ouvrir dans le navigateur
# http://127.0.0.1:8099/mediatheque/
```

Avec **Laragon**, le site est accessible directement a :
```
http://localhost/mediatheque/
```

---

## Notes techniques

- **Responsive design** : le site s'adapte aux ecrans mobiles avec un menu burger.
- **Authentification** : acces protege par session, roles admin / adherent, mots de passe haches (`password_hash` / `password_verify`), regeneration d'id de session.
- **Securite** : toutes les requetes SQL utilisent des **requetes preparees PDO** (protection contre les injections SQL).
- **XSS** : toutes les sorties utilisateur sont filterees avec `htmlspecialchars()`.
- **Transactions** : les operations impliquant plusieurs tables (ajout/suppression, emprunt, retour) sont gerees dans des **transactions MySQL** pour garantir l'integrite des donnees.
- **Regles de gestion centralisees** dans `includes/functions.php` (majorite, validite d'adhesion, disponibilite, etats autorises).
- **Auto-detection du prefixe** : les liens CSS et de navigation fonctionnent quel que soit le chemin du projet sur le serveur.

---

## Licence

Projet educatif -- Ville de Cholet
