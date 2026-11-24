<header class="navbar">
    <div class="navinner">
        <a class="brand" href="index.php?page=home" aria-label="VitaCalc — Accueil">
            <img class="header-logo" src="Views/assets/img/vitacalc-logo.png" alt="VitaCalc — Your Health, Your Balance">
        </a>
        <nav class="navlinks" aria-label="Navigation principale">
            <a class="<?= $currentPage === 'home' ? 'active' : '' ?>" href="index.php?page=home">Accueil</a>
            <div class="nav-event">
                <button id="eventMenu" aria-expanded="false" aria-haspopup="true">Événement</button>
                <div class="dropdown" id="eventDropdown">
                    <a class="<?= $currentPage === 'events' ? 'active' : '' ?>" href="index.php?page=events"><span>✦</span> Events</a>
                    <a class="<?= $currentPage === 'signup' ? 'active' : '' ?>" href="index.php?page=signup"><span>＋</span> Inscription</a>
                </div>
            </div>
            <a class="userdot" href="index.php?page=admin" aria-label="Ouvrir le back-office" title="Back-office">VC</a>
        </nav>
    </div>
</header>
