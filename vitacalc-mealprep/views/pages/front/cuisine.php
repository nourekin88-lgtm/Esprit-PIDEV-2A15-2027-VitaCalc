<?php
/**
 * MODE CUISINE : variables $recette, $lignes (ingrédients), $etapes (texte + minutes détectées).
 * Diapositives : 0 = ingrédients, 1..N = étapes, N+1 = fin.
 */
$nbEtapes = count($etapes);
?>
<?php $photo = imageAvailable($recette['image'] ?? null) ? $recette['image'] : ''; ?>
<div class="cook" id="cook" data-steps="<?= (int) $nbEtapes ?>" data-base="<?= max(1, (int) $recette['nombrePortions']) ?>">
    <?php if ($photo): ?><div class="cook-bg" aria-hidden="true"><img src="<?= e($photo) ?>" alt=""></div><?php endif; ?>

    <header class="cook-top">
        <a class="cook-icon-btn" href="<?= url('recette', ['id' => $recette['id']]) ?>" aria-label="Quitter le mode cuisine" title="Quitter">
            <i class="bi bi-x-lg"></i>
        </a>
        <div class="cook-title">
            <small>Mode cuisine</small>
            <strong><?= e($recette['nom']) ?></strong>
        </div>
        <button type="button" class="cook-icon-btn" id="cookCamBtn" aria-pressed="false" aria-label="Commande par la main (caméra)" title="Changer d'étape avec la main (caméra)">
            <i class="bi bi-camera-video"></i>
        </button>
        <button type="button" class="cook-icon-btn" id="cookIngBtn" aria-label="Voir les ingrédients" title="Ingrédients">
            <i class="bi bi-list-check"></i>
        </button>
        <button type="button" class="cook-icon-btn" id="cookFsBtn" aria-label="Plein écran" title="Plein écran">
            <i class="bi bi-fullscreen"></i>
        </button>
    </header>

    <div class="cook-progress" aria-hidden="true"><span id="cookBar"></span></div>
    <div class="cook-timers" id="cookTimers" aria-live="polite"></div>

    <main class="cook-stage" id="cookStage">

        <!-- 0 : ingrédients -->
        <section class="cook-slide is-active" tabindex="-1" aria-label="Ingrédients">
            <?php if ($photo): ?>
                <div class="cook-hero"><img src="<?= e($photo) ?>" alt="<?= e($recette['nom']) ?>"></div>
            <?php endif; ?>
            <div class="cook-eyebrow"><i class="bi bi-basket2"></i> <?= $prenom !== '' ? e($prenom) . ', on cuisine ?' : 'Avant de commencer' ?></div>
            <h1 class="cook-heading"><?= e($recette['nom']) ?></h1>
            <p class="cook-meta">
                <span><i class="bi bi-clock"></i> <?= e(formatDuration((int) $recette['tempsPreparation'])) ?></span>
                <span><i class="bi bi-list-ol"></i> <?= (int) $nbEtapes ?> étapes</span>
                <span class="cook-portions" aria-label="Nombre de portions">
                    <button type="button" data-act="pminus" aria-label="Moins de portions">−</button>
                    <b><i class="bi bi-people"></i> <span id="cookPortions"><?= (int) $recette['nombrePortions'] ?></span> portions</b>
                    <button type="button" data-act="pplus" aria-label="Plus de portions">+</button>
                </span>
            </p>
            <?php if (empty($lignes)): ?>
                <p class="cook-empty">Aucun ingrédient renseigné pour cette recette.</p>
            <?php else: ?>
                <ul class="cook-ings" id="cookIngList">
                    <?php foreach ($lignes as $l): ?>
                        <li tabindex="0" role="checkbox" aria-checked="false">
                            <?= ingredientThumb(['id' => $l['id'], 'nom' => $l['nom'], 'image' => $l['image']], 'md') ?>
                            <span class="nm"><?= e($l['nom']) ?></span>
                            <span class="qt" data-q="<?= e((string) (float) $l['quantite']) ?>" data-u="<?= e($l['unite']) ?>"><?= e(qty($l['quantite'])) ?> <?= e($l['unite']) ?></span>
                            <span class="ck"><i class="bi bi-check-lg"></i></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="cook-hint">Touchez un ingrédient pour le cocher quand il est sorti. Les quantités s'adaptent au nombre de portions.</p>
            <?php endif; ?>
            <button type="button" class="cook-cta" data-act="start"><i class="bi bi-play-fill"></i> C'est parti !</button>
        </section>

        <!-- 1..N : étapes -->
        <?php foreach ($etapes as $n => $etape): ?>
            <section class="cook-slide" tabindex="-1" aria-label="Étape <?= $n + 1 ?>"
                     <?= $etape['minutes'] ? 'data-minutes="' . (int) $etape['minutes'] . '"' : '' ?>>
                <div class="cook-step-head">
                    <div class="cook-step-no"><?= $n + 1 ?></div>
                    <span class="cook-action"><i class="bi <?= e($etape['action'][0]) ?>"></i><?= e($etape['action'][1]) ?></span>
                </div>
                <div class="cook-eyebrow">Étape <?= $n + 1 ?> sur <?= (int) $nbEtapes ?></div>

                <?php if (!empty($etape['ings'])): ?>
                    <ul class="cook-step-ings" aria-label="Ingrédients de cette étape">
                        <?php foreach ($etape['ings'] as $l): ?>
                            <li>
                                <?= ingredientThumb(['id' => $l['id'], 'nom' => $l['nom'], 'image' => $l['image']], 'xl') ?>
                                <span class="nm"><?= e($l['nom']) ?></span>
                                <span class="qt" data-q="<?= e((string) (float) $l['quantite']) ?>" data-u="<?= e($l['unite']) ?>"><?= e(qty($l['quantite'])) ?> <?= e($l['unite']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <p class="cook-text"><?= e($etape['texte']) ?></p>

                <div class="cook-timer" <?= $etape['minutes'] ? '' : 'hidden' ?>>
                    <div class="cook-clock" data-role="display" aria-live="off">--:--</div>
                    <div class="cook-timer-actions">
                        <button type="button" class="cook-btn" data-act="minus" aria-label="Retirer une minute">− 1 min</button>
                        <button type="button" class="cook-btn primary" data-act="toggle"><i class="bi bi-play-fill"></i><span>Démarrer</span></button>
                        <button type="button" class="cook-btn" data-act="plus" aria-label="Ajouter une minute">+ 1 min</button>
                        <button type="button" class="cook-btn icon" data-act="reset" aria-label="Remettre à zéro" title="Remettre à zéro"><i class="bi bi-arrow-counterclockwise"></i></button>
                    </div>
                </div>
                <?php if (!$etape['minutes']): ?>
                    <button type="button" class="cook-btn ghost" data-act="add"><i class="bi bi-stopwatch"></i> Ajouter un minuteur</button>
                <?php endif; ?>
                <p class="cook-next-up">
                    <?= isset($etapes[$n + 1]) ? '<span>Ensuite</span> ' . e($etapes[$n + 1]['texte']) : '<span>Ensuite</span> Dernière étape : bientôt à table !' ?>
                </p>
            </section>
        <?php endforeach; ?>

        <!-- N+1 : fin -->
        <section class="cook-slide" tabindex="-1" aria-label="Terminé">
            <?php if ($photo): ?>
                <div class="cook-hero cook-hero-done"><img src="<?= e($photo) ?>" alt="<?= e($recette['nom']) ?>"><span class="cook-badge">🎉</span></div>
            <?php else: ?>
                <div class="cook-done-ico">🎉</div>
            <?php endif; ?>
            <h1 class="cook-heading"><?= $prenom !== '' ? 'Bravo ' . e($prenom) . ', bon appétit !' : 'Bon appétit !' ?></h1>
            <p class="cook-text small">« <?= e($recette['nom']) ?> » est prête. Rangez vos portions dans des boîtes et profitez toute la semaine.</p>
            <div class="cook-end-actions">
                <a class="cook-cta" href="<?= url('recette', ['id' => $recette['id']]) ?>#avis"><i class="bi bi-star-fill"></i> Noter cette recette</a>
                <a class="cook-btn" href="<?= url('recettes') ?>">Autres recettes</a>
                <button type="button" class="cook-btn" data-act="restart"><i class="bi bi-arrow-counterclockwise"></i> Recommencer</button>
            </div>
        </section>
    </main>

    <footer class="cook-nav">
        <button type="button" class="cook-nav-btn" id="cookPrev" aria-label="Étape précédente"><i class="bi bi-chevron-left"></i><span>Précédent</span></button>
        <div class="cook-pos" id="cookPos" aria-live="polite">Ingrédients</div>
        <button type="button" class="cook-nav-btn next" id="cookNext" aria-label="Étape suivante"><span>Suivant</span><i class="bi bi-chevron-right"></i></button>
    </footer>

    <!-- Commande par la main : aperçu de la caméra (traitée uniquement dans le navigateur, rien n'est envoyé ni enregistré) -->
    <div class="cook-cam" id="cookCam" hidden>
        <video id="cookCamVideo" autoplay muted playsinline></video>
        <div class="cam-flash" id="cookCamFlash" aria-hidden="true"></div>
        <div class="cam-bar" aria-hidden="true"><span id="cookCamLevel"></span></div>
        <p class="cam-msg" id="cookCamMsg" aria-live="polite">Caméra…</p>
    </div>

    <!-- Ingrédients (fenêtre) -->
    <div class="cook-sheet" id="cookSheet" role="dialog" aria-modal="true" aria-label="Ingrédients" hidden>
        <div class="box">
            <div class="sheet-head"><h2>Ingrédients</h2><button type="button" class="cook-icon-btn" id="cookSheetClose" aria-label="Fermer"><i class="bi bi-x-lg"></i></button></div>
            <div id="cookSheetBody"></div>
        </div>
    </div>

    <!-- Alarme du minuteur -->
    <div class="cook-alarm" id="cookAlarm" role="alertdialog" aria-live="assertive" aria-label="Minuteur terminé" hidden>
        <div class="box">
            <div class="ring"><i class="bi bi-bell-fill"></i></div>
            <strong id="cookAlarmText">Minuteur terminé !</strong>
            <button type="button" class="cook-cta" id="cookAlarmOk">OK, j'ai vu</button>
        </div>
    </div>
</div>
