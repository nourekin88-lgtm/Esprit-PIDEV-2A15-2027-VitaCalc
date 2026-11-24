<?php $adminUser = currentUser(); ?>
<!-- ======== Header Start ======== -->
<header class="header">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <button id="menu-toggle" type="button" class="btn btn-outline-secondary btn-sm" aria-label="Afficher / masquer le menu">
                <i class="bi bi-list fs-5"></i>
            </button>
            <span class="d-none d-md-inline text-muted small">Back Office · Module Meal Prep</span>
        </div>

        <div class="dropdown">
            <button class="user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar"><?= e(initials($adminUser)) ?></span>
                <span class="d-none d-sm-inline"><?= e($adminUser['prenom'] ?? 'Admin') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><span class="dropdown-header"><?= e($adminUser['email'] ?? '') ?><br><strong>Administrateur</strong></span></li>
                <li><a class="dropdown-item" href="<?= url('home') ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-2"></i>Voir le site</a></li>
                <li><a class="dropdown-item" href="<?= url('profil') ?>"><i class="bi bi-person me-2"></i>Mon profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="post" action="<?= url('logout') ?>">
                        <?= csrfField() ?>
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Se déconnecter</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
<!-- ======== Header End ======== -->
