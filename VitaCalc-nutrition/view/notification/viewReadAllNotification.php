<div class="page-heading">
    <h1>Notifications</h1>
    <p>Choisissez les rappels quotidiens et leur heure sur cet appareil.</p>
</div>

<noscript><p class="notice error">Activez JavaScript pour programmer les rappels.</p></noscript>

<form class="form-panel reminder-panel" data-reminder-form hidden>
    <div class="reminder-settings">
        <?php foreach (dailyReminders() as $id => [$label, $symbol, $hour]): ?>
            <div class="reminder-row" data-reminder="<?= e($id) ?>">
                <span class="nav-symbol"><?= icon($symbol, 20) ?></span>
                <strong id="reminder-label-<?= e($id) ?>"><?= e($label) ?></strong>
                <label class="visually-hidden" for="reminder-time-<?= e($id) ?>">Heure du rappel · <?= e($label) ?></label>
                <input type="time" id="reminder-time-<?= e($id) ?>" value="<?= e($hour) ?>" required>
                <label class="reminder-toggle">
                    <input class="visually-hidden" type="checkbox" role="switch" aria-labelledby="reminder-label-<?= e($id) ?>">
                    <span aria-hidden="true"></span>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="form-actions reminder-actions">
        <button type="submit" class="button button-primary"><?= icon('check', 18) ?> Enregistrer les rappels</button>
        <p class="reminder-feedback" data-reminder-feedback role="status"></p>
    </div>
    <div class="reminder-permission">
        <p>Laissez un onglet VitaCalc ouvert pour recevoir vos rappels. Les réglages sont enregistrés dans ce navigateur.</p>
        <button type="button" class="button button-secondary" data-reminder-permission hidden><?= icon('bell', 18) ?> Autoriser les notifications système</button>
        <p class="form-hint" data-reminder-permission-status role="status"></p>
    </div>
</form>
