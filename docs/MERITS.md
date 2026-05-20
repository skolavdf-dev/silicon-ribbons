# METADATA & MERIT SYSTÉM: GAMIFIKACE IT OBORŮ

> **Interní dokumentace motivačního systému pro žáky střední školy**  
> **Verze:** 1.1 (2026) | **Status:** Připraveno k nasazení

---

## 1. Úvod a Filozofie systému

Merit systém je nástroj **gamifikace vzdělávání**, který transformuje tradiční hodnocení na cestu osobního rozvoje. Místo trestání za chyby systém oceňuje proaktivitu, inovaci, samostatnost a ochotu pomáhat ostatním.

Žáci získávají digitální i fyzické **Ribbons (stužky/odznaky)**, které reprezentují jejich reálné dovednosti v klíčových IT disciplínách.

### Hlavní cíle systému:
*   **Motivace k rozvoji:** Podnítit zájem o studium nad rámec běžných školních povinností.
*   **Transparentnost:** Žák přesně ví, jaké praktické dovednosti musí prokázat pro dosažení vyšší úrovně.
*   **Peer-learning:** Zapojit nejlepší studenty do chodu školy skrze roli Mentora, který pomáhá mladším spolužákům.

---

## 2. Architektura Úrovní (Progression System)

Systém striktně rozlišuje **5 úrovní růstu** (L1 až L5) v každé z kategorií. Úrovně se neodvíjí od věku nebo ročníku, ale od prokazatelných kompetencí. 

Všechny vizuální prvky a symboly na stužkách jsou v **čistě bílé barvě (`#FFFFFF`)** na barevném podkladu dané kategorie:

| Úroveň | Název | Vizuální symbolika (Device System) | Popis a význam úrovně |
| :--- | :--- | :--- | :--- |
| **L1** | **Nováček** | *Žádný symbol* (pouze zkratka a textura) | Žák úspěšně vstoupil do problematiky a zvládl základy. |
| **L2** | **Pokročilý** | **●** (1 centrální kroužek) | Žák se v oblasti orientuje a samostatně řeší standardní úlohy. |
| **L3** | **Profík** | **● ●** (2 horizontální kroužky) | Žák vykazuje nadprůměrné výsledky a zvládá komplexnější projekty. |
| **L4** | **Mistr** | **● ● ●** (3 horizontální kroužky) | Žák dosáhl vrcholu středoškolského maxima, tvoří inovativní řešení či drží certifikaci. |
| **L5** | **Mentor** | **★** (1 centrální hvězda) | Žák své znalosti předává dál a aktivně táhne školní komunitu nahoru. |

---

## 3. Katalog kategorií a barevné schéma

Každá specializace má dedikovaný barevný kód (Tailwind CSS paleta) pro okamžitou vizuální identifikaci zaměření žáka. Průmyslové certifikace jsou integrovány přímo do odpovídajících odborností.

### DEV – Software & Web Development
* **Barva:** `#1E3A8A` (Deep Blue)
* **Náplň:** Algoritmizace, čistý kód, frontend, backend a softwarová architektura.
  * **L1 (Nováček):** Napíše funkční skript s podmínkami a cykly / nakóduje statický responzivní web (HTML/CSS).
  * **L2 (Pokročilý):** Používá funkce/objekty, verzuje kód v Gitu, zvládá základy moderního frameworku (React/Vue/Svelte).
  * **L3 (Profík):** Vytvoří plně funkční full-stack aplikaci s databází, ošetřením chyb a nasazením na server.
  * **L4 (Mistr):** Navrhne komplexní architekturu projektu, píše unit testy, zvládne nový programovací jazyk nad rámec výuky.
  * **L5 (Mentor):** Provádí code-review spolužákům, vede vývojářský tým v ročníkovém projektu, doučuje základy nižší ročníky.

### SEC – Cyber Security
* **Barva:** `#7F1D1D` (Deep Red)
* **Náplň:** Defenzivní i ofenzivní bezpečnost, kryptografie, etický hacking a bezpečný vývoj.
  * **L1 (Nováček):** Správně nastaví silné ověřování (2FA/MFA), prokáže hluboké porozumění phishingu a sociálnímu inženýrství.
  * **L2 (Pokročilý):** Úspěšně vyřeší základní Capture The Flag (CTF) výzvy, dokáže analyzovat síťový provoz na přítomnost hrozeb.
  * **L3 (Profík):** Provede bezpečný audit webu/kódu podle standardu OWASP Top 10, dokáže úspěšně detekovat a popsat malware v sandboxu.
  * **L4 (Mistr):** Zodpovědně nahlásí reálnou zranitelnost ve školní či jiné síti (Bug Bounty) / získá průmyslový certifikát (např. **CompTIA Security+**, **CEH**).
  * **L5 (Mentor):** Spoluorganizuje školní CTF soutěž pro ostatní, spravuje bezpečné laboratorní prostředí pro praktická cvičení třídy.

### NET – Networking & Infrastructure
* **Barva:** `#14532D` (Deep Green)
* **Náplň:** Síťové protokoly, topologie, aktivní prvky, diagnostika a správa síťového HW.
  * **L1 (Nováček):** Rozumí IP adresaci (IPv4/IPv6), správně vyrobí (nakrimpuje) síťový kabel a zapojí domácí router.
  * **L2 (Pokročilý):** Konfiguruje VLANy a základní směrování (routing) na Cisco/MikroTik zařízeních v simulátoru (Packet Tracer).
  * **L3 (Profík):** Navrhne, zdokumentuje a fyzicky zapojí funkční topologii s redundancí a zabezpečením v reálné laboratoři.
  * **L4 (Mistr):** Úspěšně diagnostikuje a opraví komplexní výpadek sítě, konfiguruje dynamické směrování (OSPF) / získá certifikaci **Cisco CCNA**.
  * **L5 (Mentor):** Spravuje a dokumentuje školní síťovou laboratoř, pomáhá učiteli s přípravou praktických zadání pro mladší ročníky.

### HW – Hardware & IoT
* **Barva:** `#1C1C24` (Deep Zinc)
* **Náplň:** Stavba PC, diagnostika závad, mikrokoncepty, robotika a vestavěné systémy.
  * **L1 (Nováček):** Samostatně sestaví počítač z komponent, nainstaluje operační systém a nakonfiguruje BIOS/UEFI.
  * **L2 (Pokročilý):** Naprogramuje jednoúčelové Arduino/ESP32 (např. čtení z čidel a zápis na displej).
  * **L3 (Profík):** Navrhne jednoduchý plošný spoj (PCB) v CAD programu, bezpečně spájí komponenty a oživí IoT zařízení.
  * **L4 (Mistr):** Provede pokročilou opravu na úrovni desky (multimetr, pájení), realizuje komplexní IoT projekt s odesíláním dat do cloudu přes MQTT/HTTP.
  * **L5 (Mentor):** Působí jako správce školní dílny/3D tiskáren, pomáhá spolužákům s hardwarovou částí jejich maturitních a ročníkových prací.

### AID – Artificial Intelligence & Data
* **Barva:** `#BE185D` (Deep Fuchsia)
* **Náplň:** Efektivní integrace AI, prompt engineering, analýza dat, databáze a lokální nasazování modelů.
  * **L1 (Nováček):** Efektivně využívá LLM jako asistenta pro zrychlení práce a učení (pokročilý prompt engineering), prokáže kritické myšlení vůči halucinacím AI.
  * **L2 (Pokročilý):** Zpracuje a vizualizuje komplexní surový dataset (SQL, Python/Pandas), vytvoří z něj čistý interaktivní dashboard (PowerBI/Streamlit).
  * **L3 (Profík):** Inovativně integruje AI do vlastního projektu (např. přes API OpenAI/Anthropic ve své webové či mobilní aplikaci).
  * **L4 (Mistr):** Dokáže lokálně nasadit open-source model (např. přes Ollama), úspěšně provede fine-tuning (dotrénování) nebo RAG (vektorovou databázi) nad specifickými daty.
  * **L5 (Mentor):** Pomáhá učitelům nebo spolužákům s efektivním a etickým nasazením AI do výuky, vede datové analýzy pro velké školní projekty.

### OPS – DevOps & Cloud
* **Barva:** `#0F766E` (Deep Teal)
* **Náplň:** Linux, virtualizace, kontejnerizace, automatizace vývoje (CI/CD) a správa serverů.
  * **L1 (Nováček):** Ovládá základní práci v Linux terminálu (CLI), dokáže se bezpečně připojit přes SSH a spravovat soubory.
  * **L2 (Pokročilý):** Spustí a správně zprovozní webovou aplikaci v Docker kontejneru, rozumí principům izolace procesů.
  * **L3 (Profík):** Spravuje vlastní Linux server (webserver, databáze), nastaví automatické šifrované zálohování a bezpečný firewall.
  * **L4 (Mistr):** Vytvoří funkční CI/CD pipeline (GitHub Actions/GitLab CI) s automatickým nasazením na cloud/VPS / získá certifikaci (např. **AWS SysOps**, **Azure Administrator**).
  * **L5 (Mentor):** Spravuje sdílenou serverovou infrastrukturu pro projekty spolužáků, pomáhá s deploymentem ostatním maturitním týmům.

### DSN – UI/UX & Design
* **Barva:** `#581C87` (Deep Purple)
* **Náplň:** Grafický design, wireframy, uživatelské testování, vizuální identita a prezentace.
  * **L1 (Nováček):** Vytvoří čistou, typograficky správnou prezentaci projektu podle základních kompozičních pravidel.
  * **L2 (Pokročilý):** Navrhne vektorové logo a základní identitu značky (brand book), bezpečně ovládá nástroje jako Figma nebo Adobe Creative Cloud.
  * **L3 (Profík):** Vytvoří kompletní interaktivní UI/UX prototyp mobilní nebo webové aplikace včetně doloženého uživatelského testování.
  * **L4 (Mistr):** Kompletně zastřeší vizuální a frontendovou identitu velkého školního projektu, vytvoří profesionální animace, video nebo promo grafiku.
  * **L5 (Mentor):** Garantuje "design code" u týmových projektů, pomáhá ostatním technicky zaměřeným žákům vizuálně a marketingově prodat jejich řešení.

### AWD – Special & External Awards
* **Barva:** `#B45309` (Deep Gold)
* **Náplň:** Reprezentace školy na externích akcích, úspěchy v odborných soutěžích, účast na hackathonech a mimořádné zásluhy o rozvoj školní komunity.
  * **L1 (Nováček) – Regionální reprezentant:** Úspěšná účast (v horní polovině listiny) v okresním kole odborné soutěže (SOČ, KyberSoutěž, programování) / aktivní účast na lokálním hackathonu / pomoc na Dnech otevřených dveří.
  * **L2 (Pokročilý) – Krajský finalista:** Postup a úspěšná účast v krajském kole odborné soutěže / prezentace vlastní práce na regionální přehlídce / odevzdání funkčního prototypu na meziškolním hackathonu v limitu.
  * **L3 (Profík) – Národní úroveň:** Postup do celostátního finále uznávané soutěže / 1.–3. místo v krajském kole SOČ či KyberSoutěže / naprogramování interního systému, který škola reálně nasadí do ostrého provozu.
  * **L4 (Mistr) – Národní elita:** Umístění na stupních vítězů (1.–3. místo) v celostátním finále / nominace do mezinárodního kola IT soutěže / absolutní vítězství na prestižním hackathonu v konkurenci dospělých/VŠ týmů.
  * **L5 (Mentor) – Školní legenda:** Absolutní vítězství v celostátním finále nebo medaile z mezinárodního kola / dlouhodobý úspěšný koučink mladších žáků, které prokazatelně dovede k zisku stužek AWD L1–L3.

---

## 4. Pravidla schvalování a udělování (Governance)

Aby si systém udržel vysokou prestiž a motivační hodnotu, podléhá proces udělování jasným pravidlům:

1.  **Iniciace:** O udělení ribbonu může zažádat sám žák doložením splněných kritérií (odkaz na GitHub, funkční prototyp, certifikát), nebo jej na základě excelentních výsledků navrhne vyučující.
2.  **Schválení:** Úrovně **L1** a **L2** schvaluje garant daného předmětu. Úrovně **L3** až **L5** podléhají schválení oborové komise IT (vzhledem k provázanosti na akademické výhody).
3.  **Zpětná vazba:** Pokud žák kritéria nesplní, schvalující učitel mu určí přesný backlog (co konkrétně chybí, co je třeba doučit či předělat), aby úrovně úspěšně dosáhl.

---

## 5. Propojení s hodnocením (Gamification Perks)

Získávání stužek má přímý, hmatatelný dopad na školní život žáka a nahrazuje některé rutinní formy zkoušení:

> **Dosažení úrovně L3 (Profík):** Automatická známka 1 (Výborný) do hlavního odborného předmětu a možnost prominutí jedné rutinní teoretické prověrky.
> 
> **Dosažení úrovně L4 (Mistr):** Možnost individuálního plánu v daném předmětu – místo docházky na běžné teoretické hodiny žák samostatně pracuje na pokročilém projektu.
> 
> **Dosažení úrovně L5 (Mentor):** Žák získává oficiální status asistenta výuky, zápis do doporučení pro vysoké školy / zaměstnavatele a přednostní právo na výběr nejlepších partnerských firem pro povinné školní praxe.