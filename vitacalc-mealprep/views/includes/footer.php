<!-- Footer Start -->
<footer class="site-footer">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-5">
                <a href="<?= url('home') ?>" class="footer-brand">
                    <img src="assets/images/logo/vitacalc-icon.png" alt="" class="footer-icon">
                    <span><span class="v">Vita</span><span class="c">Calc</span></span>
                </a>
                <p class="mt-3 mb-0 footer-text">Your Health, Your Balance. Recettes healthy, meal prep et listes de courses pour manger mieux, simplement.</p>
            </div>
            <div class="col-6 col-lg-3">
                <h6 class="footer-title">Navigation</h6>
                <a class="footer-link" href="<?= url('home') ?>">Accueil</a>
                <a class="footer-link" href="<?= url('recettes') ?>">Recettes</a>
                <?php if (isLoggedIn()): ?>
                    <a class="footer-link" href="<?= url('frigo') ?>">Qu'est-ce que j'ai dans mon frigo ?</a>
                    <a class="footer-link" href="<?= url('favoris') ?>">Mes favoris</a>
                    <a class="footer-link" href="<?= url('courses') ?>">Liste de courses</a>
                <?php else: ?>
                    <a class="footer-link" href="<?= url('login') ?>">Se connecter</a>
                    <a class="footer-link" href="<?= url('register') ?>">S'inscrire</a>
                <?php endif; ?>
            </div>
            <div class="col-6 col-lg-4">
                <h6 class="footer-title">Contact</h6>
                <p class="footer-text mb-2"><i class="bi bi-geo-alt me-2"></i>Tunis, Tunisie</p>
                <p class="footer-text mb-2"><i class="bi bi-envelope me-2"></i>contact@vitacalc.tn</p>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container py-3 text-center">&copy; <?= date('Y') ?> VitaCalc · Projet Technologies Web 2A · ESPRIT</div>
    </div>
</footer>
<!-- Footer End -->

<!-- Back to Top -->
<a href="#" class="btn btn-primary back-to-top" id="backToTop" aria-label="Haut de page"><i class="bi bi-arrow-up"></i></a>
