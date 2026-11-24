# VitaCalc — Module Événement

Projet pédagogique structuré en PHP MVC, conformément à l'atelier **Intégration Template**. Le front-office et le back-office utilisent les mêmes données sur le serveur via une API JSON et une base **MySQL**.

**Prérequis : PHP 8.1 ou plus récent + MySQL 8 (XAMPP récent).**

---

## Mise en place de la base de données

1. Démarrez **Apache** et **MySQL** depuis le panneau XAMPP.
2. Ouvrez **phpMyAdmin** (`http://localhost/phpmyadmin`) et importez le fichier `database.sql` (onglet *Importer*), ou exécutez-le directement dans la console MySQL :
   ```bash
   mysql -u root -p < database.sql
   ```
3. La base `vitacalc_events` est créée avec les tables `Evenement` et `Inscription` et 7 événements de démonstration.

### Configurer la connexion

Ouvrez `config.php` et adaptez les paramètres à votre environnement :

```php
return [
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'vitacalc_events',
    'db_user' => 'root',
    'db_pass' => '',          // mot de passe MySQL si différent
    'db_charset' => 'utf8mb4',
];
```

---

## Démarrage avec XAMPP

1. Décompressez l'archive dans `C:/xampp/htdocs/VitaCalm-Events/`.
2. Importez `database.sql` (voir ci-dessus).
3. Configurez `config.php`.
4. Ouvrez `http://localhost/VitaCalm-Events/`.

## Démarrage avec le serveur PHP intégré

```bash
php -S localhost:8000
```
Puis ouvrez `http://localhost:8000/`. MySQL doit être accessible indépendamment.

---

## Architecture MVC

```text
Elearning/
├── index.php                    # Routeur des pages
├── api.php                      # Point d'entrée API JSON
├── config.php                   # Paramètres de connexion MySQL
├── bootstrap.php                # Autoload + helper e()
├── routes.php                   # Association page → contrôleur
├── database.sql
├── Controllers/                 # Controller, Home, Events, Signup, Admin, Api, Image, Upload
├── Models/                      # Database, Event, JsonStore, Registration, Favorite, ListeAttente
├── Views/
│   ├── assets/                  # css, img, js, lib (Leaflet)
│   ├── includes/                # head, spinner, navbar, sidebar, header, footer, admin-footer, scripts
│   ├── layout.php · admin-layout.php
│   └── index, events, event-detail, signup, admin, 404 (.php)
└── storage/
```

---

## API REST

Toutes les routes passent par `api.php`.

| Méthode  | URL                                | Action                              |
|----------|------------------------------------|-------------------------------------|
| GET      | `api.php?resource=events`          | Liste tous les événements           |
| GET      | `api.php?resource=events&id={id}`  | Détail d'un événement               |
| POST     | `api.php?resource=events`          | Créer un événement                  |
| PUT      | `api.php?resource=events&id={id}`  | Modifier un événement               |
| DELETE   | `api.php?resource=events&id={id}`  | Supprimer (cascade inscriptions)    |
| GET      | `api.php?resource=registrations`   | Liste toutes les inscriptions       |
| GET      | `api.php?resource=registrations&id={id}` | Détail d'une inscription      |
| POST     | `api.php?resource=registrations`   | S'inscrire à un événement           |
| PUT      | `api.php?resource=registrations&id={id}` | Modifier une inscription      |
| DELETE   | `api.php?resource=registrations&id={id}` | Annuler une inscription       |

La suppression d'un événement supprime automatiquement ses inscriptions via la contrainte `ON DELETE CASCADE` MySQL. L'inscription décrémente `placesReservees` de façon atomique à la suppression.

| GET / POST / DELETE | `api.php?resource=favorites[&id={idEvenement}]` | Favoris de l'utilisateur courant (cookie `vc_user`) |
| GET                 | `api.php?resource=waitlist&view=counts`   | Nombre de personnes en attente par événement |
| GET                 | `api.php?resource=waitlist[&event={id}]`  | Liste d'attente complète (back-office) |
| POST                | `api.php?resource=waitlist`               | Rejoindre la liste d'attente (événement complet) |
| POST                | `api.php?resource=waitlist` `{action:"promote", id}` | Inscrire une personne en attente |
| DELETE              | `api.php?resource=waitlist&id={id}`       | Retirer une demande de la liste |

---

## Nouveaux métiers

**Ajouter aux favoris (simple)** — classes `Utilisateur` / `Favori`. Chaque visiteur est un utilisateur anonyme identifié par un cookie ; le cœur ♡ des cartes et de la page détail enregistre l'événement, et le filtre « ♥ Mes favoris » les retrouve.

**Gérer une liste d'attente (avancé)** — classe `ListeAttente`. Quand un événement est complet, le formulaire d'inscription bascule en « liste d'attente » (rang calculé par ordre d'arrivée). Dès qu'une place se libère (annulation d'une inscription, capacité augmentée, bouton « Inscrire » du back-office), la première personne est inscrite automatiquement et passe au statut *promue*.

---

## Modèle de données

```
Evenement
  id UNSIGNED INT PK AUTO_INCREMENT
  titre, description, lieu, categorie, format, image
  dateEvenement DATE · heure TIME
  capacite UNSIGNED INT · prix DECIMAL(10,2)
  placesReservees UNSIGNED INT DEFAULT 0

Inscription
  id UNSIGNED INT PK AUTO_INCREMENT
  dateInscription DATE
  idEvenement → Evenement.id (CASCADE DELETE)
  prenom, nom, email, telephone

Utilisateur   id PK · token UNIQUE · dateCreation
Favori        id PK · idUtilisateur → Utilisateur · idEvenement → Evenement · UNIQUE(idUtilisateur, idEvenement)
ListeAttente  id PK · idEvenement → Evenement · prenom, nom, email, telephone
              dateDemande · statut (en_attente | promue) · datePromotion
```

---

## Notes

- La carte Leaflet déduit la zone géographique (Tunis, Ariana, La Soukra, La Marsa, Ben Arous) depuis le champ `lieu` ; les coordonnées ne sont pas stockées en base, elles sont calculées à la volée par le modèle.
- Ce prototype n'inclut pas d'authentification sur le back-office ; à ajouter avant tout déploiement public.
