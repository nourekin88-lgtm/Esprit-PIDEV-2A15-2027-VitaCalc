<?php
$range = static fn (array $limits, int $digits = 0): string => $format($limits[0], $digits) . '–' . $format($limits[1], $digits);
$nutrients = [
    ['Glucides', 'grain', 'carbs', $estimate['carbs'] === null ? null : $range($estimate['carbs']), '45 à 60 % de l’énergie'],
    ['Protéines', 'egg', 'protein', $estimate['protein'] === null ? null : $format($estimate['protein']), 'Repère adulte selon le poids'],
    ['Lipides', 'drop', 'fat', $estimate['fat'] === null ? null : $range($estimate['fat']), '20 à 35 % de l’énergie'],
    ['Fibres', 'fibre', 'fibre', $estimate['fibre'] === null ? null : $format($estimate['fibre']), 'Repère adulte de l’EFSA'],
];
?>
<section class="plan-section" aria-labelledby="repere-title">
    <div class="section-heading"><h2 id="repere-title">Mes repères personnels</h2></div>
    <div class="report-top">
        <article class="report-energy">
            <span class="energy-emblem"><?= icon('energy', 45) ?></span>
            <div class="energy-content">
                <h3>Énergie de maintien estimée</h3>
                <?php if ($estimate['energy'] !== null): ?>
                    <p class="energy-number"><?= e($range($estimate['energy'])) ?></p>
                    <p class="energy-unit">kcal / jour</p>
                    <a class="energy-activity" href="#mon-objectif">Activité <?= e(mb_strtolower($activities[$activity]['label'], 'UTF-8')) ?></a>
                <?php else: ?>
                    <p class="energy-number">—</p>
                    <p class="energy-unit">kcal / jour</p>
                <?php endif; ?>
            </div>
        </article>

        <div class="report-side">
            <article class="plan-metric side-metric side-water">
                <span class="tile-icon"><?= icon('drop', 23) ?></span>
                <div>
                    <h3>Eau totale estimée</h3>
                    <?php if ($estimate['water'] !== null): ?>
                        <p class="metric-value"><?= e($range($estimate['water'], 1)) ?> <span>L/jour</span></p>
                    <?php else: ?>
                        <p class="metric-value">—</p>
                    <?php endif; ?>
                    <p>Boissons et aliments</p>
                </div>
            </article>

            <article class="plan-metric side-metric side-bmi">
                <span class="tile-icon"><?= icon('scale', 23) ?></span>
                <div>
                    <h3>IMC</h3>
                    <?php if ($estimate['bmi'] !== null): ?>
                        <p class="metric-value"><?= e($format($estimate['bmi'], 1)) ?> <span>kg/m²</span></p>
                        <p><?= $estimate['adult'] ? 'Poids rapporté à la taille' : 'À lire avec les courbes de croissance' ?></p>
                    <?php else: ?>
                        <p class="metric-value">—</p>
                        <p>Calcul à partir de 2 ans</p>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    </div>

    <div class="plan-metrics report-nutrients">
        <?php foreach ($nutrients as [$name, $symbol, $tone, $value, $detail]): ?>
            <article class="plan-metric nutrient-card nutrient-<?= e($tone) ?>">
                <span class="tile-icon"><?= icon($symbol, 21) ?></span>
                <h3><?= e($name) ?></h3>
                <p class="metric-value"><?= e($value ?? '—') ?> <?php if ($value !== null): ?><span>g/jour</span><?php endif; ?></p>
                <p><?= e($detail) ?></p>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($estimate['energy'] === null): ?>
        <p class="plan-note report-note">Les apports journaliers estimés s’affichent lorsque le profil entre dans la plage prévue par le calcul. Un professionnel peut vous aider à définir des repères adaptés.</p>
    <?php else: ?>
        <p class="report-reading">Les fourchettes de maintien se lisent séparément. Les menus proposés illustrent une journée, avec des portions à adapter.</p>
    <?php endif; ?>

    <details class="plan-method">
        <summary>Comment lire et calculer mes repères</summary>
        <p>L’IMC est le poids en kg divisé par la taille en mètres au carré. <a href="https://www.cdc.gov/BMI/about/index.html">CDC · IMC</a></p>
        <p>L’énergie de maintien combine les deux coefficients de Mifflin–St Jeor et le niveau d’activité choisi. Le calcul utilise l’âge, le poids et la taille. <a href="https://pubmed.ncbi.nlm.nih.gov/2305711/">Équation de Mifflin–St Jeor</a></p>
        <p>Les glucides représentent 45 à 60 % de l’énergie, à 4 kcal/g, et les lipides 20 à 35 %, à 9 kcal/g. Les protéines suivent le repère adulte de 0,83 g/kg/jour. Le repère adulte pour les fibres est de 25 g/jour. <a href="https://www.efsa.europa.eu/sites/default/files/2017_09_DRVs_summary_report.pdf">EFSA · Repères nutritionnels</a></p>
        <p>L’eau totale utilise un repère approximatif de 1 mL par kcal de dépense. La chaleur et l’entraînement peuvent modifier les besoins. <a href="https://backend.orbit.dtu.dk/ws/files/6350877/5.Scientific.pdf">EFSA · Eau</a></p>
        <p>Ces estimations sont prévues pour des adultes en bonne santé de 19 à 78 ans. Une grossesse, un allaitement, une restriction hydrique ou une situation médicale demandent des repères adaptés.</p>
    </details>
</section>
