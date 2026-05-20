<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silicon Ribbons — Merit Hub</title>
    <meta name="description" content="Přehled IT ocenění žáků VOŠ, SPŠ a SOŠ Varnsdorf — Silicon Ribbons Merit Systém.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,200;0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <div class="frame header-inner">
        <a class="logo" href="index.php" aria-label="Silicon Ribbons – domů">
            <div class="logo-img">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 866">
                    <polygon class="lt" points="0 0 500 0 500 188 326 188 0 0"/>
                    <polygon class="lb" points="0 0 326 188 413 339 250 433 0 0"/>
                    <polygon class="rt" points="500 0 1000 0 674 188 500 188 500 0"/>
                    <polygon class="rb" points="674 188 1000 0 750 433 587 339 674 188"/>
                    <polygon class="bl" points="250 433 413 339 500 490 500 866 250 433"/>
                    <polygon class="br" points="500 490 587 339 750 433 500 866 500 490"/>
                </svg>
            </div>
            <div class="logo-txt">VOŠ | SPŠ | SOŠ<br>Varnsdorf</div>
        </a>
        <div class="site-title">
            <h1>Silicon Ribbons</h1>
            <p class="subtitle">Merit Hub</p>
        </div>
        <a class="admin-link" href="admin.php">ADMIN</a>
    </div>
</header>

<main>
    <div class="frame">

        <!-- ═══ ŽEBŘÍČEK STUDENTŮ ═══ -->
        <section id="leaderboard" class="section">
            <p class="section-label">Žebříček studentů</p>

            <div class="filters panel">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="f-name">Hledat jméno</label>
                        <input id="f-name" type="search" placeholder="Příjmení nebo jméno…" autocomplete="off">
                    </div>
                    <div class="filter-group">
                        <label for="f-cat">Kategorie</label>
                        <select id="f-cat">
                            <option value="">Všechny</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="f-lvl">Min. úroveň</label>
                        <select id="f-lvl">
                            <option value="0">Jakákoli</option>
                            <option value="1">L1 Nováček+</option>
                            <option value="2">L2 Pokročilý+</option>
                            <option value="3">L3 Profík+</option>
                            <option value="4">L4 Mistr+</option>
                            <option value="5">L5 Mentor</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="f-year">Rok nástupu</label>
                        <select id="f-year">
                            <option value="0">Všechny</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="f-sort">Řadit dle</label>
                        <select id="f-sort">
                            <option value="score">Merit Score</option>
                            <option value="name">Příjmení A–Z</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="students-grid" class="students-grid" role="list" aria-live="polite">
                <div class="loading">Načítání…</div>
            </div>
        </section>

        <!-- ═══ KATALOG OCENĚNÍ ═══ -->
        <section id="catalog" class="section">
            <p class="section-label">Katalog kategorií a požadavky</p>
            <nav id="quick-nav" class="quick-nav" aria-label="Rychlá navigace kategoriemi"></nav>
            <div id="categories-container"></div>
        </section>

    </div>
</main>

<footer>
    <div class="frame">
        <p>Vyšší odborná škola, Střední průmyslová škola a Střední odborná škola, Varnsdorf, příspěvková organizace</p>
    </div>
</footer>

<!-- Modal pro detail studenta -->
<div id="modal-overlay" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title" hidden>
    <div class="modal-box">
        <button class="modal-close" aria-label="Zavřít">&times;</button>
        <div id="modal-content"></div>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
