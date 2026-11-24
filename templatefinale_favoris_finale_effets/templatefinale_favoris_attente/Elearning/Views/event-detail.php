<?php
/**
 * Vue : Détail d'un événement
 * Variables disponibles : $event, $reserved, $capacity, $remaining,
 *                         $fillRate, $isPast, $isFull, $status, $statusClass,
 *                         $isFavorite, $waitingCount
 */
$heure   = substr($event['heure'], 0, 5);
$dateObj = new DateTimeImmutable($event['date']);

$months    = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
              'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$days      = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$dateLabel = $days[(int)$dateObj->format('w')] . ' '
           . (int)$dateObj->format('j') . ' '
           . $months[(int)$dateObj->format('n')] . ' '
           . $dateObj->format('Y');
?>

<header class="pagehero">
 <div class="shell">
  <div class="crumb">
   <a href="index.php?page=home">Accueil</a> /
   <a href="index.php?page=events">Events</a> /
   <?= e($event['titre']) ?>
  </div>
 </div>
</header>

<main class="shell detail-layout">

 <!-- ── Colonne principale ───────────────────────────────────────────────── -->
 <div class="detail-main">

  <!-- Image hero -->
  <div class="detail-hero">
   <img src="<?= e($event['image'] ?: 'Views/assets/img/events/yoga-event.jpg') ?>"
        alt="<?= e($event['titre']) ?>"
        class="detail-img">
   <div class="detail-hero-overlay">
    <span class="detail-category"><?= e($event['categorie']) ?><?= $event['format'] === 'En ligne' ? ' · En ligne' : '' ?></span>
    <span class="detail-status <?= e($statusClass) ?>"><?= e($status) ?></span>
   </div>
  </div>

  <!-- Titre + méta -->
  <div class="detail-card">
   <div class="detail-title-row">
    <h1 class="detail-title"><?= e($event['titre']) ?></h1>
    <button type="button" class="fav-detail<?= $isFavorite ? ' active' : '' ?>" data-fav-id="<?= (int)$event['id'] ?>"
            aria-pressed="<?= $isFavorite ? 'true' : 'false' ?>">
     <span class="fav-heart" aria-hidden="true"><?= $isFavorite ? '♥' : '♡' ?></span>
     <span class="fav-label"><?= $isFavorite ? 'Dans mes favoris' : 'Ajouter aux favoris' ?></span>
    </button>
   </div>

   <div class="detail-meta-row">
    <div class="detail-meta-item">
     <span class="detail-meta-icon">◷</span>
     <span>
      <b><?= e($dateLabel) ?></b><br>
      <small>à <?= e($heure) ?></small>
     </span>
    </div>
    <div class="detail-meta-item">
     <span class="detail-meta-icon">⌖</span>
     <span>
      <b><?= e($event['lieu']) ?></b><br>
      <small><?= e($event['format']) ?></small>
     </span>
    </div>
    <div class="detail-meta-item">
     <span class="detail-meta-icon">✦</span>
     <span>
      <b><?= e($event['categorie']) ?></b><br>
      <small>Catégorie</small>
     </span>
    </div>
    <div class="detail-meta-item">
     <span class="detail-meta-icon">◈</span>
     <span>
      <b><?= $event['prix'] > 0 ? e(number_format((float)$event['prix'], 2, ',', ' ')) . ' DT' : 'Gratuit' ?></b><br>
      <small>Tarif</small>
     </span>
    </div>
   </div>

   <!-- Description -->
   <div class="detail-section">
    <h2 class="detail-section-title">À propos de cet événement</h2>
    <p class="detail-description"><?= nl2br(e($event['description'])) ?></p>
   </div>
  </div>

 </div>

 <!-- ── Sidebar capacité ─────────────────────────────────────────────────── -->
 <aside class="detail-sidebar">

  <!-- Carte capacité -->
  <div class="capacity-card">
   <div class="capacity-header">
    <h3>Gestion des places</h3>
    <span class="detail-status <?= e($statusClass) ?>"><?= e($status) ?></span>
   </div>

   <!-- Compteur inscrits -->
   <div class="capacity-count">
    <span class="capacity-num"><?= $reserved ?></span>
    <span class="capacity-sep">/</span>
    <span class="capacity-total"><?= $capacity ?></span>
    <span class="capacity-label">inscrits</span>
   </div>

   <!-- Barre de progression -->
   <div class="capacity-bar-wrap" role="progressbar"
        aria-valuenow="<?= $fillRate ?>" aria-valuemin="0" aria-valuemax="100"
        aria-label="Taux de remplissage : <?= $fillRate ?>%">
    <div class="capacity-bar <?= $statusClass ?>" style="width:<?= $fillRate ?>%"></div>
   </div>
   <div class="capacity-bar-labels">
    <span><?= $fillRate ?>% rempli</span>
    <span><?= $remaining ?> place<?= $remaining > 1 ? 's' : '' ?> restante<?= $remaining > 1 ? 's' : '' ?></span>
   </div>

   <!-- Places restantes -->
   <div class="capacity-remaining <?= $isFull ? 'full' : ($fillRate >= 80 ? 'almost' : '') ?>">
    <?php if ($isPast): ?>
     <span>🔒 Cet événement est terminé.</span>
    <?php elseif ($isFull): ?>
     <span>🔴 Événement complet — toutes les places sont occupées.</span>
    <?php elseif ($fillRate >= 80): ?>
     <span>🟠 Plus que <?= $remaining ?> place<?= $remaining > 1 ? 's' : '' ?> disponible<?= $remaining > 1 ? 's' : '' ?> !</span>
    <?php else: ?>
     <span>🟢 <?= $remaining ?> place<?= $remaining > 1 ? 's' : '' ?> disponible<?= $remaining > 1 ? 's' : '' ?></span>
    <?php endif; ?>
   </div>

   <!-- Bouton inscription -->
   <?php if (!$isPast && !$isFull): ?>
    <a class="btn detail-cta"
       href="index.php?page=signup&event=<?= (int)$event['id'] ?>">
     Réserver ma place &rarr;
    </a>
   <?php elseif (!$isPast && $isFull): ?>
    <div class="waitlist-box">
     <div class="waitlist-box-head">
      <span class="waitlist-box-ico">⏳</span>
      <span>
       <b>Liste d’attente</b>
       <small><?= $waitingCount > 0 ? $waitingCount . ' personne' . ($waitingCount > 1 ? 's' : '') . ' en attente' : 'Soyez le premier sur la liste' ?></small>
      </span>
     </div>
     <p>Si une place se libère, la première personne de la liste est inscrite automatiquement.</p>
    </div>
    <a class="btn btn-waitlist detail-cta"
       href="index.php?page=signup&event=<?= (int)$event['id'] ?>">
     Rejoindre la liste d’attente &rarr;
    </a>
   <?php else: ?>
    <button class="btn btn-disabled detail-cta" disabled>
     Événement terminé
    </button>
   <?php endif; ?>

  </div>

  <!-- Récapitulatif infos -->
  <div class="detail-info-card">
   <h3>Informations</h3>
   <ul class="detail-info-list">
    <li>
     <span>Date</span>
     <strong><?= e(ucfirst($dateLabel)) ?></strong>
    </li>
    <li>
     <span>Heure</span>
     <strong><?= e($heure) ?></strong>
    </li>
    <li>
     <span>Lieu</span>
     <strong><?= e($event['lieu']) ?></strong>
    </li>
    <li>
     <span>Format</span>
     <strong><?= e($event['format']) ?></strong>
    </li>
    <li>
     <span>Catégorie</span>
     <strong><?= e($event['categorie']) ?></strong>
    </li>
    <li>
     <span>Tarif</span>
     <strong><?= $event['prix'] > 0 ? e(number_format((float)$event['prix'], 2, ',', ' ')) . ' DT' : 'Gratuit' ?></strong>
    </li>
    <li>
     <span>Capacité</span>
     <strong><?= $capacity ?> personnes</strong>
    </li>
   </ul>
  </div>

  <?php if (!empty($event['lien']) && preg_match('#^https?://#i', $event['lien'])): ?>
   <a class="btn-outline detail-official" href="<?= e($event['lien']) ?>" target="_blank" rel="noopener noreferrer">
    🔗 Page officielle de l’événement ↗
   </a>
  <?php endif; ?>

  <a class="detail-back" href="index.php?page=events">&larr; Retour aux événements</a>

 </aside>

</main>
