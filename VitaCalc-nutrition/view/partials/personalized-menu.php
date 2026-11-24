<?php
$totals = $menu['totals'];
$foodMacronutrients = static fn (array $value): string =>
    $format($value['kcal']) . ' kcal · P ' . $format($value['protein'], 1) . ' g · G '
    . $format($value['carbs'], 1) . ' g · L ' . $format($value['fat'], 1) . ' g · Fibres ' . $format($value['fibre'], 1) . ' g';
?>
<div class="menu-totals" aria-label="Apports estimés du menu par jour">
    <div><strong><?= e($format($totals['kcal'])) ?></strong><span> kcal</span></div>
    <div><strong><?= e($format($totals['protein'], 1)) ?> g</strong><span> de protéines</span></div>
    <div><strong><?= e($format($totals['carbs'], 1)) ?> g</strong><span> de glucides</span></div>
    <div><strong><?= e($format($totals['fat'], 1)) ?> g</strong><span> de lipides</span></div>
    <div><strong><?= e($format($totals['fibre'], 1)) ?> g</strong><span> de fibres</span></div>
</div>

<h3>Ma journée de repas</h3>
<div class="personalized-meals">
    <?php foreach (MenuPlanner::MEALS as $mealKey => [$mealLabel, $mealImage]): ?>
        <article class="personalized-meal">
            <div class="meal-heading">
                <img src="<?= e(photo($mealImage)) ?>" width="80" height="80" alt="" loading="lazy" decoding="async">
                <h4><?= e($mealLabel) ?></h4>
            </div>
            <ul class="meal-ingredients">
                <?php foreach ($menu['items'] as $slot => $item): ?>
                    <?php if ($item['meal'] !== $mealKey) continue; ?>
                    <li class="ingredient" id="aliment-<?= e($slot) ?>">
                        <div class="ingredient-current">
                            <img src="<?= e(photo($item['photo'])) ?>" alt="" loading="lazy" decoding="async">
                            <div>
                                <strong><?= e($item['label']) ?></strong>
                                <span><?= e($item['grams']) ?> g</span>
                            </div>
                        </div>
                        <details class="ingredient-alternatives">
                            <summary>Remplacer<span class="visually-hidden"> <?= e($item['label']) ?></span></summary>
                            <?php if ($item['options'] === []): ?>
                                <p>Aucun équivalent assez proche dans le catalogue pour cette portion.</p>
                            <?php else: ?>
                                <p>Portion ajustée · P protéines, G glucides, L lipides.</p>
                                <p><strong>Actuellement</strong> <?= e($foodMacronutrients($item['values'])) ?></p>
                                <form action="<?= e(route('inscription')) ?>" method="post">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="remplacement">
                                    <input type="hidden" name="contexte" value="<?= e($context) ?>">
                                    <input type="hidden" name="emplacement" value="<?= e($slot) ?>">
                                    <?php foreach ($item['options'] as $foodId => $option): ?>
                                        <div class="ingredient-option">
                                            <div>
                                                <strong><?= e($option['label']) ?> · <?= e($option['grams']) ?> g</strong>
                                                <span><?= e($foodMacronutrients($option['values'])) ?></span>
                                            </div>
                                            <button class="button button-secondary" type="submit" name="aliment" value="<?= e($foodId) ?>">Choisir</button>
                                        </div>
                                    <?php endforeach; ?>
                                </form>
                            <?php endif; ?>
                        </details>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>
    <?php endforeach; ?>
</div>
<details class="plan-method menu-method">
    <summary>Comment sont choisis les équivalents ?</summary>
    <p>Les équivalences comparent l’énergie, les protéines, les glucides, les lipides et les fibres d’une portion. Les micronutriments, les allergènes et la composition des produits peuvent différer. <a href="https://fdc.nal.usda.gov/download-datasets/">USDA FoodData Central · SR Legacy</a></p>
</details>
