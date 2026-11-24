<?php
// $currentPage est défini par le Controller (voir Controller::render) : il sert à marquer le lien actif.
$currentPage = $currentPage ?? 'home';
$navUser     = currentUser();
$navFavoris  = $navUser ? \App\Models\Favori::countForUser((int) $navUser['id']) : 0;
?>
<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg vita-nav sticky-top">
    <div class="container">
        <a href="<?= url('home') ?>" class="navbar-brand" aria-label="VitaCalc - Accueil">
            <img src="assets/images/logo/vitacalc-logo.png" alt="VitaCalc - Your Health, Your Balance." class="site-logo">
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-lg-auto gap-lg-1 py-3 py-lg-0">
                <li class="nav-item"><a href="<?= url('home') ?>" class="nav-link <?= isActive('home', $currentPage) ?>">Accueil</a></li>
                <li class="nav-item"><a href="<?= url('recettes') ?>" class="nav-link <?= isActive('recettes', $currentPage) ?>">Recettes</a></li>
                <?php if ($navUser): ?>
                    <li class="nav-item"><a href="<?= url('ingredients') ?>" class="nav-link <?= isActive('ingredients', $currentPage) ?>">Ingrédients</a></li>
                    <li class="nav-item"><a href="<?= url('frigo') ?>" class="nav-link <?= isActive('frigo', $currentPage) ?>"><i class="bi bi-snow2 me-1"></i>Mon frigo</a></li>
                    <li class="nav-item">
                        <a href="<?= url('favoris') ?>" class="nav-link <?= isActive('favoris', $currentPage) ?>">
                            Favoris <?php if ($navFavoris): ?><span class="nav-count"><?= $navFavoris ?></span><?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item"><a href="<?= url('courses') ?>" class="nav-link <?= isActive('courses', $currentPage) ?>">Liste de courses</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 pb-3 pb-lg-0">
                <?php if (!$navUser): ?>
                    <a href="<?= url('login') ?>" class="btn btn-outline-primary">Se connecter</a>
                    <a href="<?= url('register') ?>" class="btn btn-primary">S'inscrire</a>
                <?php else: ?>
                    <?php if (isAdmin()): ?>
                        <a href="<?= url('admin') ?>" class="btn btn-dark"><i class="bi bi-speedometer2 me-1"></i>Back Office</a>
                    <?php endif; ?>
                    <div class="dropdown">
                        <button class="user-chip dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar"><?= e(initials($navUser)) ?></span>
                            <span><?= e($navUser['prenom']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><span class="dropdown-header"><?= e($navUser['email']) ?></span></li>
                            <li><a class="dropdown-item" href="<?= url('profil') ?>"><i class="bi bi-person me-2"></i>Mon profil</a></li>
                            <li><a class="dropdown-item" href="<?= url('favoris') ?>"><i class="bi bi-heart me-2"></i>Mes favoris</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= url('logout') ?>">
                                    <?= csrfField() ?>
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Se déconnecter</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<!-- Navbar End -->
