<?php
// $currentPage est fourni par le Controller (voir controllers/Controller.php)
$currentPage = $currentPage ?? 'admin';
$mealPrepOpen = in_array($currentPage, ['admin-recettes', 'admin-ingredients'], true);
?>
<!-- ======== Sidebar Start ======== -->
<aside class="sidebar-nav-wrapper">
    <div class="navbar-logo">
        <a href="<?= url('admin') ?>" class="logo-card" aria-label="VitaCalc - Dashboard">
            <img src="assets/images/logo/vitacalc-logo.png" alt="VitaCalc" class="site-logo">
        </a>
        <div class="logo-sub">Espace administrateur</div>
    </div>

    <nav class="sidebar-nav" aria-label="Navigation du Back Office">
        <ul class="list-unstyled mb-0">
            <li>
                <a href="<?= url('admin') ?>" class="<?= isActive('admin', $currentPage) ?>">
                    <i class="bi bi-speedometer2"></i><span class="text">Dashboard</span>
                </a>
            </li>

            <li class="nav-label">Module Meal Prep</li>
            <li>
                <a href="#menu-mealprep" class="has-children <?= $mealPrepOpen ? '' : 'collapsed' ?>" data-bs-toggle="collapse"
                   aria-expanded="<?= $mealPrepOpen ? 'true' : 'false' ?>" aria-controls="menu-mealprep">
                    <i class="bi bi-basket2"></i><span class="text">Meal Prep</span><i class="bi bi-chevron-down chev"></i>
                </a>
                <ul id="menu-mealprep" class="collapse list-unstyled dropdown-nav <?= $mealPrepOpen ? 'show' : '' ?>">
                    <li><a href="<?= url('admin-recettes') ?>" class="<?= isActive('admin-recettes', $currentPage) ?>">Recettes</a></li>
                    <li><a href="<?= url('admin-ingredients') ?>" class="<?= isActive('admin-ingredients', $currentPage) ?>">Ingrédients</a></li>
                </ul>
            </li>

            <li class="nav-label">Raccourcis</li>
            <li><a href="<?= url('admin-recette-create') ?>"><i class="bi bi-journal-plus"></i><span class="text">Nouvelle recette</span></a></li>
            <li><a href="<?= url('home') ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i><span class="text">Voir le site</span></a></li>
        </ul>
    </nav>
</aside>
<!-- ======== Sidebar End ======== -->
