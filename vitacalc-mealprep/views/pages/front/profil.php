<section class="page-hero">
    <span class="blob blob-a"></span><span class="plus plus-1">+</span><span class="plus plus-2">+</span>
    <div class="container position-relative text-center">
        <span class="eyebrow-pill"><i class="bi bi-person me-1"></i>Mon espace</span>
        <h1>Mon profil</h1>
        <p class="mb-0 text-muted-2">Gérez vos informations personnelles et votre mot de passe.</p>
    </div>
</section>

<section class="section pt-4">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4">
                <div class="panel text-center">
                    <span class="avatar xl mx-auto"><?= e(initials($compte)) ?></span>
                    <h4 class="mt-3 mb-0"><?= e($compte['prenom'] . ' ' . $compte['nom']) ?></h4>
                    <p class="text-muted"><?= e($compte['email']) ?></p>
                    <span class="chip"><i class="bi bi-shield-check"></i><?= $compte['role'] === ROLE_ADMIN ? 'Administrateur' : 'Client' ?></span>
                    <hr>
                    <div class="row text-center g-0">
                        <div class="col-6"><div class="stat-num"><?= (int) $nbFavoris ?></div><small class="text-muted">favoris</small></div>
                        <div class="col-6"><div class="stat-num"><?= e(date('d/m/Y', strtotime($compte['dateInscription']))) ?></div><small class="text-muted">membre depuis</small></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="panel">
                    <form method="post" action="<?= url('profil') ?>" novalidate>
                        <?= csrfField() ?>
                        <h5 class="mb-3">Informations</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="prenom">Prénom</label>
                                <input type="text" id="prenom" name="prenom" class="form-control <?= isset($errors['prenom']) ? 'is-invalid' : '' ?>" value="<?= e(old($form, 'prenom')) ?>">
                                <div class="invalid-feedback"><?= e($errors['prenom'] ?? '') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="nom">Nom</label>
                                <input type="text" id="nom" name="nom" class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>" value="<?= e(old($form, 'nom')) ?>">
                                <div class="invalid-feedback"><?= e($errors['nom'] ?? '') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">E-mail</label>
                                <input type="email" id="email" class="form-control" value="<?= e($compte['email']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="telephone">Téléphone</label>
                                <input type="text" id="telephone" name="telephone" class="form-control <?= isset($errors['telephone']) ? 'is-invalid' : '' ?>" value="<?= e(old($form, 'telephone')) ?>">
                                <div class="invalid-feedback"><?= e($errors['telephone'] ?? '') ?></div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-1">Changer le mot de passe</h5>
                        <p class="text-muted small">Laissez vide pour conserver votre mot de passe actuel.</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="motDePasseActuel">Mot de passe actuel</label>
                                <input type="password" id="motDePasseActuel" name="motDePasseActuel" autocomplete="current-password" class="form-control <?= isset($errors['motDePasseActuel']) ? 'is-invalid' : '' ?>">
                                <div class="invalid-feedback"><?= e($errors['motDePasseActuel'] ?? '') ?></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="nouveauMotDePasse">Nouveau mot de passe</label>
                                <input type="password" id="nouveauMotDePasse" name="nouveauMotDePasse" autocomplete="new-password" class="form-control <?= isset($errors['nouveauMotDePasse']) ? 'is-invalid' : '' ?>">
                                <div class="invalid-feedback"><?= e($errors['nouveauMotDePasse'] ?? '') ?></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="confirmation">Confirmation</label>
                                <input type="password" id="confirmation" name="confirmation" autocomplete="new-password" class="form-control <?= isset($errors['confirmation']) ? 'is-invalid' : '' ?>">
                                <div class="invalid-feedback"><?= e($errors['confirmation'] ?? '') ?></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4 px-4"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
