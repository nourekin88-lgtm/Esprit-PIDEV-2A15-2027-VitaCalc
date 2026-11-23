<?php /** Tableau de bord. Variables : $stats, $derniers, $categories. */ ?>
<div class="page-heading">
    <h1>Bienvenue sur VitaCalc !</h1>
    <p>Gérez vos conseils et vos catégories depuis votre espace administration.</p>
</div>

<section class="stats-grid">
    <div class="stat-card"><div class="stat-icon">📚</div><p>Total des conseils</p><h2><?= (int) $stats['conseils'] ?></h2></div>
    <div class="stat-card"><div class="stat-icon">📂</div><p>Catégories</p><h2><?= (int) $stats['categories'] ?></h2></div>
    <div class="stat-card"><div class="stat-icon">🆕</div><p>Publiés ce mois-ci</p><h2><?= (int) $stats['ce_mois'] ?></h2></div>
    <div class="stat-card"><div class="stat-icon">📭</div><p>Catégories sans conseil</p><h2><?= (int) $stats['vides'] ?></h2></div>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Derniers conseils</h3>
        <a href="admin.php?page=conseils">Voir tous les conseils</a>
    </div>
    <?php if (!$derniers): ?>
        <p class="empty">Aucun conseil pour le moment.</p>
    <?php else: ?>
    <table class="table-dashboard">
        <thead><tr><th>Titre</th><th>Catégorie</th><th>Date de publication</th></tr></thead>
        <tbody>
        <?php foreach ($derniers as $c): ?>
            <tr>
                <td><?= h($c['titre']) ?></td>
                <td><span class="category-badge"><?= h($c['categorie']) ?></span></td>
                <td><?= date('d/m/Y', strtotime($c['date_publication'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Catégories de conseils</h3>
        <a href="admin.php?page=categories">Gérer les catégories</a>
    </div>
    <table class="table-dashboard">
        <thead><tr><th>Catégorie</th><th>Description</th><th>Conseils</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $k): ?>
            <tr>
                <td><span class="category-badge"><?= h($k['nom']) ?></span></td>
                <td><?= h($k['description']) ?></td>
                <td><?= (int) $k['nb'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
