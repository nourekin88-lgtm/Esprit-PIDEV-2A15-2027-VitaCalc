# VitaCalc · Module 4 · Gestion nutritionnelle

Application locale en PHP natif, HTML, CSS et JavaScript. Le catalogue public présente les régimes alimentaires et les objectifs nutritionnels. Le back-office permet de créer, consulter, modifier et supprimer les fiches des deux collections.

## Installation avec XAMPP

1. Décompressez `VitaCalc-XAMPP.zip`. Copiez le dossier `VitaCalc` dans `C:\xampp\htdocs` en conservant les fichiers `.htaccess`.
2. Dans le panneau XAMPP, démarrez **Apache** et **MySQL**. Le projet utilise PHP 8.1 ou supérieur avec `pdo_mysql`, `mbstring` et les sessions PHP.
3. Ouvrez [phpMyAdmin](http://localhost/phpmyadmin/) et importez `VitaCalc.sql` depuis l’onglet **Importer** de la page d’accueil. Si vous souhaitez six fiches de démonstration supplémentaires, importez aussi `config/exemples.sql`. Toutes les tables sont accessibles sous la base `vitacalc_nutrition`.
4. Dans PowerShell, créez le compte administrateur local. Choisissez votre identifiant et saisissez deux fois le mot de passe. La saisie est visible dans le terminal. Le projet enregistre son empreinte dans `config/admin.local.php`.

   ```powershell
   Set-Location 'C:\xampp\htdocs\VitaCalc'
   & 'C:\xampp\php\php.exe' '.\tools\creer-admin.php'
   ```

5. Ouvrez [VitaCalc](http://localhost/VitaCalc/) ou [la connexion administrateur](http://localhost/VitaCalc/index.php?page=connexion). Si Apache utilise un autre port, adaptez les adresses, par exemple `http://localhost:8080/VitaCalc/`.

Par défaut, PDO utilise `127.0.0.1:3306`, la base `vitacalc_nutrition` et le compte XAMPP `root` sans mot de passe. Si votre installation diffère, créez `config/db.local.php` avec les valeurs à remplacer. Par exemple, pour changer le port MariaDB :

```php
<?php
return ['port' => '3307'];
```

Les clés disponibles sont `host`, `port`, `name`, `user` et `password`. Si vous changez le nom de la base, adaptez aussi les fichiers SQL avant l’import.

## Parcours de démonstration

- **Inscription** — Cliquez sur **S’inscrire**. Le formulaire guide la saisie du genre, de l’âge, du poids et de la taille. Le profil est enregistré dans MySQL et se modifie depuis **Mon profil** pendant la session du navigateur.
- **Repères et régime** — Après l’inscription, ouvrez **Mon profil** pour consulter l’IMC, les fourchettes de glucides et d’eau totale, et choisir un objectif et un niveau d’activité. Le choix est enregistré dans MySQL. La suggestion présente une journée de repas et ouvre la fiche liée du catalogue quand elle est disponible.
- **Notifications** — Choisissez les rappels de repas et d’hydratation et enregistrez leurs heures locales. Les réglages restent dans ce navigateur. Laissez un onglet public de VitaCalc ouvert. Le bouton d’autorisation permet d’afficher aussi les rappels dans les notifications du système, selon le navigateur et ses réglages.
- **Visiteur** — Ouvrir les régimes alimentaires ou les objectifs nutritionnels, rechercher une fiche et lire sa description.
- **Cartes d’accueil et du catalogue** — Survolez une carte pour voir son aperçu. Au toucher ou au clavier, utilisez le bouton de rotation. Le bouton de retour ou la touche Échap réaffiche le recto.
- **Vidéo d’accueil** — Le montage d’entraînement défile avec la page, avec un léger effet de profondeur. Le bouton dans le bandeau permet de mettre la vidéo en pause et de la relancer. Elle s’arrête aussi quand le bandeau sort de l’écran.
- **Administrateur** — Se connecter, choisir une collection dans la barre latérale, ajouter une fiche, la consulter, l’éditer, puis la supprimer avec confirmation.
- **Vérification** — Ouvrir la collection publique après chaque changement pour vérifier la publication, la modification ou la disparition de la fiche.

## Structure et intégration

```text
VitaCalc/
├── index.php
├── VitaCalc.sql
├── config/     Conf.php, bootstrap, réglages locaux
├── controller/ contrôleurs et routes
├── model/      connexion PDO, modèles et données nutritionnelles
├── view/       view.php, header.php, footer.php et vues par rubrique
├── assets/     CSS/, JS/ et imges/
└── tools/      création du compte administrateur
```

La structure suit le projet d’exemple AdELo. Le dossier `view/` regroupe notamment les vues de l’accueil, de la nutrition, du profil et des notifications. Les quatre nouvelles rubriques du menu public ouvrent des pages dont la zone principale est blanche et vide.

Sur une installation existante, reportez les éventuels fichiers `app/admin.local.php` et `app/db.local.php` dans `config/`. L’arborescence ne nécessite pas de réimporter la base existante. Le script `VitaCalc.sql` peut servir à compléter une installation à laquelle il manque des tables.

## Méthode des repères nutritionnels

- L’IMC correspond au poids en kilogrammes divisé par la taille en mètres au carré. La page indique que les valeurs des mineurs s’interprètent avec les courbes de croissance. [OMS](https://apps.who.int/nutrition/landscape/help.aspx?helpid=420) · [CDC](https://www.cdc.gov/BMI/about/index.html)
- Pour les estimations de maintien chez les adultes entre 19 et 78 ans, les deux coefficients de Mifflin–St Jeor encadrent la dépense au repos. Le modèle multiplie ces deux valeurs par le niveau d’activité choisi. Le genre renseigné n’entre pas dans le calcul. [Mifflin–St Jeor](https://pubmed.ncbi.nlm.nih.gov/2305711/) · [Facteurs d’activité de l’EFSA](https://www.efsa.europa.eu/sites/default/files/2017_09_DRVs_summary_report.pdf)
- Les glucides sont estimés à 45–60 % de l’énergie de maintien, avec 4 kcal par gramme. Le repère d’eau totale utilise environ 1 mL/kcal et inclut les boissons et les aliments. [EFSA · Glucides](https://www.efsa.europa.eu/sites/default/files/2017_09_DRVs_summary_report.pdf) · [EFSA · Eau](https://backend.orbit.dtu.dk/ws/files/6350877/5.Scientific.pdf)
- Le bilan quotidien affiche aussi les lipides estimés (20–35 % de l’énergie, à 9 kcal/g), les protéines selon le poids (0,83 g/kg) et le repère adulte de 25 g de fibres par jour. Les fourchettes se lisent séparément. Les repas illustrent une journée avec des portions à adapter. [EFSA · Apports de référence](https://www.efsa.europa.eu/sites/default/files/2017_09_DRVs_summary_report.pdf)
- Les propositions de repas sont des exemples pour adultes. Elles s’appuient sur des repères de repas variés, d’enrichissement progressif et d’alimentation autour du renforcement musculaire. [Santé publique France](https://www.mangerbouger.fr/content/show/1597/file/L%E2%80%99essentiel%20des%20recommandations%20alimentaires.pdf) · [NHS](https://www.nhs.uk/live-well/healthy-weight/managing-your-weight/healthy-ways-to-gain-weight/) · [Nutrition et performance sportive](https://pubmed.ncbi.nlm.nih.gov/26920240/)

Le PDF d’atelier a servi de référence pour le découpage MVC, l’assemblage des vues et la navigation dynamique. La maquette verte et blanche est personnalisée pour VitaCalc. Le formulaire de connexion et les opérations d’écriture contrôlent le jeton CSRF. Les modifications et suppressions exigent une session administrateur. Les descriptions sont stockées et affichées sous forme de texte.

## Visuels et crédits

VitaCalc utilise le logo original fourni et des pictogrammes pour les rubriques, les objectifs et l’administration. Les photographies réelles illustrent les repas et les aliments. [Licence Pexels](https://www.pexels.com/license/)

- Matin. Bol d’avoine aux framboises par Mateusz Feliksik. [Pexels n° 8023873](https://www.pexels.com/photo/close-up-shot-of-an-oatmeal-in-a-bowl-8023873/)
- Midi. Poulet, riz et légumes par Nadin Sh. [Pexels n° 24738519](https://www.pexels.com/photo/chicken-with-rice-with-sesame-seeds-and-vegetables-24738519/)
- Collation. Pomme verte par Valentina Rosati. [Pexels n° 6227174](https://www.pexels.com/photo/green-apple-on-white-background-6227174/)
- Soir. Penne à la tomate par Polina Tankilevitch. [Pexels n° 5419338](https://www.pexels.com/photo/italian-pasta-in-white-bowl-5419338/)

- Assiette de légumes. [Pexels n° 5966437](https://www.pexels.com/photo/5966437/)
- Courses au marché. [Pexels n° 1884572](https://www.pexels.com/photo/1884572/)
- Bol au petit-déjeuner. [Pexels n° 4220141](https://www.pexels.com/photo/4220141/)
- Marché de légumes. [Pexels n° 16153899](https://www.pexels.com/photo/16153899/)
- Activité en plein air. [Pexels n° 10615642](https://www.pexels.com/photo/10615642/)
- Renforcement. [Pexels n° 14190588](https://www.pexels.com/photo/14190588/)
- Bol de salade. [Pexels n° 29266893](https://www.pexels.com/photo/29266893/)
- Bol d’avoine et banane. [Pexels n° 9304017](https://www.pexels.com/photo/9304017/)
- Riz. [Pexels n° 8923092](https://www.pexels.com/photo/8923092/)
- Oeuf. [Pexels n° 7947302](https://www.pexels.com/photo/7947302/)
- Pois. [Pexels n° 7421385](https://www.pexels.com/photo/7421385/)
- Haricot. [Pexels n° 34944742](https://www.pexels.com/photo/34944742/)
- Fruit. [Pexels n° 36076414](https://www.pexels.com/photo/36076414/)
- Volaille. [Pexels n° 12916874](https://www.pexels.com/photo/12916874/)
- Yaourt. [Pexels n° 8892364](https://www.pexels.com/photo/8892364/)
- Poisson. [Pexels n° 1321124](https://www.pexels.com/photo/1321124/)
- Legume. [Pexels n° 4504593](https://www.pexels.com/photo/4504593/)
- Huile. [Pexels n° 6094524](https://www.pexels.com/photo/6094524/)
- Pain. [Pexels n° 14594116](https://www.pexels.com/photo/14594116/)
- Avoine. [Pexels n° 19745775](https://www.pexels.com/photo/19745775/)
- Noix. [Pexels n° 4753637](https://www.pexels.com/photo/4753637/)
- Pates. [Pexels n° 22468831](https://www.pexels.com/photo/22468831/)
- Lait. [Pexels n° 5545921](https://www.pexels.com/photo/5545921/)
- Couscous. [Pexels n° 21531369](https://www.pexels.com/photo/21531369/)

La vidéo d’accueil est un montage muet à partir de deux plans de Mikhail Nilov, sous licence Pexels. [Haltères](https://www.pexels.com/video/a-woman-training-with-dumbbells-in-a-park-8456977/) · [Exercice en plein air](https://www.pexels.com/video/woman-exercising-outdoors-8457011/)

## Menu personnalisé

- Le menu dépend du profil, de l’objectif et du niveau d’activité. Les identifiants des aliments et leurs valeurs par 100 g sont conservés dans `model/data/foods.json`. [USDA · FoodData Central](https://fdc.nal.usda.gov/download-datasets/)
- Pour une portion de remplacement, le moteur rapproche les calories de la portion d’origine et contrôle les écarts de protéines, de glucides, de lipides et de fibres. Chaque produit et chaque préparation peuvent avoir leur propre composition et leurs allergènes.
- Le menu présente une journée type. Pour les profils auxquels les estimations adultes précédentes s’appliquent, l’énergie de maintien sert de base aux portions. La prise de poids utilise un modèle un peu plus énergétique. La perte de poids conserve des repas équilibrés et utilise un écart modéré uniquement lorsque le profil entre dans la plage prévue. [NHS · Prise de poids progressive](https://www.nhs.uk/live-well/healthy-weight/managing-your-weight/healthy-ways-to-gain-weight/) · [NHS · Équilibre alimentaire](https://www.nhs.uk/live-well/healthy-weight/managing-your-weight/understanding-calories/)
