# VitaCalc – Module Meal Prep (Recette + Ingrédient)
ESPRIT – Projet Technologies Web 2A – 2026/2027

## Lancer le projet
**XAMPP** : démarrer Apache seulement → copier le dossier dans `C:\xampp\htdocs\` → http://localhost/vitacalc-mealprep/index.php?page=home
**Sans XAMPP** : `php -S localhost:8000` dans le dossier → http://localhost:8000/index.php?page=home
Aucune base de données : les données sont dans `data/vitacalc.json` (créé automatiquement avec 12 recettes healthy).

## Comptes de démonstration
- Admin : admin@vitacalc.tn / Admin@123  → Back Office
- Client : client@vitacalc.tn / Client@123 → espace utilisateur

## Structure (MVC)
- `assets/` css (theme = charte, front, back), js, fonts locales, images/logo
- `config/` config.php, helpers.php, routes.php
- `models/` (module) Model (stockage JSON), Utilisateur, Recette, Ingredient, RecetteIngredient, Favori, **Avis**
- `controllers/` Controller (render + layouts), Auth, Front, Favori, Courses, Profil, Dashboard, Recette, Ingredient, **Avis**
- `views/layouts/` front, back, auth, **cook** (mode cuisine plein écran) · `views/includes/` head, spinner, navbar, footer, scripts, sidebar, header · `views/pages/`
- `index.php` contrôleur frontal (`?page=`)

## Parcours
Visiteur : accueil + recettes avec « Voir plus 🔒 » → page inscription → retour à la recette. Client : recettes (par moment du repas), ingrédients, **Mon frigo**, favoris, liste de courses, **notes et avis**, **mode cuisine**, profil. Admin : Dashboard (dont recettes les mieux notées), CRUD Recettes (composition des ingrédients + moment du repas), CRUD Ingrédients.

## Charte graphique
#24A89A, #E8F5F2, #FFFFFF, #263746 · Poppins (titres) / Inter (textes). Boutons pleins en #1A8176 (contraste du texte blanc).

## Fonctionnalités créatives
Favoris, liste de courses intelligente (additionne les ingrédients, portions, copier/imprimer), recalcul des portions, ingrédients à cocher, filtres de durée, upload photo, indicateur de mot de passe, dashboard top favoris.

## Nouvelles fonctionnalités (métiers ajoutés)
1. **Mode cuisine** (`?page=cuisine&id=…`, bouton sur la page d'une recette) : plein écran, une grande étape à la fois (boutons, flèches du clavier ou balayage tactile), liste d'ingrédients à cocher, **minuteur intégré** à chaque étape. La durée est détectée automatiquement dans le texte de l'étape (« cuire 30 minutes », « 1 h 30 »…) ; sinon on peut ajouter un minuteur manuellement. Plusieurs minuteurs peuvent tourner en même temps (pastilles en haut), alarme sonore + vibration à la fin, écran maintenu allumé. Fichiers : `views/layouts/cook.php`, `views/pages/front/cuisine.php`, `assets/js/cook.js`, `assets/css/cook.css`.
2. **Moments de la journée** : chaque recette a un moment (`matin`, `dejeuner`, `diner`) choisi dans le Back Office. La page Recettes propose des onglets (Petit-déjeuner / Déjeuner / Dîner, avec le nombre de recettes) et, sans filtre, regroupe les recettes en trois sections. L'accueil affiche trois tuiles. Les recettes déjà présentes dans `data/vitacalc.json` reçoivent un moment automatiquement au premier lancement (modifiable ensuite dans « Modifier la recette »).
3. **Notes et avis** : le client donne 1 à 5 étoiles (+ commentaire facultatif) sur la page de la recette ; un seul avis par client et par recette (modifiable / supprimable). La **moyenne** et le nombre d'avis s'affichent sur la carte de chaque recette. Table `avis` dans le JSON, modèle `Avis`, contrôleur `AvisController`.
4. **« Qu'est-ce que j'ai dans mon frigo ? »** (`?page=frigo`) : le client coche ses ingrédients ; les recettes sont classées en direct avec le **% d'ingrédients disponibles**, la liste de ce qui manque, un lien vers la liste de courses et, à 100 %, vers le mode cuisine. La sélection est mémorisée dans le navigateur. Fichiers : `views/pages/front/frigo.php`, `assets/js/frigo.js`.

## Tableau GitHub (à recopier)
Intégration templates (layouts/includes) · Routing · Visiteur · Auth · CRUD Recette · CRUD Ingrédient · Dashboard · Favoris/Courses · Mode cuisine · Moments du repas · Notes et avis · Frigo · Tests · README


## Mise à jour : frigo, courses, mode cuisine
- **Courses (bug corrigé)** : la liste restait vide car la variable `$data` était ignorée par `Controller::capture()` (collision de noms). Renommée `$coursesData` et `capture()` protégé. La liste affiche maintenant les photos des ingrédients et propose « Retirer ce que j'ai déjà dans mon frigo ».
- **Frigo** : un frigo 3D dont la porte s'ouvre (`assets/css/frigo.css`), ingrédients rangés par étagère (laitiers, viandes, fruits, balconnet, bac à légumes, placard), résultats groupés « Prêtes / Presque prêtes / Autres idées » avec photos des ingrédients possédés ou manquants et bouton « Acheter ce qui manque ».
- **Mode cuisine** : fond avec la photo de la recette, salutation avec le prénom, portions réglables (quantités recalculées), photos réelles des ingrédients de chaque étape, geste de l'étape, aperçu de l'étape suivante, écran de fin avec la photo du plat.
- **Mode cuisine : commande par la main** : la caméra s'ouvre à l'arrivée (bouton caméra pour couper). Balayez la main de droite à gauche = étape suivante, de gauche à droite = précédente. Détection de mouvement dans le navigateur (aucune bibliothèque, vidéo non envoyée ni enregistrée). Fonctionne sur `localhost` ou en HTTPS.
