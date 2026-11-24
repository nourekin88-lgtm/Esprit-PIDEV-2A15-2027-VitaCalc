<?php if ($estimate['adult']): ?>
    <section class="plan-section plan-goals" id="mon-objectif" aria-labelledby="goal-title">
        <div class="section-heading"><h2 id="goal-title">Mon objectif</h2></div>
        <form method="post" action="<?= e(route('inscription')) ?>">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="objectif">
            <?php if ($choiceError !== null): ?>
                <p class="notice error" role="alert"><?= e($choiceError) ?></p>
            <?php endif; ?>
            <fieldset class="signup-step">
                <legend class="visually-hidden">Choisir mon objectif nutritionnel</legend>
                <div class="goal-options">
                    <?php foreach ($goals as $key => $option): ?>
                        <label class="goal-card">
                            <input type="radio" name="objectif" value="<?= e($key) ?>" required <?= $selectedGoal === $key ? 'checked' : '' ?>>
                            <span class="goal-card-inner">
                                <span class="visual-symbol goal-picture">
                                    <?= icon($option['icon'], 36) ?>
                                    <span class="goal-check" aria-hidden="true"><?= icon('check', 16) ?></span>
                                </span>
                                <span class="goal-caption">
                                    <span class="gender-radio" aria-hidden="true"></span><?= e($option['label']) ?>
                                </span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <div class="goal-actions">
                <div class="activity-input">
                    <label for="plan-activity">Activité habituelle</label>
                    <div class="activity-select">
                        <select name="activite" id="plan-activity">
                            <?php foreach ($activities as $key => $option): ?>
                                <option value="<?= e($key) ?>" <?= $activity === $key ? 'selected' : '' ?>><?= e($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= icon('chevron', 15) ?>
                    </div>
                </div>
                <button class="button button-primary" type="submit"><?= $goal === '' ? 'Voir mon régime' : 'Actualiser mon régime' ?></button>
            </div>
        </form>
    </section>
<?php else: ?>
    <p class="plan-note">Pour les moins de 18 ans, les besoins varient avec la croissance. Un professionnel peut vous orienter vers des repères adaptés.</p>
<?php endif; ?>
