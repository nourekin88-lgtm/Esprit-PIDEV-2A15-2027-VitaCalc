<section class="login-panel form-panel">
    <span class="tile-icon regimes"><?= icon('shield', 25) ?></span>
    <h1>Connexion administrateur</h1>
    <p>Identifiez-vous pour gérer les fiches nutrition de VitaCalc.</p>
    <?php if (!$configured): ?>
        <p class="notice error" role="status"><?= icon('alert', 18) ?> Le compte administrateur doit d’abord être créé depuis le terminal.</p>
    <?php else: ?>
        <?php if ($error): ?><p class="notice error" role="alert"><?= icon('alert', 18) ?> <?= e($error) ?></p><?php endif; ?>
        <form method="post" action="<?= e(route('connexion')) ?>">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <div class="form-field">
                <label for="identifiant">Identifiant</label>
                <input id="identifiant" name="identifiant" required maxlength="60" autocomplete="username" value="<?= e($user) ?>">
            </div>
            <div class="form-field">
                <label for="mot_de_passe">Mot de passe</label>
                <input id="mot_de_passe" type="password" name="mot_de_passe" required autocomplete="current-password">
            </div>
            <button class="button button-primary" type="submit">Se connecter <?= icon('arrow', 17) ?></button>
        </form>
    <?php endif; ?>
    <a class="back-link" href="<?= e(route('accueil')) ?>"><?= icon('back', 17) ?> Revenir à l’espace nutrition</a>
</section>
