<?php
$v = filemtime(ASSETS_PATH . '/js/' . (($layout ?? 'front') === 'admin' ? 'admin.js' : 'app.js'));
?>
<?php if (($layout ?? 'front') === 'admin'): ?>
<script src="Views/assets/js/admin.js?v=<?= $v ?>" defer></script>
<?php else: ?>
<?php if ($currentPage === 'events'): ?>
<script src="Views/assets/lib/leaflet/leaflet.js" defer></script>
<?php endif; ?>
<script src="Views/assets/js/app.js?v=<?= $v ?>" defer></script>
<?php if ($currentPage === 'events'): ?>
<script src="Views/assets/js/events-map.js?v=<?= filemtime(ASSETS_PATH . '/js/events-map.js') ?>" defer></script>
<?php endif; ?>
<?php endif; ?>
<script src="Views/assets/js/effects.js?v=<?= filemtime(ASSETS_PATH . '/js/effects.js') ?>" defer></script>
