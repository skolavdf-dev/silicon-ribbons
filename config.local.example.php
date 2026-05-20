<?php
/**
 * Šablona lokální konfigurace.
 *
 * Zkopírujte tento soubor jako config.local.php a doplňte hodnoty.
 * Soubor config.local.php je v .gitignore a NIKDY se necommituje.
 */
return [
    // Povinné: JWT secret sdílený s microsoft-sso-hub.
    // Musí být shodný s hodnotou  jwt.secret  v souboru
    // microsoft-sso-hub/src/config.local.php
    'sso_jwt_secret' => 'ZDE_VLOZIT_SDILENY_JWT_SECRET_Z_SSO_HUB',

    // Volitelné: přepsání URL přihlašovací stránky SSO Hubu
    // (užitečné pro lokální vývoj nebo jiné prostředí)
    // 'sso_hub_url' => 'http://localhost/hub/auth/',

    // Volitelné: přepsání seznamu povolených e-mailových adres učitelů
    // 'allowed_teachers' => [
    //     'ucitel@skolavdf.cz',
    // ],
];
