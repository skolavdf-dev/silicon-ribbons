<?php
/**
 * Šablona lokální konfigurace.
 *
 * Zkopírujte tento soubor jako config.local.php a doplňte hodnoty.
 * Soubor config.local.php je v .gitignore a NIKDY se necommituje.
 */
return [
    // Přihlášení běží přes jednorázový kód z hubu a vlastní session cookie aplikace.
    // Žádné tajemství se tu nekonfiguruje.
    // 'app_session_name' => 'skolavdf_merits',
    // 'sso_session_key'  => 'user',

    // Povinné: UPN (přihlašovací jména v Entra ID) učitelů s přístupem do administrace.
    // Bez tohoto klíče nemá přístup nikdo.
    'allowed_teachers' => ['jmeno.prijmeni@skolavdf.cz'],

    // 'production' (výchozí) | 'local' (vývojový stroj: výpis chyb, cookie cesta '/')
    // 'env' => 'local',

    // Veřejný origin aplikace (výchozí https://www.skolavdf.cz)
    // 'app_origin' => 'https://www.skolavdf.cz',

    // Volitelné: databáze mimo DocumentRoot
    // 'db_path' => '/var/lib/skolavdf/merits.db',

    // Volitelné: vlastní sůl pro anonymní kódy žáků. POZOR: změní všechny kódy.
    // 'anon_salt' => 'DLOUHY_NAHODNY_RETEZEC',

    // Volitelné: přepsání URL přihlašovací stránky SSO Hubu
    // (užitečné pro lokální vývoj nebo jiné prostředí)
    // 'sso_hub_url' => 'http://localhost/hub/auth/',

];
