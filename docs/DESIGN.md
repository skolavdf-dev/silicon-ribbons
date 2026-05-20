# DESIGN.md: Vizuální Identita Silicon Ribbons

Tento dokument detailně popisuje kompletní grafický a vizuální design systému Silicon Ribbons, vytvořený tak, aby bylo možné rozhraní i samotné stužky jednoduše replikovat do jakéhokoliv dalšího systému, prezentace nebo tisku.

---

## 1. Barevná paleta (Design Tokens)

Web i celý systém je postaven na hlubokém, elegantním "dark mode" tématu inspirovaném prémiovým uživatelským rozhraním. Využívá poloprůhledné (glassmorphism) panely s lehkým rozostřením (backdrop-filter).

### Téma a pozadí webu
- **Pozadí horní část (Gradient Top):** `#003b4b`
- **Pozadí spodní část (Gradient Bottom):** `#006783`
- *Poznámka:* Web má vertikální lineární gradient zespodu nahoru `linear-gradient(0deg, #006783, #003b4b)`.
- **Panely (Karty):** `rgba(0, 27, 34, 0.45)` s `backdrop-filter: blur(12px)`
- **Jemné ohraničení (Border):** `rgba(255, 255, 255, 0.12)`

### Typografie a texty
- **Základní text:** `#ffffff` (Bílá)
- **Ztlumený text (Muted):** `rgba(255, 255, 255, 0.5)`
- **Zvýraznění (Accent):** `#41d7ff` (Shodné s pravou horní částí loga)

### Barvy instituce (Logo)
Základní vizuál je odvozen od složeného barevného loga:
- **Levá horní (Žlutá 1):** `#ffd400`
- **Levá spodní (Žlutá 2):** `#ffea80`
- **Pravá horní (Modrá 1):** `#41d7ff`
- **Pravá spodní (Modrá 2):** `#00c0f3`
- **Spodní levá (Zelená 1):** `#c2dd78`
- **Spodní pravá (Zelená 2):** `#a6ce39`

### Kategorie Ribbons (Gradientní barvy)
Každá odborná kategorie má přiděleny dvě barvy, tvořící lineární gradient shora dolů (Light -> Base).
- **DEV** (Software & Web): Light `#3B82F6` -> Base `#1E3A8A`
- **SEC** (Cyber Security): Light `#EF4444` -> Base `#7F1D1D`
- **NET** (Infrastructure): Light `#22C55E` -> Base `#14532D`
- **HW** (Hardware & IoT): Light `#94A3B8` -> Base `#1C1C24`
- **AID** (AI & Data): Light `#EC4899` -> Base `#BE185D`
- **OPS** (DevOps & Cloud): Light `#14B8A6` -> Base `#0F766E`
- **DSN** (UI/UX Design): Light `#A855F7` -> Base `#581C87`
- **AWD** (Special Awards): Light `#F59E0B` -> Base `#B45309`

---

## 2. Písmo (Typografie)

Celý systém spoléhá na jeden moderní bezpatkový font **Raleway**.

- **Zdroj:** Google Fonts
- **Řezy (Weights):** 200, 400 (Základní), 600 (Nadpisy), 700 (Zkratky ve stužkách)
- **Import:**
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,200;0,400;0,600;1,400&display=swap" rel="stylesheet">
```

---

## 3. Logo instituce (SVG Zdroj)

Níže je kompletní SVG kód loga školy, zkonstruovaný pomocí vektorových polygonů a zbarvený definovanými design tokeny. Obalující `<svg>` by měl mít definované zobrazení (např. přes viewBox).

```html
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 866" width="100%" height="100%">
    <!-- Levá strana -->
    <polygon points="0 0 500 0 500 188 326 188 0 0" fill="#ffd400"></polygon>
    <polygon points="0 0 326 188 413 339 250 433 0 0" fill="#ffea80"></polygon>
    <!-- Pravá strana -->
    <polygon points="500 0 1000 0 674 188 500 188 500 0" fill="#41d7ff"></polygon>
    <polygon points="674 188 1000 0 750 433 587 339 674 188" fill="#00c0f3"></polygon>
    <!-- Spodní strana -->
    <polygon points="250 433 413 339 500 490 500 866 250 433" fill="#c2dd78"></polygon>
    <polygon points="500 490 587 339 750 433 500 866 500 490" fill="#a6ce39"></polygon>
</svg>
```

---

## 4. Design Stužek (Ribbons)

Stužky (Ribbons) jsou ústředním vizuálním prvkem celého hodnocení. Mají přesný formát inspirovaný vojenskými stužkami. Kreslí se nativně v SVG.

### Geometrie a proporce
- **Rozměry:** Šířka `140px`, Výška `48px`
- **Rádius rohů:** `3px`
- **Levý blok (Zkratka):** Zatemněný černým obdélníkem s `opacity="0.22"`. Šířka bloku je `52px`. V něm vycentrován textový štítek kategorie (např. "DEV").
- **Zvýraznění okraje:** Na spodní hraně je světlá linka (výška `2px`, bílá, `opacity="0.08"`) dodávající mírný 3D efekt materiálu.

### Kód jedné ukázkové stužky (Příklad: DEV L5 - Mentor)

Zde je plně replikovatelný SVG kód pro nejvyšší stužku kategorie DEV (Software & Web). Pro ostatní kategorie se mění zkratka v `<text>` a dvě barvy ve `<stop>`. Pro nižší úrovně se mění symbolika na pravé straně.

```html
<svg viewBox="0 0 140 48" xmlns="http://www.w3.org/2000/svg">
    <!-- Definice barevného přechodu pro kategorii DEV -->
    <defs>
        <linearGradient id="gradient_dev" x1="0%" y1="0%" x2="0%" y2="100%">
            <!-- Světlá barva (Light) -->
            <stop offset="0%" stop-color="#3B82F6" stop-opacity="1"/>
            <!-- Tmavá barva (Base) -->
            <stop offset="100%" stop-color="#1E3A8A" stop-opacity="1"/>
        </linearGradient>
        <clipPath id="clip_ribbon">
            <rect width="140" height="48" rx="3"/>
        </clipPath>
    </defs>
    
    <!-- Tělo stužky (Gradient) -->
    <rect width="140" height="48" rx="3" fill="url(#gradient_dev)"/>
    
    <!-- Zatemněná levá sekce -->
    <rect width="52" height="48" fill="black" opacity="0.22" clip-path="url(#clip_ribbon)"/>
    
    <!-- Textový štítek (Zkratka) -->
    <text x="26" y="25" text-anchor="middle" dominant-baseline="middle" font-family="'Raleway', sans-serif" font-size="14" font-weight="700" fill="#FFFFFF" opacity="0.95" letter-spacing="2">DEV</text>
    
    <!-- Geometrický Device System (Vizuál úrovně) - Zde je L5 Hvězda -->
    <polygon points="100,13 103.5,20 111,20 105,26 107,33 100,29 93,33 95,26 89,20 96.5,20" fill="#FFFFFF" opacity="0.95"/>
    
    <!-- Spodní 3D Highlight -->
    <rect width="140" height="2" rx="1" fill="#FFFFFF" opacity="0.08"/>
</svg>
```

### Vizuální symbolika úrovní (Device System)

Symbolika (tzv. "devices") je umístěna do pravé části stužky se středem zhruba na souřadnicích `x=100, y=24`. 
Značky jsou bílé (`#FFFFFF`) s průhledností `opacity="0.92"` (u teček) až `opacity="0.95"` (hvězda).

1. **L1 (Nováček):** Bez symbolu. Pravá část je jen čistý gradient barev.
2. **L2 (Pokročilý):** Jedna středová tečka.
   `<circle cx="100" cy="24" r="5" fill="#FFFFFF" opacity="0.92"/>`
3. **L3 (Profík):** Dvě horizontální tečky.
   `<circle cx="90" cy="24" r="5" .../>`
   `<circle cx="110" cy="24" r="5" .../>`
4. **L4 (Mistr):** Tři horizontální tečky.
   `<circle cx="80" cy="24" r="5" .../>`
   `<circle cx="100" cy="24" r="5" .../>`
   `<circle cx="120" cy="24" r="5" .../>`
5. **L5 (Mentor):** Jedna centrální hvězda (kód polygonu viz výše).
