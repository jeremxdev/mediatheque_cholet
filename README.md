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

L'application est organisee en **4 modules CRUD** complets, avec un **tableau de bord** d'accueil :

| Module | Description | Operations |
|---|---|---|
| **Livres** | Catalogue des ouvrages (titre, ISBN, classification Dewey, dates) | Ajouter / Consulter / Modifier / Supprimer |
| **Exemplaires** | Copies physiques d'un livre en magasin (etat, historique des emprunts) | Ajouter / Consulter / Modifier / Supprimer |
| **Adherents** | Inscription et suivi des membres (coordonnees, parrainage) | Ajouter / Consulter / Modifier / Supprimer |
| **Emprunts** | Enregistrement et suivi des prets (dates, adherent, exemplaire) | Enregistrer / Consulter / Modifier / Supprimer |

Le **tableau de bord** (page d'accueil) affiche un compteur en temps reel pour chaque module.

---

## Arborescence du projet

```
mediatheque/
|
|-- index.php                         # Page d'accueil (dashboard avec compteurs)
|
|-- config/
|   |-- database.php                  # Connexion PDO a la base de donnees
|
|-- includes/
|   |-- header.php                    # En-tete commun (logo + navigation burger)
|   |-- footer.php                    # Pied de page commun
|
|-- livres/
|   |-- index.php                     # Liste des livres
|   |-- ajouter.php                   # Formulaire d'ajout
|   |-- consulter.php                 # Fiche detaillee d'un livre
|   |-- modifier.php                  # Formulaire de modification
|   |-- supprimer.php                 # Suppression (action)
|
|-- exemplaires/
|   |-- index.php                     # Liste des exemplaires
|   |-- ajouter.php                   # Ajout d'un exemplaire + lien avec un livre
|   |-- consulter.php                 # Fiche + historique des emprunts
|   |-- modifier.php                  # Modification de l'etat / du livre lie
|   |-- supprimer.php                 # Suppression avec gestion des dependances
|
|-- adherents/
|   |-- index.php                     # Liste des adherents
|   |-- ajouter.php                   # Inscription (avec parrainage facultatif)
|   |-- consulter.php                 # Fiche detaillee d'un adherent
|   |-- modifier.php                  # Mise a jour des informations
|   |-- supprimer.php                 # Suppression (action)
|
|-- emprunts/
|   |-- index.php                     # Liste des emprunts
|   |-- enregistrer.php               # Enregistrement d'un nouvel emprunt
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
```

---

## Base de donnees

Nom de la base : **`mediateque`**

### Diagramme des entites

```
CODEDEWEY <---- codewey ---- LIVRE
                               |
                           Posseder (table de jonction)
                               |
                           EXEMPLAIRE
                               |
                          Emprunter (table de jonction)
                               |
                           EMPRUNT -----> ADHERENT
                                              |
                                     ADHERENT (parrain : auto-reference)
                                              |
                                          CARTE
```

### Description des tables

| Table | Description | Cles principales |
|---|---|---|
| `CODEDEWEY` | Classification Dewey des livres | `codewey` (PK) |
| `Livre` | Ouvrages du catalogue | `Id_Livre` (PK), `codewey` (FK) |
| `Exemplaire` | Copies physiques | `Id_Exemplaire` (PK) |
| `Adherent` | Membres inscrits | `Id_Adherent` (PK), `Id_Adherent_1` (FK auto-ref : parrain) |
| `Carte` | Cartes de bibliotheque | `Id_Carte` (PK), `Id_Adherent` (FK) |
| `Emprunt` | Enregistrements de prets | `Id_Emprunt` (PK), `Id_Adherent` (FK) |
| `Emprunter` | Jonction exemplaire <-> emprunt | `(Id_Exemplaire, Id_Emprunt)` (PK composite) |
| `Posseder` | Jonction livre <-> exemplaire | `(Id_Livre, Id_Exemplaire)` (PK composite) |

---

## Installation

### 1. Importer la base de donnees

```bash
# Via la ligne de commande MySQL
mysql -u root mediateque < sql/mediateque.sql
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
- **Securite** : toutes les requetes SQL utilisent des **requetees preparees PDO** (protection contre les injections SQL).
- **XSS** : toutes les sorties utilisateur sont filterees avec `htmlspecialchars()`.
- **Transactions** : les operations impliquant plusieurs tables (ajout/suppression d'exemplaire ou d'emprunt) sont gerees dans des **transactions MySQL** pour garantir l'integrite des donnees.
- **Auto-detection du prefixe** : les liens CSS et de navigation fonctionnent quel que soit le chemin du projet sur le serveur.

---

## Licence

Projet educatif -- Ville de Cholet
