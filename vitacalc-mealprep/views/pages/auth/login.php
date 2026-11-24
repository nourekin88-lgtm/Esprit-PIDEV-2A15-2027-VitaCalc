<h1>Content de vous revoir 👋</h1>
<p class="text-muted mb-4">Connectez-vous pour retrouver vos recettes, vos favoris et votre liste de courses.</p>

<?php if (!empty($errors['global'])): ?>
    <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle me-2"></i><?= e($errors['global']) ?></div>
<?php endif; ?>

<form method="post" action="<?= url('login') ?>" novalidate>
    <?= csrfField() ?>
    <div class="mb-3">
        <label for="email" class="form-label">Adresse e-mail</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="vous@exemple.com"
               autocomplete="email" value="<?= e(old($old, 'email')) ?>" required autofocus>
    </div>
    <div class="mb-4">
        <label for="motDePasse" class="form-label">Mot de passe</label>
        <div class="pwd-wrap">
            <input type="password" id="motDePasse" name="motDePasse" class="form-control" placeholder="Votre mot de passe"
                   autocomplete="current-password" required>
            <button type="button" class="pwd-toggle" data-toggle-password="#motDePasse" aria-label="Afficher le mot de passe"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100">Se connecter</button>
</form>

<p class="text-center text-muted mt-4 mb-0">Pas encore de compte ? <a href="<?= url('register') ?>" class="fw-semibold">Créer un compte gratuit</a></p>

<?php if (SHOW_DEMO_ACCOUNTS): ?>
    <div class="demo-box">
        <div class="fw-semibold mb-1"><i class="bi bi-stars me-1 text-primary"></i>Comptes de démonstration</div>
        <div class="demo-row">
            <span><strong>Client</strong> · <code>client@vitacalc.tn</code> · <code>Client@123</code></span>
            <button type="button" class="btn btn-sm btn-outline-primary" data-fill-email="client@vitacalc.tn" data-fill-pass="Client@123">Remplir</button>
        </div>
        <div class="demo-row">
            <span><strong>Admin</strong> · <code>admin@vitacalc.tn</code> · <code>Admin@123</code></span>
            <button type="button" class="btn btn-sm btn-outline-primary" data-fill-email="admin@vitacalc.tn" data-fill-pass="Admin@123">Remplir</button>
        </div>
    </div>
<?php endif; ?>
