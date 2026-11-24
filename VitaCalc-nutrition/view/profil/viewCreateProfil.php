<section class="signup-shell" aria-labelledby="signup-title">
    <div class="signup-intro">
        <span class="signup-emblem"><?= icon('leaf', 23) ?></span>
        <h2>Un profil<br> à votre mesure.</h2>
        <p>Vos repères et votre objectif guident les idées de repas.</p>
    </div>

    <div class="signup-panel">
        <a class="back-link" href="<?= e(route($editing ? 'inscription' : 'accueil')) ?>">
            <?= $editing ? 'Retour à mon profil' : 'Retour à l’accueil' ?>
        </a>
        <h1 id="signup-title"><?= $editing ? 'Modifier mon profil' : 'Créer mon profil' ?></h1>

        <form method="post" action="<?= e(route('inscription')) ?>" data-signup>
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <?php if ($pendingGoal !== ''): ?>
                <input type="hidden" name="objectif_suggere" value="<?= e($pendingGoal) ?>">
            <?php endif; ?>
            <ol class="signup-progress" aria-label="Étapes du formulaire" data-signup-progress hidden>
                <li><span class="step-marker">1</span> Genre</li>
                <li><span class="step-marker">2</span> Âge</li>
                <li><span class="step-marker">3</span> Mesures</li>
            </ol>

            <fieldset class="signup-step" data-signup-step>
                <legend tabindex="-1">Quel est votre genre ?</legend>
                <div class="gender-options">
                    <?php foreach ($genders as $key => $label): ?>
                        <label class="gender-option">
                            <input type="radio" name="genre" value="<?= e($key) ?>" required
                                <?= $values['genre'] === $key ? 'checked' : '' ?>
                                <?= isset($errors['genre']) ? 'aria-invalid="true" aria-describedby="genre-error"' : '' ?>>
                            <span class="gender-label"><span class="gender-radio" aria-hidden="true"></span><?= e($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['genre'])): ?>
                    <p class="field-error" id="genre-error"><?= e($errors['genre']) ?></p>
                <?php endif; ?>
            </fieldset>

            <fieldset class="signup-step" data-signup-step>
                <legend tabindex="-1">Quel âge avez-vous ?</legend>
                <label class="visually-hidden" for="signup-age">Âge en années</label>
                <div class="measurement-input age-input">
                    <?= icon('calendar', 21) ?>
                    <input type="number" id="signup-age" name="age" min="1" max="125" step="1" inputmode="numeric"
                        placeholder="Ex. 24" value="<?= e($values['age']) ?>" required
                        <?= isset($errors['age']) ? 'aria-invalid="true" aria-describedby="age-error"' : '' ?>>
                    <span class="measurement-unit" aria-hidden="true">ans</span>
                </div>
                <?php if (isset($errors['age'])): ?>
                    <p class="field-error" id="age-error"><?= e($errors['age']) ?></p>
                <?php endif; ?>
            </fieldset>

            <fieldset class="signup-step" data-signup-step>
                <legend tabindex="-1">Quelles sont vos mesures ?</legend>
                <p class="signup-hint" id="signup-measurement-hint">Vous pouvez utiliser une virgule pour les décimales.</p>
                <div class="measurements-grid">
                    <div>
                        <label for="signup-weight">Poids<span class="visually-hidden"> en kilogrammes</span></label>
                        <div class="measurement-input">
                            <?= icon('scale', 21) ?>
                            <input type="text" id="signup-weight" name="poids" maxlength="5" inputmode="decimal"
                                pattern="[0-9]{1,3}([,.][0-9])?" data-min="0.1" data-max="700"
                                placeholder="Ex. 68,5" value="<?= e($values['poids']) ?>" required
                                aria-describedby="signup-measurement-hint<?= isset($errors['poids']) ? ' poids-error' : '' ?>"
                                <?= isset($errors['poids']) ? 'aria-invalid="true"' : '' ?>>
                            <span class="measurement-unit" aria-hidden="true">kg</span>
                        </div>
                        <?php if (isset($errors['poids'])): ?>
                            <p class="field-error" id="poids-error"><?= e($errors['poids']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="signup-height">Taille<span class="visually-hidden"> en centimètres</span></label>
                        <div class="measurement-input">
                            <?= icon('ruler', 21) ?>
                            <input type="text" id="signup-height" name="taille" maxlength="5" inputmode="decimal"
                                pattern="[0-9]{1,3}([,.][0-9])?" data-min="20" data-max="300"
                                placeholder="Ex. 172" value="<?= e($values['taille']) ?>" required
                                aria-describedby="signup-measurement-hint<?= isset($errors['taille']) ? ' taille-error' : '' ?>"
                                <?= isset($errors['taille']) ? 'aria-invalid="true"' : '' ?>>
                            <span class="measurement-unit" aria-hidden="true">cm</span>
                        </div>
                        <?php if (isset($errors['taille'])): ?>
                            <p class="field-error" id="taille-error"><?= e($errors['taille']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </fieldset>

            <div class="signup-actions">
                <button type="button" class="button button-secondary" data-signup-back hidden>Précédent</button>
                <button type="button" class="button button-primary" data-signup-next hidden>Continuer</button>
                <button type="submit" class="button button-primary" data-signup-save>
                    <?= $editing ? 'Enregistrer les modifications' : 'Créer mon profil' ?>
                </button>
            </div>
        </form>
    </div>
</section>
