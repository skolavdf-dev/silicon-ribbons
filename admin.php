<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || ($_SERVER['SERVER_PORT'] == 443),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once __DIR__ . '/config.php';

initDb();

// ─── Validace JWT tokenu z microsoft-sso-hub ─────────────────────────────────
function validateSsoJwt(string $token): ?array {
    if (SSO_JWT_SECRET === '') {
        error_log('[silicon-ribbons] validateSsoJwt: SSO_JWT_SECRET není nakonfigurován — validace tokenu přeskočena');
        return null;
    }
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$header, $payload, $sig] = $parts;

    $decode = function (string $s): string {
        $b64 = str_replace(['-', '_'], ['+', '/'], $s);
        $pad = strlen($b64) % 4;
        if ($pad) $b64 .= str_repeat('=', 4 - $pad);
        return base64_decode($b64);
    };

    $expected = str_replace(['+', '/', '='], ['-', '_', ''],
        base64_encode(hash_hmac('sha256', "$header.$payload", SSO_JWT_SECRET, true))
    );
    if (!hash_equals($expected, $sig)) return null;

    $data = json_decode($decode($payload), true);
    if (!is_array($data)) return null;
    if (isset($data['exp']) && $data['exp'] < time()) return null;

    return $data['user'] ?? null;
}

$loginError = '';

// ─── Odhlášení ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// ─── Zpracování SSO callback (JWT token z microsoft-sso-hub) ─────────────────
if (isset($_GET['token']) && empty($_SESSION['teacher_email'])) {
    $userData = validateSsoJwt($_GET['token']);
    if ($userData !== null) {
        $email = $userData['mail'] ?? $userData['userPrincipalName'] ?? '';
        if (!empty($email) && in_array($email, $ALLOWED_TEACHERS, true)) {
            session_regenerate_id(true);
            $_SESSION['teacher_email'] = $email;
            $_SESSION['teacher_name']  = $userData['displayName'] ?? $email;
            header('Location: admin.php');
            exit;
        }
        $loginError = 'Váš Microsoft účet (' . htmlspecialchars($email) . ') nemá oprávnění pro přístup do administrace.';
    } else {
        $loginError = 'Přihlašovací token je neplatný nebo vypršel. Přihlaste se prosím znovu.';
    }
}

$isLoggedIn = !empty($_SESSION['teacher_email']) && in_array($_SESSION['teacher_email'], $ALLOWED_TEACHERS, true);

// ─── URL pro přesměrování na SSO Hub ─────────────────────────────────────────
$callbackUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    . ($_SERVER['SCRIPT_NAME'] ?? '/admin.php');
$ssoLoginUrl = SSO_HUB_URL . '?callback=' . urlencode($callbackUrl);

// Auto-redirect k přihlášení — přeskočit mezistránku s tlačítkem
if (!$isLoggedIn && empty($loginError)) {
    header('Location: ' . $ssoLoginUrl);
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silicon Ribbons — Administrace</title>
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
            <p class="subtitle">Administrace</p>
        </div>
        <?php if ($isLoggedIn): ?>
        <form method="post" style="margin:0">
            <input type="hidden" name="_action" value="logout">
            <button type="submit" class="btn btn-ghost">Odhlásit se</button>
        </form>
        <?php endif; ?>
    </div>
</header>

<main>
<div class="frame">

<?php if (!$isLoggedIn): ?>
<!-- ═══ LOGIN ═══ -->
<section class="section" style="max-width:400px;margin:0 auto">
    <p class="section-label">Přihlášení administrátora</p>
    <div class="panel" style="padding:2rem;text-align:center">
        <?php if (!empty($loginError)): ?>
        <div class="alert alert-error" style="text-align:left;margin-bottom:1.5rem"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>
        <p style="color:var(--muted);margin:0 0 1.5rem">
            Pro přístup do administrace se přihlaste školním Microsoft účtem.
        </p>
        <a href="<?= htmlspecialchars($ssoLoginUrl) ?>" class="btn btn-primary" style="display:inline-block;text-decoration:none;width:100%;box-sizing:border-box">
            Přihlásit se přes Microsoft
        </a>
    </div>
</section>

<?php else: ?>
<!-- ═══ ADMIN ROZHRANÍ ═══ -->

<div class="admin-grid">

    <!-- Levý sloupec: formuláře -->
    <div class="admin-forms">

        <!-- Přidat studenta -->
        <div class="panel admin-panel">
            <h2 class="panel-title">Přidat studenta</h2>
            <form id="form-add-student">
                <div class="form-row">
                    <div class="form-group">
                        <label>Příjmení</label>
                        <input type="text" name="lastname" required placeholder="Novák">
                    </div>
                    <div class="form-group">
                        <label>Jméno</label>
                        <input type="text" name="firstname" required placeholder="Jan">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Rok nástupu</label>
                        <input type="number" name="admission_year" required min="2000" max="2099" value="<?= date('Y') ?>">
                    </div>
                    <div class="form-group form-group--checkbox">
                        <label>
                            <input type="checkbox" name="is_public" value="1">
                            Veřejný profil (GDPR souhlas)
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Přidat studenta</button>
            </form>
        </div>

        <!-- Udělit ocenění -->
        <div class="panel admin-panel" id="panel-add-merit">
            <h2 class="panel-title">Udělit ocenění</h2>
            <form id="form-add-merit">
                <input type="hidden" name="student_id" id="merit-student-id">
                <div class="form-group">
                    <label>Student</label>
                    <div id="merit-student-display" class="merit-student-display">
                        <span class="merit-student-placeholder">Vyberte studenta tlačítkem&nbsp;<strong>+</strong>&nbsp;ze seznamu vpravo</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kategorie</label>
                        <select name="category" id="merit-cat-select" required>
                            <option value="">— kategorie —</option>
                            <?php foreach ($CATEGORIES as $id => $cat): ?>
                            <option value="<?= $id ?>"><?= $id ?> — <?= htmlspecialchars($cat['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Úroveň</label>
                        <select name="level" required>
                            <option value="1">L1 — Nováček</option>
                            <option value="2">L2 — Pokročilý</option>
                            <option value="3">L3 — Profík</option>
                            <option value="4">L4 — Mistr</option>
                            <option value="5">L5 — Mentor</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Udělil(a)</label>
                    <input type="text" name="granted_by" required value="<?= htmlspecialchars($_SESSION['teacher_name'] ?? $_SESSION['teacher_email']) ?>" placeholder="Jméno nebo e-mail učitele">
                </div>
                <div class="form-group">
                    <label>Zdůvodnění (volitelné)</label>
                    <textarea name="description" rows="3" placeholder="Konkrétní popis splněného výkonu…"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Udělit ocenění</button>
            </form>
        </div>

    </div>

    <!-- Pravý sloupec: seznam studentů -->
    <div class="admin-list-col">
        <div class="panel admin-panel">
            <h2 class="panel-title">Studenti a jejich ocenění</h2>
            <div class="admin-search">
                <input type="search" id="admin-search" placeholder="Hledat studenta…" autocomplete="off">
            </div>
            <div id="admin-students-list" class="admin-students-list">
                <div class="loading">Načítání…</div>
            </div>
        </div>
    </div>

</div>

<?php endif; ?>
</div>
</main>

<footer>
    <div class="frame">
        <p>Vyšší odborná škola, Střední průmyslová škola a Střední odborná škola, Varnsdorf, příspěvková organizace</p>
    </div>
</footer>

<!-- Modal pro editaci studenta -->
<div id="modal-overlay" class="modal-overlay" role="dialog" aria-modal="true" hidden>
    <div class="modal-box">
        <button class="modal-close" aria-label="Zavřít">&times;</button>
        <div id="modal-content"></div>
    </div>
</div>

<?php if ($isLoggedIn): ?>
<script>
const IS_ADMIN = true;
const CATEGORIES_DATA = <?php
    $catJs = [];
    foreach ($CATEGORIES as $id => $cat) {
        $catJs[] = [
            'id'    => $id,
            'title' => $cat['title'],
            'base'  => $cat['base'],
            'light' => $cat['light'],
            'desc'  => $cat['desc'],
            'levels'=> array_values(array_map(fn($lvl, $k) => ['level' => $k, 'name' => $lvl['name'], 'req' => $lvl['req']], $cat['levels'], array_keys($cat['levels']))),
        ];
    }
    echo json_encode($catJs, JSON_UNESCAPED_UNICODE);
?>;
</script>
<script src="assets/js/app.js"></script>
<script src="assets/js/admin.js"></script>
<?php endif; ?>
</body>
</html>
