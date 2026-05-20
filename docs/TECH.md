# TECHNICKÁ SPECIFIKACE A MANUÁL PRO VÝVOJÁŘE

**Název sub-modulu:** Školní Merit Hub

**Cílová URL:** `[https://www.skolavdf.cz/hub/merits](https://www.skolavdf.cz/hub/merits)`

**Relativní cesta od kořene:** `hub/merits/`

**Verze dokumentace:** 1.4 (2026)

---

## 1. Technologický Stack (Tech Stack)

Aplikace je navržena jako lehký, souborově orientovaný sub-modul integrovaný do stávající infrastruktury školního webu.

* **Backend:** PHP 8.x (využití nativních funkcí, objektově orientované PDO pro databázovou vrstvu, práce s JSON).
* **Databáze:** SQLite 3 (souborové úložiště umístěné v chráněném adresáři).
* **Autentizace:** Microsoft Entra ID / Azure AD (integrováno skrze stávající školní OAuth2 mechanismus, předávání identity přes bezpečné serverové `$_SESSION`).
* **Frontend:** Čisté klientské technologie (Vanilla JavaScript ES6+, HTML5, CSS3). **Zákaz externích frameworků a knihoven** (React, Vue, jQuery) kvůli zachování maximální rychlosti, nulových závislostí a minimální datové náročnosti.

---

## 2. Struktura adresářů sub-modulu

Vývojář je povinen striktně dodržet následující modulární strukturu:

```text
hub/merits/
│
├── config.php          <-- Deklarativní konfigurace (seznam e-mailů učitelů, mapování barev)
├── index.php           <-- Hlavní klientský controller (HTML kostra veřejného webu)
├── admin.php           <-- Vstupní bod a controller pro učitelskou administraci
├── api.php             <-- REST API endpoint (obsluha veškerých GET/POST datových požadavků)
│
├── css/
│   └── style.css       <-- CSS3 stylování (grid systém, modaly, komponenty stužek)
├── js/
│   ├── app.js          <-- Klientská aplikační logika (fetch API, filtry, stav aplikace, modaly)
│   └── admin.js        <-- Klientská logika specifická pro administraci (CRUD operace, live-search)
│
└── data/               <-- Datová složka chráněná serverovým pravidlem (.htaccess)
|   └── merits.db       <-- Soubor SQLite databáze
└── doc/                <-- Markdown dokumentace projektu
    ├── MERIT.md        <-- Popis funkcionality Merit Systému
    ├── TECH.md         <-- Technická dokumentace
    ├── DESIGN.md       <-- Design dokumentace
    └── DESIGN.html     <-- Demonstrace vzhledu

```

---

## 3. Návrh databázového modelu (SQLite)

Vzhledem k architektuře SQLite jsou textové řetězce navrženy pod datovým typem `TEXT`. Databáze obsahuje dvě hlavní tabulky a sadu indexů pro optimalizaci vyhledávání.

### Tabulka: `students`

Ukládá trvalé identifikační údaje žáka. Neobsahuje proměnlivé údaje (třídu).

| Sloupec | Datový typ | Klíč / Atributy | Popis |
| --- | --- | --- | --- |
| `id` | `INTEGER` | PRIMARY KEY AUTOINCREMENT | Unikátní ID záznamu. |
| `lastname` | `TEXT` | NOT NULL | Příjmení žáka. |
| `firstname` | `TEXT` | NOT NULL | Křestní jméno žáka. |
| `admission_year` | `INTEGER` | NOT NULL | Čtyřmístný rok nástupu do školy (např. `2024`). Slouží pro výpočet aktuálnosti studenta pro firmy. |
| `is_public` | `INTEGER` | DEFAULT 0 | GDPR přepínač (0 = Anonymní profil na webu, 1 = Zobrazit reálné jméno). |


#### Logika zobrazování jmen na veřejném webu (`index.php`):

* Pokud je `is_public = 1`, JavaScript vykreslí plné jméno: **Jan Novák (Nastoupil: 2024)**.
* Pokud je `is_public = 0`, JavaScript jméno skryje a vygeneruje anonymní vizitku: **IT Talent #24A1 (Nastoupil: 2024)**. *Poznámka pro vývojáře: Anonymní ID může být z bezpečnostních důvodů zahashovaný `student_id` nebo zkrácený řetězec, aby žáci nemohli podle ID odvodit, o koho jde.*

#### Logika filtrování a řazení:

* **Filtr dle ročníku (nástupu):** Místo filtru podle třídy bude na webu filtr „Rok nástupu“ (nebo přepínač: *Zobrazit pouze studenty před maturitou*).
* **Abecední řazení:** Při abecedním řazení (A-Z) se anonymní profily zařadí buď na konec seznamu, nebo se budou řadit podle svého anonymního kódu, aby se zachovala konzistence. V soukromé administraci pro učitele se samozřejmě vždy řadí abecedně podle reálného příjmení.

### Tabulka: `merits`

Historický transakční log udělených ocenění.

| Sloupec | Datový typ | Klíč / Atributy | Popis |
| --- | --- | --- | --- |
| `id` | `INTEGER` | PRIMARY KEY AUTOINCREMENT | Unikátní ID záznamu. |
| `student_id` | `INTEGER` | FOREIGN KEY | Vazba na `students.student_id`. |
| `category` | `TEXT` | NOT NULL | Zkratka kategorie (DEV, SEC, AID...). |
| `level` | `INTEGER` | NOT NULL (1 až 5) | Dosažený stupeň (Nováček až Mentor). |
| `granted_by` | `TEXT` | NOT NULL | Identifikátor učitele, který záznam zapsal. |
| `granted_at` | `DATETIME` | DEFAULT CURRENT_TIMESTAMP | Časové razítko udělení. |
| `description` | `TEXT` | NULLABLE | Konkrétní specifikace a zdůvodnění úspěchu. |

#### Databázová integrita a optimalizace:

1. **Kompozitní unikátní klíč:** Nad tabulkou `merits` je definován `UNIQUE(student_id, category, level)`. To na úrovni databáze brání duplicitnímu udělení stejného levelu v jedné kategorii témuž žákovi.
2. **Indexy:** Pro bleskové řazení a filtrování musí vývojář vytvořit indexy nad cizím klíčem `student_id`, nad kombinací `(category, level)` a nad sloupcem `class`.

---

## 4. Funkční požadavky a logika (Business Logic)

### Logika výpočtu skóre (Merit Score)

Aplikace nepoužívá statické ukládání celkového skóre. Skóre je vypočítáváno dynamicky (nebo agregovanou pod-query) na základě vzorce:

$$\text{Merit Score} = \sum (\text{level}_1 + \text{level}_2 + \dots + \text{level}_n)$$

> *Příklad:* Pokud má student stužku DEV na úrovni 3 (Profík) a SEC na úrovni 1 (Nováček), jeho celkové Merit Score je **4**.

### Chování veřejného webu (`index.php` + `app.js`)

1. **Výchozí stav (Lazy Loading):** Při prvním načtení stránky bez nastavených filtrů zobrazi systém **Top 20 žáků** s nejvyšším Merit Score.
2. **Možnosti filtrování (Asynchronní - bez reloadu stránky):**
* **Dle kategorie:** Zobrazí pouze žáky, kteří mají alespoň jeden ribbon ve zvolené kategorii.
* **Dle úrovně:** Zobrazí žáky, jejichž úroveň v dané kategorii (nebo celkové skóre, dle implementace rozhraní) je **vyšší než** zadaná hodnota ($>\text{X}$).
* **Dle jména:** Fulltextové vyhledávání v řetězci jména a příjmení (s implementovanou funkcí *debounce* s limitem 300 ms, aby se nezatěžoval server při každém stisku klávesy).


3. **Možnosti řazení:**
* **Dle skóre:** Sestupně od nejvyššího počtu bodů po nejnižší (defaultní herní žebříček).
* **Dle jména:** Abecedně podle příjmení a jména žáka.


4. **Interaktivita (Detail žáka):** Kliknutím na kartu žáka v mřížce se otevře asynchronní vyskakovací okno (Modal), které odešle požadavek na API a vykreslí kompletní časovou osu a detailní slovní hodnocení všech ribbonů, které daný žák získal.

---

## 5. Backendová logika a Bezpečnost (Security & Governance)

### Soubor `config.php`

Obsahuje deklarativní pole řetězců s povolenými e-mailovými adresami učitelů (např. `novak.jan@skolavdf.cz`), které mají oprávnění vstupovat do administrace.

### Administrační rozhraní (`admin.php` & `admin.js`)

1. **Ověření identity:** Skript nejprve zkontroluje existenci platné relace (`$_SESSION`). Podporuje vývojářský bypass s hardcodovaným přístupem i napojení na školní SSO.
2. **Ověření autorizace:** Skript porovná e-mail přihlášeného uživatele s polem povolených adres v `config.php`. Pokud shoda neexistuje, zahodí požadavek.
3. **Správa studentů a ocenění:** Plnohodnotné rozhraní napojené přes AJAX (využívající `admin.js`). Umožňuje přidávání nových žáků, jejich úpravu a smazání. Zároveň umožňuje udělování nových stužek (s našeptávačem/live search studentů) a jejich odstraňování pomocí modálních oken.

### Datový endpoint (`api.php`)

Slouží jako kompletní datový broker pro asynchronní požadavky (REST).

* **GET požadavky:** Přijímají filtry z URL, provádí sanitizaci vstupů proti SQL Injection (striktně skrze PDO prepared statements), využívají poddotazy pro výpočet agregovaného hodnocení (Merit Score) a vrací validní hlavičku `Content-Type: application/json`. Slouží pro naplnění veřejného žebříčku, katalogu a získání detailu studenta.
* **POST požadavky:** Zajišťují veškeré operace zápisu, modifikace a mazání dat (CRUD: `add_merit`, `add_student`, `update_student`, `delete_merit`, `delete_student`). Před provedením operace validují oprávnění odesílatele (učitelská session) a sanitizují uživatelské vstupy před vložením do SQLite databáze.


