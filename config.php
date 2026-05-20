<?php

define('DB_PATH', __DIR__ . '/data/merits.db');

// ─── Načtení lokální konfigurace (config.local.php – není v gitu) ─────────────
$_localCfg = [];
if (file_exists(__DIR__ . '/config.local.php')) {
    $_localCfg = require __DIR__ . '/config.local.php';
}

// ─── Microsoft SSO Hub integrace ─────────────────────────────────────────────
define('SSO_HUB_URL',    $_localCfg['sso_hub_url']    ?? 'https://www.skolavdf.cz/hub/auth/');
define('SSO_JWT_SECRET', $_localCfg['sso_jwt_secret'] ?? '');

// ─── Povolené e-maily učitelů pro admin rozhraní ─────────────────────────────
$ALLOWED_TEACHERS = $_localCfg['allowed_teachers'] ?? [
    'michal.bubilek@skolavdf.cz',
    'petr.kozak@skolavdf.cz',
    'jan.skoda@skolavdf.cz',
];

unset($_localCfg);

$CATEGORIES = [
    'DEV' => [
        'title' => 'Software & Web Development',
        'base'  => '#1E3A8A',
        'light' => '#3B82F6',
        'desc'  => 'Algoritmizace, čistý kód, frontend, backend a softwarová architektura.',
        'levels' => [
            1 => ['name' => 'Nováček',   'req' => 'Napíše funkční skript s podmínkami a cykly / nakóduje statický responzivní web (HTML/CSS).'],
            2 => ['name' => 'Pokročilý', 'req' => 'Používá funkce/objekty, verzuje kód v Gitu, zvládá základy moderního frameworku (React/Vue/Svelte).'],
            3 => ['name' => 'Profík',    'req' => 'Vytvoří plně funkční full-stack aplikaci s databází, ošetřením chyb a nasazením na server.'],
            4 => ['name' => 'Mistr',     'req' => 'Navrhne komplexní architekturu projektu, píše unit testy, zvládne nový programovací jazyk nad rámec výuky.'],
            5 => ['name' => 'Mentor',    'req' => 'Provádí code-review spolužákům, vede vývojářský tým v ročníkovém projektu, doučuje základy nižší ročníky.'],
        ],
    ],
    'SEC' => [
        'title' => 'Cyber Security',
        'base'  => '#7F1D1D',
        'light' => '#EF4444',
        'desc'  => 'Defenzivní i ofenzivní bezpečnost, kryptografie, etický hacking a bezpečný vývoj.',
        'levels' => [
            1 => ['name' => 'Nováček',   'req' => 'Správně nastaví silné ověřování (2FA/MFA), prokáže hluboké porozumění phishingu a sociálnímu inženýrství.'],
            2 => ['name' => 'Pokročilý', 'req' => 'Úspěšně vyřeší základní Capture The Flag (CTF) výzvy, dokáže analyzovat síťový provoz na přítomnost hrozeb.'],
            3 => ['name' => 'Profík',    'req' => 'Provede bezpečný audit webu/kódu podle standardu OWASP Top 10, dokáže úspěšně detekovat a popsat malware v sandboxu.'],
            4 => ['name' => 'Mistr',     'req' => 'Zodpovědně nahlásí reálnou zranitelnost ve školní či jiné síti (Bug Bounty) / získá průmyslový certifikát (např. CompTIA Security+, CEH).'],
            5 => ['name' => 'Mentor',    'req' => 'Spoluorganizuje školní CTF soutěž pro ostatní, spravuje bezpečné laboratorní prostředí pro praktická cvičení třídy.'],
        ],
    ],
    'NET' => [
        'title' => 'Networking & Infrastructure',
        'base'  => '#14532D',
        'light' => '#22C55E',
        'desc'  => 'Síťové protokoly, topologie, aktivní prvky, diagnostika a správa síťového HW.',
        'levels' => [
            1 => ['name' => 'Nováček',   'req' => 'Rozumí IP adresaci (IPv4/IPv6), správně vyrobí (nakrimpuje) síťový kabel a zapojí domácí router.'],
            2 => ['name' => 'Pokročilý', 'req' => 'Konfiguruje VLANy a základní směrování (routing) na Cisco/MikroTik zařízeních v simulátoru (Packet Tracer).'],
            3 => ['name' => 'Profík',    'req' => 'Navrhne, zdokumentuje a fyzicky zapojí funkční topologii s redundancí a zabezpečením v reálné laboratoři.'],
            4 => ['name' => 'Mistr',     'req' => 'Úspěšně diagnostikuje a opraví komplexní výpadek sítě, konfiguruje dynamické směrování (OSPF) / získá certifikaci Cisco CCNA.'],
            5 => ['name' => 'Mentor',    'req' => 'Spravuje a dokumentuje školní síťovou laboratoř, pomáhá učiteli s přípravou praktických zadání pro mladší ročníky.'],
        ],
    ],
    'HW' => [
        'title' => 'Hardware & IoT',
        'base'  => '#1C1C24',
        'light' => '#94A3B8',
        'desc'  => 'Stavba PC, diagnostika závad, mikrokoncepty, robotika a vestavěné systémy.',
        'levels' => [
            1 => ['name' => 'Nováček',   'req' => 'Samostatně sestaví počítač z komponent, nainstaluje operační systém a nakonfiguruje BIOS/UEFI.'],
            2 => ['name' => 'Pokročilý', 'req' => 'Naprogramuje jednoúčelové Arduino/ESP32 (např. čtení z čidel a zápis na displej).'],
            3 => ['name' => 'Profík',    'req' => 'Navrhne jednoduchý plošný spoj (PCB) v CAD programu, bezpečně spájí komponenty a oživí IoT zařízení.'],
            4 => ['name' => 'Mistr',     'req' => 'Provede pokročilou opravu na úrovni desky (multimetr, pájení), realizuje komplexní IoT projekt s odesíláním dat do cloudu přes MQTT/HTTP.'],
            5 => ['name' => 'Mentor',    'req' => 'Působí jako správce školní dílny/3D tiskáren, pomáhá spolužákům s hardwarovou částí jejich maturitních a ročníkových prací.'],
        ],
    ],
    'AID' => [
        'title' => 'Artificial Intelligence & Data',
        'base'  => '#BE185D',
        'light' => '#EC4899',
        'desc'  => 'Efektivní integrace AI, prompt engineering, analýza dat, databáze a lokální nasazování modelů.',
        'levels' => [
            1 => ['name' => 'Nováček',   'req' => 'Efektivně využívá LLM jako asistenta pro zrychlení práce a učení (pokročilý prompt engineering), prokáže kritické myšlení vůči halucinacím AI.'],
            2 => ['name' => 'Pokročilý', 'req' => 'Zpracuje a vizualizuje komplexní surový dataset (SQL, Python/Pandas), vytvoří z něj čistý interaktivní dashboard (PowerBI/Streamlit).'],
            3 => ['name' => 'Profík',    'req' => 'Inovativně integruje AI do vlastního projektu (např. přes API OpenAI/Anthropic ve své webové či mobilní aplikaci).'],
            4 => ['name' => 'Mistr',     'req' => 'Dokáže lokálně nasadit open-source model (např. přes Ollama), úspěšně provede fine-tuning (dotrénování) nebo RAG (vektorovou databázi) nad specifickými daty.'],
            5 => ['name' => 'Mentor',    'req' => 'Pomáhá učitelům nebo spolužákům s efektivním a etickým nasazením AI do výuky, vede datové analýzy pro velké školní projekty.'],
        ],
    ],
    'OPS' => [
        'title' => 'DevOps & Cloud',
        'base'  => '#0F766E',
        'light' => '#14B8A6',
        'desc'  => 'Linux, virtualizace, kontejnerizace, automatizace vývoje (CI/CD) a správa serverů.',
        'levels' => [
            1 => ['name' => 'Nováček',   'req' => 'Ovládá základní práci v Linux terminálu (CLI), dokáže se bezpečně připojit přes SSH a spravovat soubory.'],
            2 => ['name' => 'Pokročilý', 'req' => 'Spustí a správně zprovozní webovou aplikaci v Docker kontejneru, rozumí principům izolace procesů.'],
            3 => ['name' => 'Profík',    'req' => 'Spravuje vlastní Linux server (webserver, databáze), nastaví automatické šifrované zálohování a bezpečný firewall.'],
            4 => ['name' => 'Mistr',     'req' => 'Vytvoří funkční CI/CD pipeline (GitHub Actions/GitLab CI) s automatickým nasazením na cloud/VPS / získá certifikaci (např. AWS SysOps, Azure Administrator).'],
            5 => ['name' => 'Mentor',    'req' => 'Spravuje sdílenou serverovou infrastrukturu pro projekty spolužáků, pomáhá s deploymentem ostatním maturitním týmům.'],
        ],
    ],
    'DSN' => [
        'title' => 'UI/UX & Design',
        'base'  => '#581C87',
        'light' => '#A855F7',
        'desc'  => 'Grafický design, wireframy, uživatelské testování, vizuální identita a prezentace.',
        'levels' => [
            1 => ['name' => 'Nováček',   'req' => 'Vytvoří čistou, typograficky správnou prezentaci projektu podle základních kompozičních pravidel.'],
            2 => ['name' => 'Pokročilý', 'req' => 'Navrhne vektorové logo a základní identitu značky (brand book), bezpečně ovládá nástroje jako Figma nebo Adobe Creative Cloud.'],
            3 => ['name' => 'Profík',    'req' => 'Vytvoří kompletní interaktivní UI/UX prototyp mobilní nebo webové aplikace včetně doloženého uživatelského testování.'],
            4 => ['name' => 'Mistr',     'req' => 'Kompletně zastřeší vizuální a frontendovou identitu velkého školního projektu, vytvoří profesionální animace, video nebo promo grafiku.'],
            5 => ['name' => 'Mentor',    'req' => 'Garantuje "design code" u týmových projektů, pomáhá ostatním technicky zaměřeným žákům vizuálně a marketingově prodat jejich řešení.'],
        ],
    ],
    'AWD' => [
        'title' => 'Special & External Awards',
        'base'  => '#B45309',
        'light' => '#F59E0B',
        'desc'  => 'Reprezentace školy na externích akcích, úspěchy v odborných soutěžích, účast na hackathonech a mimořádné zásluhy o rozvoj školní komunity.',
        'levels' => [
            1 => ['name' => 'Regionální reprezentant', 'req' => 'Úspěšná účast (v horní polovině listiny) v okresním kole odborné soutěže (SOČ, KyberSoutěž, programování) / aktivní účast na lokálním hackathonu / pomoc na Dnech otevřených dveří.'],
            2 => ['name' => 'Krajský finalista',       'req' => 'Postup a úspěšná účast v krajském kole odborné soutěže / prezentace vlastní práce na regionální přehlídce / odevzdání funkčního prototypu na meziškolním hackathonu v limitu.'],
            3 => ['name' => 'Národní úroveň',          'req' => 'Postup do celostátního finále uznávané soutěže / 1.–3. místo v krajském kole SOČ či KyberSoutěže / naprogramování interního systému, který škola reálně nasadí do ostrého provozu.'],
            4 => ['name' => 'Národní elita',           'req' => 'Umístění na stupních vítězů (1.–3. místo) v celostátním finále / nominace do mezinárodního kola IT soutěže / absolutní vítězství na prestižním hackathonu v konkurenci dospělých/VŠ týmů.'],
            5 => ['name' => 'Školní legenda',          'req' => 'Absolutní vítězství v celostátním finále nebo medaile z mezinárodního kola / dlouhodobý úspěšný koučink mladších žáků, které prokazatelně dovede k zisku stužek AWD L1–L3.'],
        ],
    ],
];

function getDb(): PDO {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA journal_mode=WAL;');
    $db->exec('PRAGMA foreign_keys=ON;');
    return $db;
}

function initDb(): void {
    $db = getDb();
    $db->exec("
        CREATE TABLE IF NOT EXISTS students (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            lastname       TEXT    NOT NULL,
            firstname      TEXT    NOT NULL,
            admission_year INTEGER NOT NULL,
            is_public      INTEGER NOT NULL DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS merits (
            id          INTEGER  PRIMARY KEY AUTOINCREMENT,
            student_id  INTEGER  NOT NULL REFERENCES students(id) ON DELETE CASCADE,
            category    TEXT     NOT NULL,
            level       INTEGER  NOT NULL CHECK(level BETWEEN 1 AND 5),
            granted_by  TEXT     NOT NULL,
            granted_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            description TEXT,
            UNIQUE(student_id, category, level)
        );
        CREATE INDEX IF NOT EXISTS idx_merits_student ON merits(student_id);
        CREATE INDEX IF NOT EXISTS idx_merits_cat_lvl ON merits(category, level);
    ");

    // Seed demo data pokud je DB prázdná
    $count = $db->query('SELECT COUNT(*) FROM students')->fetchColumn();
    if ((int)$count === 0) {
        seedDemoData($db);
    }
}

function seedDemoData(PDO $db): void {
    $students = [
        ['Novák',    'Jan',     2023, 1],
        ['Svobodová','Tereza',  2023, 1],
        ['Kovář',    'Ondřej',  2024, 1],
        ['Procházka','Lucie',   2024, 0],
        ['Horáček',  'Martin',  2023, 1],
        ['Dvořák',   'Kateřina',2022, 1],
        ['Blažek',   'Tomáš',   2024, 0],
        ['Krejčí',   'Veronika',2023, 1],
        ['Marek',    'Filip',   2025, 1],
        ['Pokorná',  'Alžběta', 2025, 0],
    ];
    $stmt = $db->prepare('INSERT INTO students (lastname, firstname, admission_year, is_public) VALUES (?,?,?,?)');
    foreach ($students as $s) {
        $stmt->execute($s);
    }

    $merits = [
        [1, 'DEV', 3, 'Učitel Novák', '2025-09-10', 'Full-stack web s autentizací a nasazením na VPS.'],
        [1, 'OPS', 2, 'Učitel Novák', '2025-10-02', 'Docker compose pro dev prostředí.'],
        [1, 'SEC', 1, 'Učitelka Černá', '2025-11-15', 'MFA a phishing awareness test.'],
        [2, 'DSN', 4, 'Učitelka Malá', '2025-09-20', 'Komplexní vizuální identita školního projektu SilkRoute.'],
        [2, 'DEV', 2, 'Učitel Novák', '2025-10-05', 'React aplikace s API integrací.'],
        [2, 'AID', 3, 'Učitel Novák', '2026-01-12', 'Vlastní chatbot přes OpenAI API ve webové aplikaci.'],
        [3, 'NET', 2, 'Učitel Veselý', '2025-11-08', 'VLAN konfigurace v Packet Tracer.'],
        [3, 'HW',  1, 'Učitel Veselý', '2025-09-15', 'Sestavení PC a instalace OS.'],
        [4, 'SEC', 3, 'Učitelka Černá', '2025-12-01', 'OWASP audit školní aplikace.'],
        [4, 'SEC', 2, 'Učitelka Černá', '2025-10-20', 'CTF základní výzvy – 3/3 splněno.'],
        [5, 'DEV', 5, 'Učitel Novák', '2026-02-14', 'Code-review, vedení týmu u maturitního projektu.'],
        [5, 'OPS', 4, 'Učitel Novák', '2026-03-01', 'CI/CD pipeline pro 3 týmové projekty.'],
        [5, 'AID', 2, 'Učitel Novák', '2025-11-20', 'Pandas dashboard ze školních dat.'],
        [6, 'DEV', 4, 'Učitel Novák', '2024-06-10', 'Architektura a unit testy pro školní systém.'],
        [6, 'SEC', 4, 'Učitelka Černá', '2024-05-22', 'Bug bounty – nahlášení XSS ve školním portálu.'],
        [6, 'NET', 3, 'Učitel Veselý', '2024-04-18', 'Navržení a zapojení školní laboratoře.'],
        [6, 'AWD', 3, 'Učitelka Ředitelka', '2024-06-01', 'Postup do celostátního finále SOČ.'],
        [7, 'HW',  3, 'Učitel Veselý', '2026-01-30', 'PCB design a IoT čidlo teploty.'],
        [7, 'AID', 1, 'Učitel Novák', '2025-10-15', 'Prompt engineering workshop výstup.'],
        [8, 'DSN', 3, 'Učitelka Malá', '2025-12-10', 'UI/UX prototyp mobilní aplikace s testováním.'],
        [8, 'DEV', 2, 'Učitel Novák', '2025-11-01', 'Vue.js portfolio web.'],
        [9, 'OPS', 1, 'Učitel Novák', '2026-03-15', 'SSH a základy Linux CLI.'],
        [9, 'DEV', 1, 'Učitel Novák', '2026-02-20', 'Responzivní statický web.'],
        [10,'AID', 3, 'Učitel Novák', '2026-04-01', 'RAG nad školními dokumenty přes Ollama.'],
        [10,'DEV', 2, 'Učitel Novák', '2026-03-10', 'Svelte komponentová knihovna.'],
    ];
    $stmt = $db->prepare('INSERT OR IGNORE INTO merits (student_id, category, level, granted_by, granted_at, description) VALUES (?,?,?,?,?,?)');
    foreach ($merits as $m) {
        $stmt->execute($m);
    }
}

function anonymousId(int $studentId, int $admissionYear): string {
    $hash = strtoupper(substr(hash('sha256', $studentId . 'sr_salt_2026'), 0, 4));
    $yy = substr((string)$admissionYear, 2, 2);
    return "#{$yy}{$hash}";
}
