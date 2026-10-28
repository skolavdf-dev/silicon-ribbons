# Silicon Ribbons — Merit Hub

**Silicon Ribbons** je webová aplikace vyvinutá pro evidenci, správu a veřejnou prezentaci odborných ocenění žáků na VOŠ, SPŠ a SOŠ Varnsdorf. Systém slouží jako katalog talentů a motivační herní prvek (gamifikace), kdy žáci získávají tzv. *stužky* (ribbons) v různých IT kategoriích za mimořádné dovednosti či úspěchy.

## Hlavní vlastnosti

* **Gamifikace a motivace:** Žáci sbírají stužky v 8 kategoriích (DEV, SEC, NET, HW, AID, OPS, DSN, AWD) s odstupňovanou úrovní L1 (Nováček) až L5 (Mentor).
* **Anonymizace dat (GDPR):** Systém podporuje přepínač pro skrytí skutečného jména studenta (nahrazuje ho anonymním kódem, např. `#24A1C3`), pokud žák neposkytne souhlas s veřejným zobrazením.
* **Žebříček (Leaderboard):** Dynamický žebříček žáků, který lze asynchronně filtrovat podle jména, kategorie, minimální dosažené úrovně nebo roku nástupu, včetně asynchronního fulltextového vyhledávání a debounce.
* **Nulové externí závislosti:** Frontend je postaven striktně na čistém HTML, CSS a Vanilla JS bez externích frameworků pro zajištění bleskového načítání a dlouhodobé udržitelnosti.

## Technologický Stack
Aplikace je navržena pro jednoduché nasazení s minimálními nároky na server:
* **Backend:** PHP 8.x (využívající `PDO` a připravené SQL dotazy)
* **Databáze:** SQLite 3 (soubor `data/merits.db`)
* **Frontend:** Vanilla JavaScript (ES6+), CSS3 (Grid, Flexbox, custom properties)

## Struktura projektu

```
merits/
├── index.php                    — Hlavní veřejný web (žebříček, katalog ocenění)
├── admin.php                    — Administrační rozhraní pro učitele (CRUD)
├── api.php                      — REST API pro asynchronní komunikaci frontend ↔ DB
├── config.php                   — Konfigurace aplikace (kategorie, allowed teachers, SSO URL)
├── config.local.example.php     — Šablona lokálního configu (zkopírovat → config.local.php)
├── assets/
│   ├── css/style.css            — Veškeré stylování (dark theme, glassmorphism)
│   └── js/
│       ├── app.js               — Sdílené utility, SVG stužky, filtrace, modály
│       └── admin.js             — CRUD operace, live search, validace (admin sekce)
├── data/
│   ├── .htaccess                — Blokuje přímý webový přístup ke složce
│   └── merits.db                — SQLite databáze (generuje se automaticky, není v gitu)
└── docs/                        — Projektová a technická dokumentace
```

## Instalace a spuštění

1. Zkopírujte projektový adresář na webový server s PHP 8.x a povoleným SQLite rozšířením.
2. Zkontrolujte oprávnění zápisu pro složku `data/` (PHP zde vytvoří a zapisuje do `merits.db`).
3. Zkopírujte `config.local.example.php` jako `config.local.php` a doplňte `allowed_teachers` (UPN učitelů). Přihlášení běží přes sdílenou session hubu ([microsoft-sso-hub](https://github.com/skolavdf-dev/microsoft-sso-hub)), žádné tajemství se v aplikaci neukládá.
4. Otevřete `index.php` v prohlížeči — databáze se automaticky inicializuje se vzorovými daty.

> **Poznámka:** Soubor `config.local.php` je v `.gitignore` a nikdy se necommituje. Obsahuje tajné klíče pro přihlašování přes Microsoft.

### Bezpečnostní nastavení (`config.local.php`)

| Klíč | Výchozí | Význam |
|---|---|---|
| `allowed_teachers` | `[]` (nikdo) | **Povinné.** UPN učitelů s přístupem do administrace (bez ohledu na velikost písmen) |
| `env` | `production` | `local` zapne výpis chyb a cookie cestu `/` (neodvozuje se z hlavičky `Host`) |
| `app_origin` | `https://www.skolavdf.cz` | Origin aplikace: callback pro hub a kontrola `Origin` u zápisů |
| `db_path` | `data/merits.db` | Databáze, nejlépe mimo DocumentRoot |
| `anon_salt` | (původní sůl) | Sůl anonymních kódů. **Nastavení změní všechny kódy žáků.** |

Konfiguraci lze umístit mimo DocumentRoot (proměnná prostředí `MERITS_CONFIG`). Přihlášení čte profil ze sdílené session hubu (cookie `skolavdf_hub_sso`, jen ke čtení); `?token=` z hubu se ignoruje a z URL odstraní. Odhlášení z administrace zničí tuto sdílenou session, tedy odhlásí i z ostatních aplikací hubu. Zápisové požadavky musí být `POST` s `Content-Type: application/json` a hlavičkou `Origin` shodnou s `app_origin`. Změny dat se zapisují do tabulky `audit_log` (kdo, co, ID — bez jmen žáků).

