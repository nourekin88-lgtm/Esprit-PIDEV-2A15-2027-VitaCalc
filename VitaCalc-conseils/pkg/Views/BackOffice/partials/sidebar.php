<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="<?= $baseUrl ?>assets/img/logo.png" alt="Logo VitaCalc">
        <p>Espace Administration</p>
    </div>
    <p class="menu-title">Menu principal</p>
    <ul class="sidebar-menu">
        <li><a href="admin.php?page=dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">Tableau de bord</a></li>
        <li><a href="admin.php?page=conseils" class="<?= $currentPage === 'conseils' ? 'active' : '' ?>">Gestion des conseils</a></li>
        <li><a href="admin.php?page=categories" class="<?= $currentPage === 'categories' ? 'active' : '' ?>">Gestion des catégories</a></li>
    </ul>
    <p class="menu-title">Navigation</p>
    <ul class="sidebar-menu">
        <li><a href="index.php">Voir le Front Office</a></li>
    </ul>
</aside>
