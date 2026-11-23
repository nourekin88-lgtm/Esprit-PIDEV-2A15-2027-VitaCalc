<?php
$baseUrl     = $baseUrl ?? '';
$currentPage = $currentPage ?? '';
$appUrl      = rtrim(str_replace('\\', '/', preg_replace('~/pkg/Views/$~', '', $baseUrl)), '/');
$appUrl      = $appUrl === '' ? '/' : $appUrl . '/';

function navActive($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}

$links = [
    'accueil'  => ['Accueil',  $appUrl . 'index.php'],
    'conseils' => ['Conseils', $appUrl . 'pkg/Controllers/ConseilController.php'],
    'apropos'  => ['À propos', '#'],
    'contact'  => ['Contact', '#'],
];
?>
<nav class="navbar">
  <a class="brand" href="index.php"><img src="<?= $baseUrl ?>assets/img/logo.png" alt="VitaCalc"></a>
  <div class="nav-links">
    <?php foreach ($links as $key => [$label, $href]): ?>
      <a href="<?= $href ?>" class="<?= navActive($key, $currentPage) ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </div>
  <span class="avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8.5" r="4" fill="#0c3b30"/><path d="M4 21c1-5 4.500-7 8-7s7 2 8 7Z" fill="#0c3b30"/></svg></span>
</nav>
