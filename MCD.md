# MCD – Gestion d'une bibliothèque

## 1. Présentation générale

Ce MCD (Modèle Conceptuel de Données) représente le fonctionnement d'une bibliothèque.

Il permet notamment de gérer :

- les livres ;
- les exemplaires des livres ;
- les adhérents ;
- les cartes d'adhérent ;
- les emprunts ;
- la classification Dewey des livres ;
- le rattachement entre adhérents.

Le modèle contient **6 entités** et **6 associations**.

---

## 2. Les entités

### 2.1. CODEDEWEY

L'entité `CODEDEWEY` permet de représenter la classification Dewey utilisée pour classer les livres.

| Attribut | Description |
|---|---|
| `codewey` | Identifiant du code Dewey |
| `libelledewey` | Libellé correspondant au code Dewey |

**Clé primaire :** `codewey`

---

### 2.2. LIVRE

L'entité `Livre` représente les ouvrages disponibles dans la bibliothèque.

| Attribut | Description |
|---|---|
| `id` | Identifiant du livre |
| `titre` | Titre du livre |
| `datesortie` | Date de sortie/publication |
| `isbn` | Numéro ISBN du livre |
| `dateachat` | Date d'achat du livre |

**Clé primaire :** `id`

Un livre est associé à un code Dewey grâce à l'association `Asso 1`.

---

### 2.3. EXEMPLAIRE

L'entité `Exemplaire` représente un exemplaire physique d'un livre.

| Attribut | Description |
|---|---|
| `idexemplaire` | Identifiant de l'exemplaire |
| `etat` | État de l'exemplaire |

**Clé primaire :** `idexemplaire`

Un même livre peut être associé à plusieurs exemplaires.

---

### 2.4. EMPRUNT

L'entité `Emprunt` représente un emprunt effectué par un adhérent.

| Attribut | Description |
|---|---|
| `idemprunt` | Identifiant de l'emprunt |
| `dateemprunt` | Date à laquelle l'emprunt est effectué |
| `dateretour` | Date de retour prévue ou effective |

**Clé primaire :** `idemprunt`

Un emprunt est obligatoirement associé à un adhérent.

---

### 2.5. ADHERENT

L'entité `Adherent` contient les informations concernant les personnes inscrites à la bibliothèque.

| Attribut | Description |
|---|---|
| `idadh` | Identifiant de l'adhérent |
| `nomadh` | Nom de l'adhérent |
| `prenomadh` | Prénom de l'adhérent |
| `date naissance` | Date de naissance |
| `adresseadh` | Adresse de l'adhérent |
| `mailadh` | Adresse e-mail |
| `telephoneadh` | Numéro de téléphone |

**Clé primaire :** `idadh`

---

### 2.6. CARTE

L'entité `Carte` représente une carte d'adhérent.

| Attribut | Description |
|---|---|
| `idcarte` | Identifiant de la carte |
| `dateemission` | Date d'émission de la carte |

**Clé primaire :** `idcarte`

---

# 3. Les associations

## 3.1. Asso 1 – Classification Dewey

L'association `Asso 1` relie les entités `CODEDEWEY` et `LIVRE`.

Cardinalités :

- `CODEDEWEY` : **0,n**
- `LIVRE` : **1,1**

Cela signifie que :

- un code Dewey peut être associé à **aucun ou plusieurs livres** ;
- chaque livre est associé à **un et un seul code Dewey**.

### Exemple

Un code Dewey `500` pourrait être associé à plusieurs livres.

En revanche, un livre donné ne possède qu'un seul code Dewey dans ce modèle.

---

## 3.2. Asso 4 – Livre / Exemplaire

L'association `Asso 4` relie `LIVRE` et `EXEMPLAIRE`.

Cardinalités indiquées sur le MCD :

- `LIVRE` : **0,n**
- `EXEMPLAIRE` : **0,n**

Cela signifie qu'un livre peut être associé à zéro ou plusieurs exemplaires et qu'un exemplaire peut être associé à zéro ou plusieurs livres selon les cardinalités représentées sur le schéma.

> Remarque : dans un modèle classique de bibliothèque, on attend généralement une relation **1,n** entre Livre et Exemplaire : un exemplaire appartient à un seul livre tandis qu'un livre possède un ou plusieurs exemplaires. Il faudrait donc vérifier si les cardinalités `0,n` / `0,n` du schéma sont bien celles souhaitées.

---

## 3.3. Emprunter – Emprunt / Exemplaire

L'association `Emprunter` relie `EMPRUNT` et `EXEMPLAIRE`.

Cardinalités :

- `EMPRUNT` : **0,n**
- `EXEMPLAIRE` : **0,n**

Elle permet d'indiquer quels exemplaires sont concernés par les emprunts.

Dans le modèle représenté, les deux côtés peuvent être associés à plusieurs occurrences.

> Dans une gestion classique d'une bibliothèque, il est généralement plus logique qu'un emprunt concerne un ou plusieurs exemplaires et qu'un exemplaire puisse apparaître dans plusieurs emprunts au cours du temps. Les cardinalités exactes dépendent toutefois des règles métier retenues.

---

## 3.4. Asso 3 – Adhérent / Emprunt

L'association `Asso 3` relie `ADHERENT` et `EMPRUNT`.

Cardinalités :

- `EMPRUNT` : **1,1**
- `ADHERENT` : **0,n**

Cela signifie que :

- chaque emprunt appartient obligatoirement à **un seul adhérent** ;
- un adhérent peut avoir **zéro, un ou plusieurs emprunts**.

### Exemple

Un adhérent peut ne jamais avoir effectué d'emprunt.

Un autre adhérent peut avoir plusieurs emprunts enregistrés.

---

## 3.5. Asso 2 – Carte / Adhérent

L'association `Asso 2` relie `CARTE` et `ADHERENT`.

Cardinalités :

- `CARTE` : **1,1**
- `ADHERENT` : **0,n**

Cela signifie que :

- chaque carte est obligatoirement associée à **un seul adhérent** ;
- un adhérent peut être associé à **zéro ou plusieurs cartes**.

Cela permet notamment de conserver plusieurs cartes associées au même adhérent si le système doit gérer plusieurs cartes.

---

## 3.6. Rattacher – Adhérent / Adhérent

L'association `rattacher` est une **association réflexive** sur l'entité `ADHERENT`.

Autrement dit, un adhérent peut être relié à un autre adhérent.

Les cardinalités indiquées sont :

- un côté : **0,n**
- l'autre côté : **0,1**

Cela peut permettre de représenter une relation de rattachement entre adhérents.

### Exemple

On pourrait utiliser cette relation pour représenter :

- un parent et ses enfants ;
- un responsable et les personnes dont il est responsable ;
- un adhérent principal et des adhérents rattachés.

La signification exacte dépend de la règle métier définie par l'application.

---

# 4. Synthèse des cardinalités

| Association | Entité 1 | Cardinalité | Entité 2 | Cardinalité |
|---|---|---:|---|---:|
| Asso 1 | CODEDEWEY | 0,n | LIVRE | 1,1 |
| Asso 4 | LIVRE | 0,n | EXEMPLAIRE | 0,n |
| Emprunter | EMPRUNT | 0,n | EXEMPLAIRE | 0,n |
| Asso 3 | EMPRUNT | 1,1 | ADHERENT | 0,n |
| Asso 2 | CARTE | 1,1 | ADHERENT | 0,n |
| Rattacher | ADHERENT | 0,n | ADHERENT | 0,1 |

---

# 5. Fonctionnement global

Le fonctionnement général du modèle peut être résumé ainsi :

1. Un **livre** est classé avec un **code Dewey**.
2. Un livre peut posséder plusieurs **exemplaires**.
3. Un **adhérent** peut effectuer plusieurs **emprunts**.
4. Chaque emprunt est associé à un adhérent.
5. Les emprunts permettent d'enregistrer les exemplaires concernés.
6. Un adhérent peut être associé à une ou plusieurs **cartes** selon les cardinalités du modèle.
7. Les adhérents peuvent également être reliés entre eux grâce à l'association `rattacher`.

---

# 6. Passage potentiel vers le modèle relationnel

À partir de ce MCD, on pourrait obtenir des tables telles que :

```text
CODEDEWEY(
    codewey PK,
    libelledewey
)

LIVRE(
    id PK,
    titre,
    datesortie,
    isbn,
    dateachat,
    codewey FK
)

EXEMPLAIRE(
    idexemplaire PK,
    etat
)

EMPRUNT(
    idemprunt PK,
    dateemprunt,
    dateretour,
    idadh FK
)

ADHERENT(
    idadh PK,
    nomadh,
    prenomadh,
    date_naissance,
    adresseadh,
    mailadh,
    telephoneadh
)

CARTE(
    idcarte PK,
    dateemission,
    idadh FK
)
