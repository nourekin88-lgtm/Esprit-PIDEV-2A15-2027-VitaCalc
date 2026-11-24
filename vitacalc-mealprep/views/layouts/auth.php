<?php
/**
 * Layout AUTHENTIFICATION (connexion / inscription) : panneau de marque + formulaire.
 * Variables : $title, $currentPage, $content, $extraScripts
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once BASE_PATH . '/views/includes/head.php'; ?>
</head>
<body class="auth-body">
    <?php require_once BASE_PATH . '/views/includes/spinner.php'; ?>

    <div class="auth-wrap">
        <aside class="auth-side">
            <span class="blob blob-a"></span><span class="blob blob-b"></span>
            <div class="auth-side-inner">
                <a href="<?= url('home') ?>" class="logo-card" aria-label="VitaCalc - Accueil">
                    <img src="assets/images/logo/vitacalc-logo.png" alt="VitaCalc" class="site-logo">
                </a>
                <h2 class="auth-title">Mangez sain,<br>sans y penser.</h2>
                <p class="auth-lead">Rejoignez VitaCalc et organisez vos repas de la semaine en quelques clics.</p>
                <ul class="auth-points">
                    <li><i class="bi bi-journal-richtext"></i><span><strong>Recettes complètes</strong> : ingrédients, quantités et étapes pas à pas.</span></li>
                    <li><i class="bi bi-heart"></i><span><strong>Vos favoris</strong> : gardez vos recettes préférées sous la main.</span></li>
                    <li><i class="bi bi-cart-check"></i><span><strong>Liste de courses</strong> : générée automatiquement pour la semaine.</span></li>
                </ul>
            </div>
        </aside>

        <main class="auth-main">
            <div class="auth-card">
                <a href="<?= url('home') ?>" class="auth-back"><i class="bi bi-arrow-left me-1"></i>Retour à l'accueil</a>
                <?php require BASE_PATH . '/views/includes/flash.php'; ?>
                <?= $content ?>
            </div>
        </main>
    </div>

    <?php require_once BASE_PATH . '/views/includes/scripts.php'; ?>
</body>
</html>
