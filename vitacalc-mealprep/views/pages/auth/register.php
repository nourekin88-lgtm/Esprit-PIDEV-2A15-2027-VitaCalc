<?php
/** Affiche un champ de formulaire d'inscription. */
$field = function (string $name, string $label, string $type = 'text', string $extra = '') use ($errors, $old): string {
    $invalid = isset($errors[$name]) ? ' is-invalid' : '';
    return '<label for="' . $name . '" class="form-label">' . $label . '</label>'
        . '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" class="form-control' . $invalid . '" value="' . e(old($old, $name)) . '" ' . $extra . '>'
        . '<div class="invalid-feedback">' . e($errors[$name] ?? '') . '</div>';
};
?>
<h1>Créez votre compte 🌿</h1>
<p class="text-muted mb-4">C'est gratuit : accédez aux recettes complètes, aux favoris et à la liste de courses.</p>

<form method="post" action="<?= url('register') ?>" novalidate>
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-sm-6"><?= $field('prenom', 'Prénom', 'text', 'autocomplete="given-name" required autofocus') ?></div>
        <div class="col-sm-6"><?= $field('nom', 'Nom', 'text', 'autocomplete="family-name" required') ?></div>
        <div class="col-12"><?= $field('email', 'Adresse e-mail', 'email', 'autocomplete="email" placeholder="vous@exemple.com" required') ?></div>
        <div class="col-12"><?= $field('telephone', 'Téléphone <span class="text-muted fw-normal">(facultatif)</span>', 'text', 'autocomplete="tel" placeholder="20 123 456"') ?></div>

        <div class="col-12">
            <label for="motDePasse" class="form-label">Mot de passe</label>
            <div class="pwd-wrap">
                <input type="password" id="motDePasse" name="motDePasse" autocomplete="new-password" required
                       class="form-control<?= isset($errors['motDePasse']) ? ' is-invalid' : '' ?>"
                       data-strength="#strength" data-strength-label="#strengthLabel" placeholder="8 caractères minimum, avec un chiffre">
                <button type="button" class="pwd-toggle" data-toggle-password="#motDePasse" aria-label="Afficher le mot de passe"><i class="bi bi-eye"></i></button>
            </div>
            <?php if (isset($errors['motDePasse'])): ?><div class="text-danger small mt-1"><?= e($errors['motDePasse']) ?></div><?php endif; ?>
            <div class="strength" id="strength" data-level="0"><span></span><span></span><span></span><span></span></div>
            <div class="strength-label" id="strengthLabel"></div>
        </div>
        <div class="col-12">
            <label for="confirmation" class="form-label">Confirmer le mot de passe</label>
            <input type="password" id="confirmation" name="confirmation" autocomplete="new-password" required
                   class="form-control<?= isset($errors['confirmation']) ? ' is-invalid' : '' ?>">
            <div class="invalid-feedback"><?= e($errors['confirmation'] ?? '') ?></div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">Créer mon compte</button>
</form>

<p class="text-center text-muted mt-4 mb-0">Déjà inscrit ? <a href="<?= url('login') ?>" class="fw-semibold">Se connecter</a></p>
