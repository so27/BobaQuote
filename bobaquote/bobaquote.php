<?php
/**
 * BobaQuote – Zitatbild-Generator
 *
 * Entwickler:        Sven Owsianowski
 * Entwicklerprofil:  https://bobaro.de/page.php?p=ueber-den-entwickler
 * Entwickelt für:    Bobaro – Bloggen ohne Ballast | www.bobaro.de
 * Vorschau:          https://zitat.bobaro.de
 * Jahr:              2026
 */

// ============================================================
// GALERIE — URL zu bobaro.de (nur hier anpassen)
// ============================================================
define('GALERIE_LIST_URL',  'https://bobaro.de/updates/bobaquote_img/list.php');
define('GALERIE_BILDER_URL', 'https://bobaro.de/updates/bobaquote_img/');
// ============================================================

// Proxy: Browser ruft ?proxy=list auf → PHP holt Liste von bobaro
if (isset($_GET['proxy']) && $_GET['proxy'] === 'list') {
  $data = @file_get_contents(GALERIE_LIST_URL);
  header('Content-Type: application/json');
  if ($data === false) {
    echo json_encode([]);
  } else {
    echo $data;
  }
  exit;
}

// Proxy: Browser ruft ?proxy=img&file=name.jpg auf → PHP liefert Bild von bobarox
if (isset($_GET['proxy']) && $_GET['proxy'] === 'img' && !empty($_GET['file'])) {
  $filename = basename($_GET['file']); // Sicherheit: nur Dateiname, kein Pfad
  $url = GALERIE_BILDER_URL . rawurlencode($filename);
  $data = @file_get_contents($url);
  if ($data === false) {
    http_response_code(404);
    exit;
  }
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  $mime = match ($ext) {
    'jpg', 'jpeg' => 'image/jpeg',
    'png'         => 'image/png',
    'webp'        => 'image/webp',
    'gif'         => 'image/gif',
    default       => 'application/octet-stream'
  };
  header("Content-Type: $mime");
  header("Cache-Control: max-age=86400");
  echo $data;
  exit;
}


$settingsFile = __DIR__ . '/_private/settings.json';
$primaryColor = '#1EA3F2';
$settings = [];
if (is_file($settingsFile)) {
  $data = json_decode(file_get_contents($settingsFile), true);
  if (is_array($data)) {
    $settings = $data;
    $primaryColor = $data['userColorPri'] ?? '#1EA3F2';
  }
}

// Zitate laden
$zitateDatei = __DIR__ . '/zitate.json';
$zitateJson  = '[]';
if (is_file($zitateDatei)) {
  $zitateJson = file_get_contents($zitateDatei);
}
?>
<!doctype html>
<!--
  ------------------------------------------------------------
  Projekt-Metadaten
  ------------------------------------------------------------
  Entwickler:        Sven Owsianowski
  Website:           Bobaro.de
  Entwicklerprofil:  https://wattblicker.notion.site
  Datum:             Juli 2026
  Version:           1.1
  ------------------------------------------------------------
-->
<html lang="de" data-theme="dark">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BobaQuote – Zitatbild-Generator</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/favicon.png">

  <!-- OG / Social -->
  <meta property="og:title" content="BobaQuote – Zitatbild-Generator">
  <meta property="og:description" content="Erstelle kostenlos professionelle Zitatbilder für Social Media.">
  <meta property="og:image" content="https://zitate.bobaro.de/og-image.png">
  <meta property="og:url" content="https://zitate.bobaro.de/">
  <meta property="og:type" content="website">

  <!-- Twitter/X Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="https://zitate.bobaro.de/og-image.png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    /* ── Theme-Variablen ── */
    [data-theme="dark"] {
      --app-bg: #0d1117;
      --panel-bg: rgba(22, 27, 34, .95);
      --panel-bg-soft: rgba(33, 38, 45, .8);
      --panel-border: rgba(255, 255, 255, .09);
      --text-main: #f8fafc;
      --text-muted: #9ca3af;
      --input-bg: rgba(5, 8, 12, .55);
      --input-bg-focus: rgba(5, 8, 12, .72);
      --topbar-bg: rgba(10, 14, 20, .88);
      --sidebar-bg: rgba(10, 14, 20, .72);
      --stage-bg: rgba(255, 255, 255, .025);
      --footer-bg: rgba(10, 14, 20, .62);
      --body-grad1: rgba(139, 92, 246, .18);
      --body-grad2: rgba(249, 115, 22, .12);
    }

    [data-theme="light"] {
      --app-bg: #f1f5f9;
      --panel-bg: rgba(255, 255, 255, .97);
      --panel-bg-soft: rgba(241, 245, 249, .9);
      --panel-border: rgba(0, 0, 0, .1);
      --text-main: #1e293b;
      --text-muted: #64748b;
      --input-bg: rgba(255, 255, 255, .9);
      --input-bg-focus: #ffffff;
      --topbar-bg: rgba(255, 255, 255, .92);
      --sidebar-bg: rgba(248, 250, 252, .92);
      --stage-bg: rgba(0, 0, 0, .04);
      --footer-bg: rgba(241, 245, 249, .9);
      --body-grad1: rgba(139, 92, 246, .08);
      --body-grad2: rgba(249, 115, 22, .06);
    }

    :root {
      --accent: #8b5cf6;
      --accent-2: #a855f7;
      --quote-width: 1080px;
      --quote-height: 1080px;
      --preview-scale: .55;
      --quote-bg: linear-gradient(135deg, #ff7a00, #8a3e00);
      --quote-text: #ffffff;
      --quote-author: #ffffff;
      --quote-font: Arial, Helvetica, sans-serif;
      --quote-font-size: 84px;
      --quote-line-height: 1.2;
      --quote-padding: 96px;
      --bg-brightness: 100%;
      --bg-contrast: 100%;
      --bg-blur: 0px;
      --bg-grayscale: 0%;
      --bg-sepia: 0%;
      --bg-overlay: rgba(0, 0, 0, 0);
      --watermark-opacity: .15;
      --watermark-size: 48px;
      --watermark-color: #ffffff;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      color: var(--text-main);
      background:
        radial-gradient(circle at top left, var(--body-grad1), transparent 28%),
        radial-gradient(circle at bottom right, var(--body-grad2), transparent 30%),
        var(--app-bg);
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      transition: background .25s, color .25s;
    }

    .app {
      min-height: 100vh;
      display: grid;
      grid-template-rows: 64px 1fr auto;
    }

    /* ── Topbar ── */
    .topbar {
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: center;
      gap: 1rem;
      padding: 0 1.25rem;
      border-bottom: 1px solid var(--panel-border);
      background: var(--topbar-bg);
      backdrop-filter: blur(18px);
      position: sticky;
      top: 0;
      z-index: 20;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: .75rem;
      font-size: 1.25rem;
      font-weight: 850;
      letter-spacing: -.03em;
      white-space: nowrap;
      color: var(--text-main);
    }

    .brand-mark {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: grid;
      place-items: center;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      box-shadow: 0 0 22px rgba(139, 92, 246, .4);
      font-size: 1.3rem;
      color: #fff;
      flex-shrink: 0;
    }

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: .6rem;
    }

    /* ── Buttons allgemein ── */
    .format-btn,
    .tool-tab,
    .mini-btn,
    .theme-btn,
    .export-btn {
      border: 1px solid var(--panel-border);
      background: var(--panel-bg-soft);
      color: var(--text-main);
      border-radius: .55rem;
      padding: .45rem .7rem;
      font-weight: 700;
      font-size: .88rem;
      transition: .15s ease;
      cursor: pointer;
    }

    .format-btn:hover,
    .tool-tab:hover,
    .mini-btn:hover,
    .theme-btn:hover {
      background: var(--panel-bg);
      border-color: rgba(139, 92, 246, .4);
    }

    .format-btn.active,
    .tool-tab.active {
      border-color: rgba(168, 85, 247, .8);
      background: linear-gradient(135deg, rgba(139, 92, 246, .9), rgba(109, 40, 217, .72));
      color: #fff;
      box-shadow: 0 0 0 3px rgba(139, 92, 246, .16);
    }

    .theme-btn {
      width: 36px;
      height: 36px;
      padding: 0;
      display: grid;
      place-items: center;
      font-size: 1.1rem;
      border-radius: 50%;
    }

    .export-btn {
      border: 0;
      border-radius: .65rem;
      padding: .6rem 1.1rem;
      color: white;
      font-weight: 800;
      background: linear-gradient(135deg, #8b5cf6, #7c3aed);
      box-shadow: 0 8px 24px rgba(124, 58, 237, .3);
    }

    .export-btn:hover {
      opacity: .92;
    }

    /* ── Workspace ── */
    .workspace {
      display: grid;
      grid-template-columns: 88px 300px minmax(0, 1fr);
      min-height: 0;
    }

    /* ── Sidebar (Icons) ── */
    .sidebar {
      border-right: 1px solid var(--panel-border);
      background: var(--sidebar-bg);
      padding: .75rem .3rem;
      display: flex;
      flex-direction: column;
      gap: .4rem;
      align-items: center;
    }

    .tool-tab {
      width: 74px;
      min-height: 64px;
      display: grid;
      place-items: center;
      padding: .45rem .2rem;
      font-size: .72rem;
      border-radius: .75rem;
      text-align: center;
      line-height: 1.25;
      gap: .15rem;
    }

    .tool-tab .ico {
      display: block;
      font-size: 1.35rem;
      line-height: 1;
    }

    /* ── Panel ── */
    .panel {
      border-right: 1px solid var(--panel-border);
      background: var(--panel-bg);
      padding: 1.25rem;
      overflow-y: auto;
      max-height: calc(100vh - 98px);
    }

    .panel-section {
      display: none;
    }

    .panel-section.active {
      display: block;
    }

    .panel h2 {
      font-size: 1.05rem;
      font-weight: 800;
      margin: 0 0 1rem;
      letter-spacing: -.02em;
      color: var(--text-main);
    }

    .form-label {
      color: var(--text-main);
      font-size: .84rem;
      font-weight: 700;
      margin-bottom: .35rem;
    }

    .form-control,
    .form-select {
      color: var(--text-main);
      background-color: var(--input-bg);
      border: 1px solid var(--panel-border);
      border-radius: .6rem;
    }

    .form-control:focus,
    .form-select:focus {
      color: var(--text-main);
      background-color: var(--input-bg-focus);
      border-color: rgba(139, 92, 246, .86);
      box-shadow: 0 0 0 .22rem rgba(139, 92, 246, .16);
    }

    .form-control::placeholder {
      color: var(--text-muted);
    }

    textarea.form-control {
      min-height: 150px;
      resize: vertical;
    }

    .hint {
      color: var(--text-muted);
      font-size: .76rem;
      line-height: 1.35;
    }

    /* ── Format-Liste im Panel ── */
    .format-list {
      display: flex;
      flex-direction: column;
      gap: .45rem;
    }

    .format-btn {
      width: 100%;
      display: grid;
      grid-template-columns: 44px 1fr;
      align-items: center;
      gap: .6rem;
      text-align: left;
      padding: .6rem .75rem;
      border-radius: .65rem;
    }

    .format-btn .fmt-ratio {
      font-size: 1rem;
      font-weight: 900;
      letter-spacing: -.04em;
      text-align: center;
      color: var(--accent);
      min-width: 44px;
    }

    .format-btn.active .fmt-ratio {
      color: #fff;
    }

    .format-btn .fmt-label {
      display: flex;
      flex-direction: column;
      gap: .1rem;
    }

    .format-btn .fmt-name {
      font-size: .86rem;
      font-weight: 700;
    }

    .format-btn .fmt-desc {
      font-size: .72rem;
      font-weight: 400;
      opacity: .7;
    }

    /* ── Canvas-Bereich ── */
    .canvas-area {
      display: grid;
      grid-template-rows: 1fr 130px;
      gap: 1rem;
      padding: 1rem;
      min-width: 0;
    }

    .stage-wrap {
      min-height: 0;
      overflow: auto;
      display: grid;
      place-items: center;
      border-radius: 1rem;
      background:
        radial-gradient(circle at top left, rgba(255, 255, 255, .05), transparent 25%),
        var(--stage-bg);
      border: 1px solid var(--panel-border);
      padding: 1rem;
    }

    .stage-holder {
      width: calc(var(--quote-width) * var(--preview-scale));
      height: calc(var(--quote-height) * var(--preview-scale));
    }

    #quoteCanvas {
      width: var(--quote-width);
      height: var(--quote-height);
      position: relative;
      overflow: hidden;
      transform: scale(var(--preview-scale));
      transform-origin: top left;
      background: var(--quote-bg);
      /* Kein box-shadow — würde sonst im Export auftauchen */
      isolation: isolate;
    }

    .bg-image {
      position: absolute;
      inset: 0;
      z-index: 0;
      display: none;
      background-position: center;
      background-size: cover;
      background-repeat: no-repeat;
      filter:
        brightness(var(--bg-brightness)) contrast(var(--bg-contrast)) blur(var(--bg-blur)) grayscale(var(--bg-grayscale)) sepia(var(--bg-sepia));
      transform: scale(1.04);
    }

    #quoteCanvas.image-mode {
      background: #111827 !important;
    }

    #quoteCanvas.image-mode .bg-image {
      display: block;
    }

    .bg-overlay {
      position: absolute;
      inset: 0;
      z-index: 1;
      background: var(--bg-overlay);
      pointer-events: none;
    }

    .quote-content {
      position: absolute;
      inset: 0;
      z-index: 3;
      padding: var(--quote-padding);
      display: flex;
      flex-direction: column;
      justify-content: center;
      text-align: center;
      gap: 36px;
    }

    .quote-text {
      color: var(--quote-text);
      font-family: var(--quote-font);
      font-size: var(--quote-font-size);
      line-height: var(--quote-line-height);
      font-weight: 900;
      letter-spacing: -.045em;
      white-space: pre-wrap;
      overflow-wrap: anywhere;
      text-wrap: balance;
      text-shadow: 0 6px 28px rgba(0, 0, 0, .33);
    }

    .quote-author {
      color: var(--quote-author);
      font-family: var(--quote-font);
      font-size: calc(var(--quote-font-size) * .34);
      line-height: 1.25;
      font-weight: 750;
      opacity: .92;
      text-shadow: 0 5px 22px rgba(0, 0, 0, .32);
      white-space: pre-wrap;
    }

    .author-left {
      text-align: left;
    }

    .author-center {
      text-align: center;
    }

    .author-right {
      text-align: right;
    }

    .watermark {
      position: absolute;
      z-index: 2;
      color: var(--watermark-color);
      opacity: var(--watermark-opacity);
      font-size: var(--watermark-size);
      font-weight: 800;
      line-height: 1;
      max-width: 72%;
      overflow-wrap: anywhere;
      pointer-events: none;
      user-select: none;
      display: none;
    }

    #quoteCanvas.watermark-on .watermark {
      display: block;
    }

    .wm-bottom-right {
      right: 54px;
      bottom: 46px;
      text-align: right;
    }

    .wm-bottom-left {
      left: 54px;
      bottom: 46px;
      text-align: left;
    }

    .wm-top-right {
      right: 54px;
      top: 46px;
      text-align: right;
    }

    .wm-top-left {
      left: 54px;
      top: 46px;
      text-align: left;
    }

    .wm-center {
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%) rotate(-18deg);
      max-width: 90%;
      text-align: center;
      font-size: calc(var(--watermark-size) * 2.2);
      white-space: nowrap;
    }

    /* ── Bottom Controls ── */
    .bottom-controls {
      border: 1px solid var(--panel-border);
      border-radius: 1rem;
      background: var(--panel-bg-soft);
      padding: 1rem;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem 2rem;
      align-items: center;
    }

    .range-row {
      display: grid;
      grid-template-columns: 150px 1fr 58px;
      gap: .8rem;
      align-items: center;
      font-size: .86rem;
      color: var(--text-main);
    }

    input[type="range"] {
      accent-color: var(--accent);
    }

    /* ── Swatches ── */
    .swatch-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: .6rem;
    }

    .swatch {
      aspect-ratio: 1;
      border-radius: .5rem;
      border: 1px solid rgba(255, 255, 255, .14);
      cursor: pointer;
      padding: 0;
      transition: .15s ease;
    }

    .swatch:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, .28);
    }

    .swatch.active {
      outline: 3px solid rgba(139, 92, 246, .65);
      outline-offset: 2px;
    }

    .divider {
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      align-items: center;
      gap: .8rem;
      margin: 1.1rem 0;
      color: var(--text-muted);
      font-size: .75rem;
    }

    .divider::before,
    .divider::after {
      content: "";
      height: 1px;
      background: var(--panel-border);
    }

    /* ── Galerie ── */
    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: .6rem;
    }

    .gallery-item {
      aspect-ratio: 1;
      border-radius: .6rem;
      overflow: hidden;
      border: 2px solid transparent;
      background: var(--stage-bg);
      padding: 0;
      cursor: pointer;
    }

    .gallery-item.active {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(139, 92, 246, .18);
    }

    .gallery-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .gallery-loading {
      text-align: center;
      color: var(--text-muted);
      font-size: .82rem;
      padding: 1rem 0;
    }

    /* ── Farb-Zeile ── */
    .color-row {
      display: grid;
      grid-template-columns: 44px 1fr;
      gap: .7rem;
      align-items: center;
    }

    .color-row input[type="color"] {
      width: 44px;
      height: 38px;
      border-radius: .55rem;
      overflow: hidden;
      border: 1px solid var(--panel-border);
      background: transparent;
      padding: 0;
    }

    /* ── Position-Grid ── */
    .position-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: .4rem;
    }

    .position-btn {
      height: 34px;
      border: 1px solid var(--panel-border);
      background: var(--panel-bg-soft);
      border-radius: .45rem;
      color: var(--text-main);
      cursor: pointer;
    }

    .position-btn.active {
      background: rgba(139, 92, 246, .45);
      border-color: rgba(139, 92, 246, .9);
    }

.footer-note {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: .15rem;
  color: var(--text-muted);
  font-size: .8rem;
  border-top: 1px solid var(--panel-border);
  background: var(--footer-bg);
  padding: 0.6rem .75rem;
  white-space: nowrap;
  overflow: hidden;
}

    /* ── Responsive ── */
    @media (max-width: 1180px) {
      .topbar {
        grid-template-columns: 1fr auto;
        padding: .75rem 1rem;
        height: auto;
      }

      .workspace {
        grid-template-columns: 1fr;
      }

      .sidebar {
        flex-direction: row;
        overflow-x: auto;
        border-right: 0;
        border-bottom: 1px solid var(--panel-border);
      }

      .panel {
        max-height: none;
        border-right: 0;
        border-bottom: 1px solid var(--panel-border);
      }

      .canvas-area {
        grid-template-rows: auto auto;
      }

      .bottom-controls {
        grid-template-columns: 1fr;
      }

      .range-row {
        grid-template-columns: 120px 1fr 52px;
      }
    }
  </style>
</head>

<body>
  <main class="app">
    <header class="topbar">
      <div class="brand">
        <span class="brand-mark">❝</span>
        <span>BobaQuote</span>
      </div>
      <div class="topbar-actions">
        <button id="themeBtn" class="theme-btn" type="button" title="Dark / Light Mode">🌙</button>
        <button id="downloadPng" class="export-btn" type="button">⬇ PNG</button>
        <button id="downloadJpg" class="export-btn" style="background:linear-gradient(135deg,#0891b2,#0e7490);" type="button">⬇ JPEG</button>
      </div>
    </header>

    <section class="workspace">
      <nav class="sidebar" aria-label="Werkzeuge">
        <button class="tool-tab active" data-panel="textPanel" type="button"><span class="ico">T</span>Text</button>
        <button class="tool-tab" data-panel="backgroundPanel" type="button"><span class="ico">▧</span>Hinter&shy;grund</button>
        <button class="tool-tab" data-panel="stylePanel" type="button"><span class="ico">A</span>Stil</button>
        <button class="tool-tab" data-panel="watermarkPanel" type="button"><span class="ico">◎</span>Wasser&shy;zeichen</button>
        <button class="tool-tab" data-panel="formatPanel" type="button"><span class="ico">⊞</span>Format</button>
        <button class="tool-tab" data-panel="hilfePanel" type="button"><span class="ico">?</span>Hilfe</button>
      </nav>

      <aside class="panel">

        <!-- TEXT -->
        <section id="textPanel" class="panel-section active">
          <h2>Textinhalt</h2>
          <div class="mb-3">
            <label for="quoteInput" class="form-label">Zitat</label>
            <textarea id="quoteInput" class="form-control">Aufklären statt
Angst machen.</textarea>
          </div>
          <div class="mb-3">
            <label for="authorInput" class="form-label">Autor / Herkunft</label>
            <input id="authorInput" class="form-control" value="Sven Owsianowski" placeholder="z. B. Autor, Quelle, Jahr">
            <div class="hint mt-1">Wird kleiner dargestellt, Ausrichtung unten einstellbar.</div>
          </div>
          <div class="mb-3">
            <label for="authorPrefix" class="form-label">Autor-Präfix</label>
            <select id="authorPrefix" class="form-select">
              <option value="— ">— Autor</option>
              <option value="">Ohne Präfix</option>
              <option value="© ">© Autor</option>
              <option value="Quelle: ">Quelle: Autor</option>
            </select>
          </div>
          <div class="mb-3">
            <button id="randomQuoteBtn" type="button" class="btn w-100 fw-bold" style="border:2px solid <?php echo $primaryColor; ?>;color:<?php echo $primaryColor; ?>;border-radius:2rem;">
              🎲 Zufälliges Zitat
            </button>
          </div>
        </section>

        <!-- HINTERGRUND -->
        <section id="backgroundPanel" class="panel-section">
          <h2>Hintergrund</h2>
          <div class="mb-3">
            <label class="form-label">Modus</label>
            <div class="d-flex gap-2">
              <button id="modeGradientBtn" class="mini-btn active flex-fill" type="button">Verlauf</button>
              <button id="modeSolidBtn" class="mini-btn flex-fill" type="button">Vollton</button>
              <button id="modeImageBtn" class="mini-btn flex-fill" type="button">Bild</button>
            </div>
          </div>

          <div id="solidBox" class="d-none mb-3">
            <label class="form-label">Hintergrundfarbe</label>
            <div class="color-row">
              <input id="solidColor" type="color" value="#1e293b">
              <input id="solidColorText" class="form-control" value="#1E293B">
            </div>
          </div>

          <div id="gradientBox">
            <label class="form-label">Voreinstellungen</label>
            <div class="swatch-grid mb-3" id="swatchGrid"></div>
            <div class="divider">ODER</div>
            <div class="mb-3">
              <label class="form-label">Eigener Verlauf</label>
              <div class="mb-2">
                <label class="hint mb-1 d-block">Farbe 1</label>
                <div class="color-row">
                  <input id="colorA" type="color" value="#ff7a00">
                  <input id="colorAText" class="form-control" value="#FF7A00">
                </div>
              </div>
              <div class="mb-2">
                <label class="hint mb-1 d-block">Farbe 2</label>
                <div class="color-row">
                  <input id="colorB" type="color" value="#8a3e00">
                  <input id="colorBText" class="form-control" value="#8A3E00">
                </div>
              </div>
              <button id="randomColorsBtn" type="button" class="mini-btn w-100 mb-2 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-shuffle"></i> Zufallsfarben
              </button>
            </div>
            <div class="mb-3">
              <label for="gradientType" class="form-label">Verlaufstyp</label>
              <select id="gradientType" class="form-select">
                <option value="linear-135">Linear 135° · Diagonal</option>
                <option value="linear-45">Linear 45° · Diagonal</option>
                <option value="linear-90">Linear 90° · Links → Rechts</option>
                <option value="linear-180">Linear 180° · Oben → Unten</option>
                <option value="linear-0">Linear 0° · Unten → Oben</option>
                <option value="radial-center">Radial · Mitte</option>
                <option value="radial-topleft">Radial · Ecke oben links</option>
                <option value="radial-bottomright">Radial · Ecke unten rechts</option>
                <option value="radial-ellipse">Radial · Ellipse</option>
                <option value="mirror">Gespiegelt · A → B → A</option>
                <option value="split">Zweigeteilt · Harter Übergang</option>
              </select>
            </div>
          </div>

          <div id="imageBox" class="d-none">
            <div class="mb-3">
              <label for="imageUpload" class="form-label">Bild lokal wählen</label>
              <input id="imageUpload" class="form-control" type="file" accept="image/*">
              <div class="hint mt-1">Bleibt nur lokal im Browser — wird nicht hochgeladen.</div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem;">
              <label class="form-label mb-0">Galerie</label>
              <span id="galleryCount" style="display:none;font-size:.75rem;color:var(--text-muted);background:var(--panel-bg-soft);border:1px solid var(--panel-border);border-radius:.45rem;padding:1px 8px;"></span>
            </div>
            <div id="galleryGrid" class="gallery-grid mb-2">
              <div class="gallery-loading">Bilder werden geladen…</div>
            </div>
            <div id="galleryConsent" class="mb-2" style="display:none;">
              <div class="alert alert-info p-2 mb-2" style="font-size:.8rem;">
                <strong>Hinweis:</strong> Die Bildgalerie wird von den Servern von <strong>bobaro.de</strong> abgerufen. Durch Klick auf den Button wird eine Verbindung zu bobaro.de hergestellt.
              </div>
              <button id="galleryLoadBtn" type="button" class="btn btn-sm w-100" style="background:var(--accent);color:#fff;font-weight:700;">
                Galerie laden
              </button>
            </div>
            <div class="hint mb-3">Für den Export sind lokal geladene Bilder am zuverlässigsten.</div>
          </div>

          <h2 class="mt-4">Effekte</h2>
          <div class="mb-3">
            <label class="form-label">Überlagerung</label>
            <select id="overlayMode" class="form-select">
              <option value="none">Keine</option>
              <option value="dark">Dunkel</option>
              <option value="light">Hell</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="overlayOpacity" class="form-label">Deckkraft</label>
            <input id="overlayOpacity" type="range" min="0" max="80" value="0" class="form-range">
          </div>
          <div id="imageEffects" class="d-none">
            <div class="mb-3">
              <label for="brightnessRange" class="form-label">Helligkeit</label>
              <input id="brightnessRange" type="range" min="20" max="140" value="100" class="form-range">
            </div>
            <div class="mb-3">
              <label for="contrastRange" class="form-label">Kontrast</label>
              <input id="contrastRange" type="range" min="50" max="180" value="100" class="form-range">
            </div>
            <div class="mb-3">
              <label for="blurRange" class="form-label">Unschärfe</label>
              <input id="blurRange" type="range" min="0" max="18" value="0" class="form-range">
              <div id="safariBlurHint" class="hint mt-1" style="display:none;color:#f97316;">
                ⚠️ Safari unterstützt Unschärfe beim Export eingeschränkt. Für beste Ergebnisse Firefox oder Chrome verwenden.
              </div>
            </div>
            <div class="mb-3">
              <label for="grayscaleRange" class="form-label">Graustufen</label>
              <input id="grayscaleRange" type="range" min="0" max="100" value="0" class="form-range">
            </div>
            <div class="mb-3">
              <label for="sepiaRange" class="form-label">Sepia</label>
              <input id="sepiaRange" type="range" min="0" max="100" value="0" class="form-range">
            </div>
          </div>
        </section>

        <!-- STIL -->
        <section id="stylePanel" class="panel-section">
          <h2>Textstil</h2>
          <div class="mb-3">
            <label for="fontSelect" class="form-label">Schriftart (lokal)</label>
            <select id="fontSelect" class="form-select">
              <option value='Arial,Helvetica,sans-serif'>Arial / Helvetica</option>
              <option value='"Arial Black",Arial,sans-serif' selected>Arial Black</option>
              <option value='Verdana,Geneva,sans-serif'>Verdana</option>
              <option value='"Trebuchet MS",Helvetica,sans-serif'>Trebuchet MS</option>
              <option value='Tahoma,Geneva,sans-serif'>Tahoma</option>
              <option value='Georgia,serif'>Georgia</option>
              <option value='"Times New Roman",Times,serif'>Times New Roman</option>
              <option value='Impact,Haettenschweiler,"Arial Narrow Bold",sans-serif'>Impact</option>
              <option value='"Courier New",Courier,monospace'>Courier New</option>
              <option value='system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif'>System UI</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Textfarbe</label>
            <div class="color-row">
              <input id="textColor" type="color" value="#ffffff">
              <input id="textColorText" class="form-control" value="#FFFFFF">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Autorfarbe</label>
            <div class="color-row">
              <input id="authorColor" type="color" value="#ffffff">
              <input id="authorColorText" class="form-control" value="#FFFFFF">
            </div>
          </div>
          <div class="mb-3">
            <label for="textWeight" class="form-label">Schriftgewicht</label>
            <select id="textWeight" class="form-select">
              <option value="500">Normal</option>
              <option value="700">Fett</option>
              <option value="800">Extra fett</option>
              <option value="900" selected>Sehr fett</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="letterSpacing" class="form-label">Zeichenabstand</label>
            <input id="letterSpacing" type="range" min="-8" max="8" value="-4" class="form-range">
          </div>
          <div class="mb-3">
            <label for="shadowStrength" class="form-label">Textschatten</label>
            <input id="shadowStrength" type="range" min="0" max="80" value="33" class="form-range">
          </div>
          <div class="mb-3">
            <label class="form-label">Text-Ausrichtung</label>
            <div class="d-flex gap-2">
              <button class="mini-btn flex-fill" data-text-align="left" type="button">Links</button>
              <button class="mini-btn flex-fill active" data-text-align="center" type="button">Mitte</button>
              <button class="mini-btn flex-fill" data-text-align="right" type="button">Rechts</button>
            </div>
          </div>
        </section>

        <!-- WASSERZEICHEN -->
        <section id="watermarkPanel" class="panel-section">
          <h2>Wasserzeichen</h2>
          <div class="form-check form-switch mb-3">
            <input id="watermarkToggle" class="form-check-input" type="checkbox">
            <label class="form-check-label" for="watermarkToggle">Wasserzeichen anzeigen</label>
          </div>
          <div class="mb-3">
            <label for="watermarkText" class="form-label">Text</label>
            <input id="watermarkText" class="form-control" value="BobaQuote">
          </div>
          <div class="mb-3">
            <label class="form-label">Farbe</label>
            <div class="color-row">
              <input id="watermarkColor" type="color" value="#ffffff">
              <input id="watermarkColorText" class="form-control" value="#FFFFFF">
            </div>
          </div>
          <div class="mb-3">
            <label for="watermarkSize" class="form-label">Größe</label>
            <input id="watermarkSize" type="range" min="18" max="180" value="48" class="form-range">
          </div>
          <div class="mb-3">
            <label for="watermarkOpacity" class="form-label">Deckkraft</label>
            <input id="watermarkOpacity" type="range" min="3" max="55" value="15" class="form-range">
          </div>
          <div class="mb-3">
            <label class="form-label">Position</label>
            <div class="position-grid" id="watermarkPositions">
              <button class="position-btn" data-pos="wm-top-left" type="button">↖</button>
              <button class="position-btn" data-pos="wm-center" type="button">◇</button>
              <button class="position-btn" data-pos="wm-top-right" type="button">↗</button>
              <button class="position-btn" data-pos="wm-bottom-left" type="button">↙</button>
              <button class="position-btn active" data-pos="wm-bottom-right" type="button">↘</button>
              <span></span>
            </div>
          </div>
        </section>

        <!-- FORMAT -->
        <section id="formatPanel" class="panel-section">
          <h2>Format wählen</h2>
          <div class="format-list">
            <button class="format-btn active" data-format="1080x1080" type="button">
              <span class="fmt-ratio">1:1</span>
              <span class="fmt-label">
                <span class="fmt-name">Quadratisch</span>
                <span class="fmt-desc">Instagram · Facebook Post</span>
              </span>
            </button>
            <button class="format-btn" data-format="1080x1350" type="button">
              <span class="fmt-ratio">4:5</span>
              <span class="fmt-label">
                <span class="fmt-name">Instagram Portrait</span>
                <span class="fmt-desc">1080 × 1350 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="1080x1920" type="button">
              <span class="fmt-ratio">9:16</span>
              <span class="fmt-label">
                <span class="fmt-name">Story / Reel</span>
                <span class="fmt-desc">WhatsApp Status · TikTok</span>
              </span>
            </button>
            <button class="format-btn" data-format="1920x1080" type="button">
              <span class="fmt-ratio">16:9</span>
              <span class="fmt-label">
                <span class="fmt-name">YouTube / Blog</span>
                <span class="fmt-desc">Thumbnail · Bannerbild</span>
              </span>
            </button>
            <button class="format-btn" data-format="1920x400" type="button">
              <span class="fmt-ratio">~5:1</span>
              <span class="fmt-label">
                <span class="fmt-name">Blog Header</span>
                <span class="fmt-desc">Website · Blog · Aufteiler</span>
              </span>
            </button>
            <button class="format-btn" data-format="1200x630" type="button">
              <span class="fmt-ratio">OG</span>
              <span class="fmt-label">
                <span class="fmt-name">Facebook / Open Graph</span>
                <span class="fmt-desc">1200 × 630 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="1000x1500" type="button">
              <span class="fmt-ratio">2:3</span>
              <span class="fmt-label">
                <span class="fmt-name">Pinterest</span>
                <span class="fmt-desc">1000 × 1500 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="1080x566" type="button">
              <span class="fmt-ratio">1.91</span>
              <span class="fmt-label">
                <span class="fmt-name">Twitter / X</span>
                <span class="fmt-desc">Landscape Card</span>
              </span>
            </button>
            <button class="format-btn" data-format="1584x396" type="button">
              <span class="fmt-ratio">4:1</span>
              <span class="fmt-label">
                <span class="fmt-name">LinkedIn Banner</span>
                <span class="fmt-desc">1584 × 396 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="2560x1440" type="button">
              <span class="fmt-ratio">QHD</span>
              <span class="fmt-label">
                <span class="fmt-name">Desktop Wallpaper</span>
                <span class="fmt-desc">2560 × 1440 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="640x640" type="button">
              <span class="fmt-ratio">1:1</span>
              <span class="fmt-label">
                <span class="fmt-name">WhatsApp Profilbild</span>
                <span class="fmt-desc">640 × 640 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="1500x500" type="button">
              <span class="fmt-ratio">3:1</span>
              <span class="fmt-label">
                <span class="fmt-name">Twitter / X Banner</span>
                <span class="fmt-desc">1500 × 500 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="1200x627" type="button">
              <span class="fmt-ratio">OG</span>
              <span class="fmt-label">
                <span class="fmt-name">LinkedIn / Xing Post</span>
                <span class="fmt-desc">1200 × 627 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="600x200" type="button">
              <span class="fmt-ratio">3:1</span>
              <span class="fmt-label">
                <span class="fmt-name">E-Mail Header</span>
                <span class="fmt-desc">600 × 200 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="1080x608" type="button">
              <span class="fmt-ratio">16:9</span>
              <span class="fmt-label">
                <span class="fmt-name">Facebook Video Cover</span>
                <span class="fmt-desc">1080 × 608 px</span>
              </span>
            </button>
            <button class="format-btn" data-format="800x800" type="button">
              <span class="fmt-ratio">1:1</span>
              <span class="fmt-label">
                <span class="fmt-name">Podcast Cover</span>
                <span class="fmt-desc">800 × 800 px</span>
              </span>
            </button>
          </div>
        </section>

        <!-- HILFE -->
        <section id="hilfePanel" class="panel-section">
          <h2 id="hilfeTitel"></h2>
          <div id="hilfeInhalt" class="hint" style="line-height:1.6"></div>
        </section>

      </aside>

      <!-- CANVAS -->
      <section class="canvas-area">
        <div class="stage-wrap">
          <div class="stage-holder">
            <div id="quoteCanvas">
              <div id="bgImage" class="bg-image"></div>
              <div class="bg-overlay"></div>
              <div id="watermarkPreview" class="watermark wm-bottom-right">Dein Name / Marke</div>
              <div class="quote-content">
                <div id="quotePreview" class="quote-text">Aufklären statt
                  Angst machen.</div>
                <div id="authorPreview" class="quote-author author-right">— Sven Owsianowski</div>
              </div>
            </div>
          </div>
        </div>

        <div class="bottom-controls">
          <div class="range-row">
            <label for="fontSizeRange">Textgröße</label>
            <input id="fontSizeRange" type="range" min="28" max="160" value="84">
            <span id="fontSizeValue">84px</span>
          </div>
          <div class="range-row">
            <label for="lineHeightRange">Zeilenabstand</label>
            <input id="lineHeightRange" type="range" min="90" max="160" value="120">
            <span id="lineHeightValue">1.2</span>
          </div>
          <div class="range-row">
            <label>Autor-Position</label>
            <div class="btn-group" role="group">
              <button class="mini-btn" data-author-pos="author-left" type="button">Links</button>
              <button class="mini-btn" data-author-pos="author-center" type="button">Mitte</button>
              <button class="mini-btn active" data-author-pos="author-right" type="button">Rechts</button>
            </div>
            <span></span>
          </div>
          <div class="range-row">
            <label for="autoFitToggle">Auto-Fit</label>
            <div class="form-check form-switch">
              <input id="autoFitToggle" class="form-check-input" type="checkbox">
              <label class="form-check-label hint" for="autoFitToggle">optional</label>
            </div>
            <span></span>
          </div>
        </div>
      </section>
    </section>

<footer class="footer-note">
  <span>Bearbeitungen erfolgen lokal &middot; Vorgefertigte Hintergrundbilder von 
  <a href="https://bobaro.de" style="color:inherit;white-space:nowrap;" target="_blank">bobaro.de</a></span>
  <span>
    <a href="https://bobaro.de/impressum.php" style="color:inherit;white-space:nowrap;" target="_blank">Impressum</a> &middot; 
    <a href="https://bobaro.de/datenschutz.php" style="color:inherit;white-space:nowrap;" target="_blank">Datenschutz</a>
  </span>
</footer>
  </main>

  <script>
    /* ============================================================
   KONFIGURATION — hier kannst du den Hilfetext anpassen.
   HTML ist erlaubt (z. B. <b>, <a href="">, <br>).
   ============================================================ */
    const HILFE_TEXT = {
      titel: "Über den BobaQuote",
      inhalt: `
    <p><b>Galerie-Bilder</b><br>
    Alle Bilder in der Galerie sind entweder selbst erstellt oder mit KI generiert.
    Sie dürfen kostenlos für private und kommerzielle Zwecke verwendet werden —
    auch in veröffentlichten Beiträgen und Social-Media-Posts.</p>

    <p><b>Datenschutz</b><br>
    Alle Bearbeitungen und hochgeladene Bilder verbleiben ausschließlich lokal in deinem 
    Browser — es werden keine Daten gespeichert. Beim Abrufen der Bildvorlagen wird eine 
    Verbindung zu bobaro.de hergestellt. Weitere Informationen findest du in der 
    <a href="https://bobaro.de/datenschutz.php" target="_blank">Datenschutzerklärung</a>.</p>

    <p><b>Eigene Bilder</b><br>
    Über „Bild lokal wählen" kannst du jederzeit eigene Fotos verwenden —
    sie verlassen deinen Browser nicht.</p>

    <p><b>Export</b><br>
    Die fertige Grafik kannst du als PNG (verlustfrei) oder JPEG (kleinere Dateigröße)
    herunterladen.</p>
    
    <p><b>Entwickler</b><br>
    Ich, Sven Owsianowski, habe im Juni 2026 die WebApp BobaQuote entwickelt. BobaQuote dient dazu, 
    Textzitate grafisch ansprechend darzustellen und in unterschiedliche Formate 
    auszugeben.</p>
    
    <p><b>Impressum</b><br>
    Selbstverständlich gibt es auch ein ordentliches <a href="https://bobaro.de/impressum.php" target="_blank">Impressum</a>. Dies kannst du hier öffnen oder über
    den Footer der Website. Ebenfalls auch die Datenschutzerklärung.</p>

    <hr style="opacity:.2;margin:1rem 0">
    <p style="font-size:.78rem;opacity:.6">
      BobaQuote · Teil von <a href="https://bobaro.de" target="_blank"
      style="color:inherit">Bobaro</a> ·
      Alle Rechte vorbehalten.
    </p>
  `
    };
    /* ============================================================ */

    document.addEventListener("DOMContentLoaded", () => {
      const $ = (id) => document.getElementById(id);

      // ── Zitate ──
      const ZITATE = <?php echo $zitateJson; ?>;
      document.getElementById("randomQuoteBtn").addEventListener("click", () => {
        if (!ZITATE.length) return;
        const z = ZITATE[Math.floor(Math.random() * ZITATE.length)];
        el.quoteInput.value  = z.text;
        el.authorInput.value = z.autor;
        updateAll();
      });

      // ── Safari-Erkennung ──
      const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
      if (isSafari) {
        const hint = document.getElementById("safariBlurHint");
        if (hint) hint.style.display = "block";
      }
      const root = document.documentElement;
      const canvas = $("quoteCanvas");

      const el = {
        quoteInput: $("quoteInput"),
        authorInput: $("authorInput"),
        authorPrefix: $("authorPrefix"),
        quotePreview: $("quotePreview"),
        authorPreview: $("authorPreview"),
        modeGradientBtn: $("modeGradientBtn"),
        modeSolidBtn: $("modeSolidBtn"),
        modeImageBtn: $("modeImageBtn"),
        gradientBox: $("gradientBox"),
        solidBox: $("solidBox"),
        solidColor: $("solidColor"),
        solidColorText: $("solidColorText"),
        imageBox: $("imageBox"),
        swatchGrid: $("swatchGrid"),
        colorA: $("colorA"),
        colorAText: $("colorAText"),
        colorB: $("colorB"),
        colorBText: $("colorBText"),
        gradientType: $("gradientType"),
        imageUpload: $("imageUpload"),
        galleryGrid: $("galleryGrid"),
        bgImage: $("bgImage"),
        imageEffects: $("imageEffects"),
        brightnessRange: $("brightnessRange"),
        contrastRange: $("contrastRange"),
        blurRange: $("blurRange"),
        grayscaleRange: $("grayscaleRange"),
        sepiaRange: $("sepiaRange"),
        overlayMode: $("overlayMode"),
        overlayOpacity: $("overlayOpacity"),
        fontSelect: $("fontSelect"),
        textColor: $("textColor"),
        textColorText: $("textColorText"),
        authorColor: $("authorColor"),
        authorColorText: $("authorColorText"),
        textWeight: $("textWeight"),
        letterSpacing: $("letterSpacing"),
        shadowStrength: $("shadowStrength"),
        fontSizeRange: $("fontSizeRange"),
        fontSizeValue: $("fontSizeValue"),
        lineHeightRange: $("lineHeightRange"),
        lineHeightValue: $("lineHeightValue"),
        autoFitToggle: $("autoFitToggle"),
        watermarkToggle: $("watermarkToggle"),
        watermarkText: $("watermarkText"),
        watermarkPreview: $("watermarkPreview"),
        watermarkColor: $("watermarkColor"),
        watermarkColorText: $("watermarkColorText"),
        watermarkSize: $("watermarkSize"),
        watermarkOpacity: $("watermarkOpacity"),
        watermarkPositions: $("watermarkPositions"),
        themeBtn: $("themeBtn"),
        downloadPng: $("downloadPng"),
        downloadJpg: $("downloadJpg")
      };

      const gradients = [
        ["#7c3aed", "#ec4899"],
        ["#0ea5e9", "#1d4ed8"],
        ["#f97316", "#ec4899"],
        ["#be123c", "#831843"],
        ["#ff7a00", "#8a3e00"],
        ["#fb7185", "#f97316"],
        ["#7e22ce", "#334155"],
        ["#0f172a", "#334155"],
        ["#6d28d9", "#111827"],
        ["#84cc16", "#16a34a"],
        ["#0891b2", "#164e63"],
        ["#a78bfa", "#2563eb"]
      ];

      let currentMode = "gradient";
      let currentFormat = {
        w: 1080,
        h: 1080
      };
      let currentTextAlign = "center";

      // ── Dark / Light Mode ──
      const savedTheme = localStorage.getItem("qmTheme") || "dark";
      root.setAttribute("data-theme", savedTheme);
      el.themeBtn.textContent = savedTheme === "dark" ? "🌙" : "☀️";

      el.themeBtn.addEventListener("click", () => {
        const next = root.getAttribute("data-theme") === "dark" ? "light" : "dark";
        root.setAttribute("data-theme", next);
        el.themeBtn.textContent = next === "dark" ? "🌙" : "☀️";
        localStorage.setItem("qmTheme", next);
      });

      // ── Hilfetext einsetzen ──
      $("hilfeTitel").textContent = HILFE_TEXT.titel;
      $("hilfeInhalt").innerHTML = HILFE_TEXT.inhalt;

      // ── Galerie von Server laden ──
      const GALLERY_URL = "<?= GALERIE_BILDER_URL ?>";
      const LIST_URL = "?proxy=list";

      async function loadGallery() {
        try {
          const res = await fetch(LIST_URL);
          if (!res.ok) throw new Error("HTTP " + res.status);
          const files = await res.json();

          const grid = el.galleryGrid;
          grid.innerHTML = "";

          if (!files.length) {
            grid.innerHTML = '<div class="gallery-loading">Keine Bilder gefunden.</div>';
            return;
          }

          files.forEach(filename => {
            // Vorschau direkt von bobarox (nur img-Tag, kein Canvas → kein CORS-Problem)
            const previewUrl = GALLERY_URL + encodeURIComponent(filename);
            // Für Export: Proxy-URL auf demselben Server → Canvas bleibt sauber
            const proxyUrl = "?proxy=img&file=" + encodeURIComponent(filename);
            const btn = document.createElement("button");
            btn.className = "gallery-item";
            btn.type = "button";
            btn.dataset.url = proxyUrl; // Export nutzt Proxy
            const img = document.createElement("img");
            img.src = previewUrl; // Vorschau direkt von bobarox
            img.alt = filename;
            img.loading = "lazy";
            btn.appendChild(img);
            grid.appendChild(btn);
          });

          // Anzahl-Badge aktualisieren
          const countBadge = document.getElementById("galleryCount");
          if (countBadge) {
            countBadge.textContent = files.length + " Bilder";
            countBadge.style.display = "inline";
          }

        } catch (e) {
          el.galleryGrid.innerHTML = '<div class="gallery-loading">Galerie konnte nicht geladen werden.</div>';
        }
      }

      // ── Galerie: Einwilligung prüfen ──
  const CONSENT_KEY = "bobaquote_gallery_consent";
  const consentBox  = document.getElementById("galleryConsent");
  const gridBox     = el.galleryGrid;

  if (localStorage.getItem(CONSENT_KEY) === "1") {
    // Bereits zugestimmt → direkt laden
    loadGallery();
  } else {
    // Noch nicht zugestimmt → Hinweis + Button zeigen, Lade-Spinner verstecken
    gridBox.style.display = "none";
    consentBox.style.display = "block";
    document.getElementById("galleryLoadBtn").addEventListener("click", () => {
      localStorage.setItem(CONSENT_KEY, "1");
      consentBox.style.display = "none";
      gridBox.style.display = "";
      loadGallery();
    });
  }

      // ── Hilfsfunktionen ──
      function clampHex(value) {
        const clean = String(value).trim();
        if (/^#[0-9a-fA-F]{6}$/.test(clean)) return clean.toUpperCase();
        return "#FFFFFF";
      }

      function syncColor(colorInput, textInput) {
        colorInput.value = clampHex(textInput.value);
        textInput.value = colorInput.value.toUpperCase();
      }

      function makeGradient() {
        const a = el.colorA.value, b = el.colorB.value;
        const t = el.gradientType.value;
        if (t === "radial-center")      return `radial-gradient(circle at center, ${a}, ${b})`;
        if (t === "radial-topleft")     return `radial-gradient(circle at top left, ${a}, ${b})`;
        if (t === "radial-bottomright") return `radial-gradient(circle at bottom right, ${a}, ${b})`;
        if (t === "radial-ellipse")     return `radial-gradient(ellipse at center, ${a}, ${b})`;
        if (t === "mirror")             return `linear-gradient(135deg, ${a}, ${b}, ${a})`;
        if (t === "split")              return `linear-gradient(135deg, ${a} 0%, ${a} 40%, ${b} 60%, ${b} 100%)`;
        const deg = t.split("-")[1] || "135";
        return `linear-gradient(${deg}deg, ${a}, ${b})`;
      }

      function setMode(mode) {
        currentMode = mode;
        const imageMode = mode === "image";
        const solidMode = mode === "solid";
        canvas.classList.toggle("image-mode", imageMode);
        el.modeGradientBtn.classList.toggle("active", mode === "gradient");
        el.modeSolidBtn.classList.toggle("active", solidMode);
        el.modeImageBtn.classList.toggle("active", imageMode);
        el.gradientBox.classList.toggle("d-none", mode !== "gradient");
        el.solidBox.classList.toggle("d-none", !solidMode);
        el.imageBox.classList.toggle("d-none", !imageMode);
        el.imageEffects.classList.toggle("d-none", !imageMode);
        if (!imageMode) el.bgImage.style.backgroundImage = "none";
        updateAll();
      }

      function calcScale() {
        const stageWrap = document.querySelector(".stage-wrap");
        const availW = stageWrap ? stageWrap.clientWidth - 32 : 720;
        const availH = stageWrap ? stageWrap.clientHeight - 32 : 720;
        const scaleW = availW / currentFormat.w;
        const scaleH = availH / currentFormat.h;
        return Math.min(scaleW, scaleH, 0.92);
      }

      function setFormat(format) {
        const [w, h] = format.split("x").map(Number);
        currentFormat = {
          w,
          h
        };
        root.style.setProperty("--quote-width", `${w}px`);
        root.style.setProperty("--quote-height", `${h}px`);
        const scale = calcScale();
        root.style.setProperty("--preview-scale", String(scale));
        const padding = Math.round(Math.max(52, Math.min(128, Math.min(w, h) * .09)));
        root.style.setProperty("--quote-padding", `${padding}px`);
        const holder = document.querySelector(".stage-holder");
        if (holder) {
          holder.style.width = `${w * scale}px`;
          holder.style.height = `${h * scale}px`;
        }
        updateAll();
      }

      // Scale bei Fenstergrößenänderung neu berechnen
      const stageWrap = document.querySelector(".stage-wrap");
      if (stageWrap && window.ResizeObserver) {
        new ResizeObserver(() => {
          const scale = calcScale();
          root.style.setProperty("--preview-scale", String(scale));
          // stage-holder ebenfalls anpassen
          const holder = document.querySelector(".stage-holder");
          if (holder) {
            holder.style.width = `${currentFormat.w * scale}px`;
            holder.style.height = `${currentFormat.h * scale}px`;
          }
        }).observe(stageWrap);
      }

      function renderSwatches() {
        el.swatchGrid.innerHTML = "";
        gradients.forEach((pair, index) => {
          const btn = document.createElement("button");
          btn.type = "button";
          btn.className = "swatch" + (index === 4 ? " active" : "");
          btn.style.background = `linear-gradient(135deg,${pair[0]},${pair[1]})`;
          btn.addEventListener("click", () => {
            document.querySelectorAll(".swatch").forEach(s => s.classList.remove("active"));
            btn.classList.add("active");
            el.colorA.value = pair[0];
            el.colorB.value = pair[1];
            el.colorAText.value = pair[0].toUpperCase();
            el.colorBText.value = pair[1].toUpperCase();
            updateAll();
          });
          el.swatchGrid.appendChild(btn);
        });
      }

      function autoFit() {
        if (!el.autoFitToggle.checked) return;
        const text = el.quoteInput.value.trim();
        let size = Math.min(150, Math.max(34, Math.round(98 - text.length * .35)));
        if (currentFormat.h > currentFormat.w) size += 6;
        if (currentFormat.w > currentFormat.h) size -= 8;
        el.fontSizeRange.value = size;
        el.fontSizeValue.textContent = `${size}px`;
        root.style.setProperty("--quote-font-size", `${size}px`);
      }

      function updateOverlay() {
        const mode    = el.overlayMode.value;
        const opacity = Number(el.overlayOpacity.value) / 100;
        // Schieberegler-Startwert nur wenn noch auf 0
        if (mode === "dark"  && opacity === 0) el.overlayOpacity.value = 40;
        if (mode === "light" && opacity === 0) el.overlayOpacity.value = 30;
        const op = Number(el.overlayOpacity.value) / 100;
        let color = "rgba(0,0,0,0)";
        if (mode === "dark")              color = `rgba(0,0,0,${op})`;
        else if (mode === "light")        color = `rgba(255,255,255,${op})`;
        else if (mode === "none" && op > 0) color = `rgba(0,0,0,${op})`; // Fallback dunkel
        root.style.setProperty("--bg-overlay", color);
      }

      function updateAll() {
        autoFit();
        if (currentMode === "solid") {
          canvas.style.background = el.solidColor.value;
        } else if (currentMode === "gradient") {
          canvas.style.background = makeGradient();
        }
        el.quotePreview.textContent = el.quoteInput.value || "Dein Zitat erscheint hier.";
        const author = el.authorInput.value.trim();
        el.authorPreview.textContent = author ? `${el.authorPrefix.value}${author}` : "";
        root.style.setProperty("--quote-font", el.fontSelect.value);
        root.style.setProperty("--quote-text", el.textColor.value);
        root.style.setProperty("--quote-author", el.authorColor.value);
        root.style.setProperty("--quote-font-size", `${el.fontSizeRange.value}px`);
        root.style.setProperty("--quote-line-height", String(Number(el.lineHeightRange.value) / 100));
        el.fontSizeValue.textContent = `${el.fontSizeRange.value}px`;
        el.lineHeightValue.textContent = String(Number(el.lineHeightRange.value) / 100);
        el.quotePreview.style.textAlign = currentTextAlign;
        el.quotePreview.style.fontWeight = el.textWeight.value;
        el.quotePreview.style.letterSpacing = `${el.letterSpacing.value}px`;
        const shadowAlpha = Number(el.shadowStrength.value) / 100;
        el.quotePreview.style.textShadow = `0 6px 28px rgba(0,0,0,${shadowAlpha})`;
        el.authorPreview.style.textShadow = `0 5px 22px rgba(0,0,0,${shadowAlpha})`;
        root.style.setProperty("--bg-brightness", `${el.brightnessRange.value}%`);
        root.style.setProperty("--bg-contrast", `${el.contrastRange.value}%`);
        root.style.setProperty("--bg-blur", `${el.blurRange.value}px`);
        root.style.setProperty("--bg-grayscale", `${el.grayscaleRange.value}%`);
        root.style.setProperty("--bg-sepia", `${el.sepiaRange.value}%`);
        updateOverlay();
        canvas.classList.toggle("watermark-on", el.watermarkToggle.checked);
        el.watermarkPreview.textContent = el.watermarkText.value;
        root.style.setProperty("--watermark-color", el.watermarkColor.value);
        root.style.setProperty("--watermark-size", `${el.watermarkSize.value}px`);
        root.style.setProperty("--watermark-opacity", String(Number(el.watermarkOpacity.value) / 100));
      }

      function setAuthorPosition(pos) {
        el.authorPreview.classList.remove("author-left", "author-center", "author-right");
        el.authorPreview.classList.add(pos);
        document.querySelectorAll("[data-author-pos]").forEach(btn =>
          btn.classList.toggle("active", btn.dataset.authorPos === pos));
      }

      function setWatermarkPosition(pos) {
        el.watermarkPreview.classList.remove("wm-top-left", "wm-top-right", "wm-bottom-left", "wm-bottom-right", "wm-center");
        el.watermarkPreview.classList.add(pos);
        document.querySelectorAll(".position-btn").forEach(btn =>
          btn.classList.toggle("active", btn.dataset.pos === pos));
      }

      // ── Events ──
      document.querySelectorAll(".tool-tab").forEach(tab => {
        tab.addEventListener("click", () => {
          document.querySelectorAll(".tool-tab").forEach(t => t.classList.remove("active"));
          document.querySelectorAll(".panel-section").forEach(p => p.classList.remove("active"));
          tab.classList.add("active");
          $(tab.dataset.panel).classList.add("active");
        });
      });

      document.querySelectorAll(".format-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          document.querySelectorAll(".format-btn").forEach(b => b.classList.remove("active"));
          btn.classList.add("active");
          setFormat(btn.dataset.format);
        });
      });

      el.modeGradientBtn.addEventListener("click", () => setMode("gradient"));
      el.modeSolidBtn.addEventListener("click", () => setMode("solid"));
      el.modeImageBtn.addEventListener("click", () => setMode("image"));

      el.solidColor.addEventListener("input", () => {
        el.solidColorText.value = el.solidColor.value.toUpperCase();
        updateAll();
      });
      el.solidColorText.addEventListener("change", () => {
        syncColor(el.solidColor, el.solidColorText);
        updateAll();
      });

      document.querySelectorAll("[data-text-align]").forEach(btn => {
        btn.addEventListener("click", () => {
          currentTextAlign = btn.dataset.textAlign;
          document.querySelectorAll("[data-text-align]").forEach(b => b.classList.remove("active"));
          btn.classList.add("active");
          updateAll();
        });
      });

      el.imageUpload.addEventListener("change", event => {
        const file = event.target.files && event.target.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        el.bgImage.style.backgroundImage = `url("${url}")`;
        document.querySelectorAll(".gallery-item").forEach(g => g.classList.remove("active"));
        setMode("image");
      });

      el.galleryGrid.addEventListener("click", event => {
        const btn = event.target.closest(".gallery-item");
        if (!btn) return;
        document.querySelectorAll(".gallery-item").forEach(g => g.classList.remove("active"));
        btn.classList.add("active");
        el.bgImage.style.backgroundImage = `url("${btn.dataset.url}")`;
        setMode("image");
      });

      document.querySelectorAll("[data-author-pos]").forEach(btn => {
        btn.addEventListener("click", () => setAuthorPosition(btn.dataset.authorPos));
      });

      el.watermarkPositions.addEventListener("click", event => {
        const btn = event.target.closest(".position-btn");
        if (!btn) return;
        setWatermarkPosition(btn.dataset.pos);
      });

      el.colorA.addEventListener("input", () => {
        el.colorAText.value = el.colorA.value.toUpperCase();
        updateAll();
      });
      el.colorB.addEventListener("input", () => {
        el.colorBText.value = el.colorB.value.toUpperCase();
        updateAll();
      });

      // Zufallsfarben
      document.getElementById("randomColorsBtn").addEventListener("click", () => {
        const rand = () => '#' + Math.floor(Math.random() * 0xFFFFFF).toString(16).padStart(6, '0');
        const a = rand(), b = rand();
        el.colorA.value = a; el.colorAText.value = a.toUpperCase();
        el.colorB.value = b; el.colorBText.value = b.toUpperCase();
        document.querySelectorAll(".swatch").forEach(s => s.classList.remove("active"));
        updateAll();
      });
      el.colorAText.addEventListener("change", () => {
        syncColor(el.colorA, el.colorAText);
        updateAll();
      });
      el.colorBText.addEventListener("change", () => {
        syncColor(el.colorB, el.colorBText);
        updateAll();
      });
      el.textColor.addEventListener("input", () => {
        el.textColorText.value = el.textColor.value.toUpperCase();
        updateAll();
      });
      el.textColorText.addEventListener("change", () => {
        syncColor(el.textColor, el.textColorText);
        updateAll();
      });
      el.authorColor.addEventListener("input", () => {
        el.authorColorText.value = el.authorColor.value.toUpperCase();
        updateAll();
      });
      el.authorColorText.addEventListener("change", () => {
        syncColor(el.authorColor, el.authorColorText);
        updateAll();
      });
      el.watermarkColor.addEventListener("input", () => {
        el.watermarkColorText.value = el.watermarkColor.value.toUpperCase();
        updateAll();
      });
      el.watermarkColorText.addEventListener("change", () => {
        syncColor(el.watermarkColor, el.watermarkColorText);
        updateAll();
      });

      document.addEventListener("input", event => {
        if (event.target.closest(".workspace") || event.target.closest(".bottom-controls")) updateAll();
      });
      document.addEventListener("change", event => {
        if (event.target.closest(".workspace") || event.target.closest(".bottom-controls")) updateAll();
      });

      // ── Export: native Canvas API ──
      async function exportImage(format) {
        const W = currentFormat.w,
          H = currentFormat.h;
        const c = document.createElement("canvas");
        c.width = W;
        c.height = H;
        const ctx = c.getContext("2d");

        // 1. Hintergrund: Bild oder Verlauf
        if (currentMode === "image") {
          const bgStyle = el.bgImage.style.backgroundImage;
          const match = bgStyle && bgStyle.match(/url\(["']?(.+?)["']?\)/);
          if (match) {
            await new Promise(resolve => {
              const img = new Image();

              img.onload = () => {
                const br = el.brightnessRange.value;
                const co = el.contrastRange.value;
                const bl = Number(el.blurRange.value);
                const gr = el.grayscaleRange.value;
                const se = el.sepiaRange.value;

                const scale = Math.max(W / img.width, H / img.height);
                const sw    = img.width  * scale;
                const sh    = img.height * scale;
                const sx    = (W - sw) / 2;
                const sy    = (H - sh) / 2;

                if (bl > 0) {
                  // Offscreen-Canvas: Bild erst ohne Blur zeichnen,
                  // dann den fertigen Canvas mit blur() als Quelle nutzen
                  const extra = Math.ceil(bl * 2.5);
                  const ow = W + extra * 2;
                  const oh = H + extra * 2;
                  const off    = document.createElement("canvas");
                  off.width    = ow;
                  off.height   = oh;
                  const offCtx = off.getContext("2d");

                  // Schritt 1: Bild auf Offscreen ohne Blur
                  if (br != 100 || co != 100 || gr != 0 || se != 0) {
                    offCtx.filter = `brightness(${br}%) contrast(${co}%) grayscale(${gr}%) sepia(${se}%)`;
                  }
                  offCtx.drawImage(img, sx + extra, sy + extra, sw, sh);
                  offCtx.filter = "none";

                  // Schritt 2: Offscreen mit blur() auf Ziel-Canvas
                  ctx.save();
                  ctx.beginPath();
                  ctx.rect(0, 0, W, H);
                  ctx.clip();
                  ctx.filter = `blur(${bl}px)`;
                  ctx.drawImage(off, -extra, -extra, ow, oh);
                  ctx.filter = "none";
                  ctx.restore();
                } else {
                  if (br != 100 || co != 100 || gr != 0 || se != 0) {
                    ctx.filter = `brightness(${br}%) contrast(${co}%) grayscale(${gr}%) sepia(${se}%)`;
                  }
                  ctx.drawImage(img, sx, sy, sw, sh);
                  ctx.filter = "none";
                }
                resolve();
              };
              img.onerror = () => {
                ctx.fillStyle = "#111827";
                ctx.fillRect(0, 0, W, H);
                resolve();
              };
              img.src = match[1];
            });
          } else {
            ctx.fillStyle = "#111827";
            ctx.fillRect(0, 0, W, H);
          }
        } else if (currentMode === "solid") {
          ctx.fillStyle = el.solidColor.value;
          ctx.fillRect(0, 0, W, H);
        } else {
          // Farbverlauf
          let grad;
          const a = el.colorA.value, b = el.colorB.value;
          const t = el.gradientType.value;

          if (t.startsWith("radial")) {
            let cx = W/2, cy = H/2;
            if (t === "radial-topleft")     { cx = 0;  cy = 0; }
            if (t === "radial-bottomright") { cx = W;  cy = H; }
            const isEllipse = t === "radial-ellipse";
            const rx = isEllipse ? W/1.4 : Math.max(W,H)/1.4;
            const ry = isEllipse ? H/1.4 : Math.max(W,H)/1.4;
            grad = ctx.createRadialGradient(cx, cy, 0, cx, cy, Math.max(rx,ry));
            grad.addColorStop(0, a);
            grad.addColorStop(1, b);
          } else if (t === "mirror") {
            const rad = (135 - 90) * Math.PI / 180;
            const half = Math.sqrt(W*W + H*H) / 2;
            grad = ctx.createLinearGradient(W/2 - Math.cos(rad)*half, H/2 - Math.sin(rad)*half,
                                            W/2 + Math.cos(rad)*half, H/2 + Math.sin(rad)*half);
            grad.addColorStop(0, a);
            grad.addColorStop(0.5, b);
            grad.addColorStop(1, a);
          } else if (t === "split") {
            const rad = (135 - 90) * Math.PI / 180;
            const half = Math.sqrt(W*W + H*H) / 2;
            grad = ctx.createLinearGradient(W/2 - Math.cos(rad)*half, H/2 - Math.sin(rad)*half,
                                            W/2 + Math.cos(rad)*half, H/2 + Math.sin(rad)*half);
            grad.addColorStop(0,   a);
            grad.addColorStop(0.4, a);
            grad.addColorStop(0.6, b);
            grad.addColorStop(1,   b);
          } else {
            const deg = parseFloat(t.split("-")[1]) || 135;
            const rad = (deg - 90) * Math.PI / 180;
            const half = Math.sqrt(W*W + H*H) / 2;
            grad = ctx.createLinearGradient(W/2 - Math.cos(rad)*half, H/2 - Math.sin(rad)*half,
                                            W/2 + Math.cos(rad)*half, H/2 + Math.sin(rad)*half);
            grad.addColorStop(0, a);
            grad.addColorStop(1, b);
          }
          ctx.fillStyle = grad;
          ctx.fillRect(0, 0, W, H);
        }

        // 2. Overlay
        const oMode = el.overlayMode.value;
        const op    = Number(el.overlayOpacity.value) / 100;
        if (op > 0) {
          if      (oMode === "dark")  ctx.fillStyle = `rgba(0,0,0,${op})`;
          else if (oMode === "light") ctx.fillStyle = `rgba(255,255,255,${op})`;
          else if (oMode === "none")  ctx.fillStyle = `rgba(0,0,0,${op})`; // Fallback dunkel wie in Vorschau
          else ctx.fillStyle = null;
          if (ctx.fillStyle) ctx.fillRect(0, 0, W, H);
        }

        // 3. Texthilfe mit Zeilenumbruch
        function drawText(text, x, y, maxW, font, lineH, color, align, shadowAlpha) {
          ctx.font = font;
          ctx.fillStyle = color;
          ctx.textAlign = align;
          ctx.textBaseline = "top";
          ctx.shadowColor = shadowAlpha > 0 ? `rgba(0,0,0,${shadowAlpha})` : "transparent";
          ctx.shadowBlur = shadowAlpha > 0 ? 28 : 0;
          ctx.shadowOffsetY = shadowAlpha > 0 ? 6 : 0;
          const lines = [];
          text.split("\n").forEach(para => {
            let cur = "";
            para.split(" ").forEach(w => {
              const test = cur ? cur + " " + w : w;
              if (ctx.measureText(test).width > maxW && cur) {
                lines.push(cur);
                cur = w;
              } else {
                cur = test;
              }
            });
            lines.push(cur);
          });
          const totalH = lines.length * lineH;
          let cy = y - totalH / 2;
          lines.forEach(line => {
            ctx.fillText(line, x, cy);
            cy += lineH;
          });
          return totalH;
        }

        const padding = Math.round(Math.max(52, Math.min(128, Math.min(W, H) * 0.09)));
        const fontSize = parseInt(el.fontSizeRange.value) || 84;
        const fontFam = el.fontSelect.value;
        const weight = el.textWeight.value;
        const lineH = Math.round(fontSize * (Number(el.lineHeightRange.value) / 100));
        const maxW = W - padding * 2;
        const shadowA = Number(el.shadowStrength.value) / 100;
        ctx.letterSpacing = el.letterSpacing.value + "px";

        // 4. Haupttext
        const textX = currentTextAlign === "left" ? padding : currentTextAlign === "right" ? W - padding : W / 2;
        const textH = drawText(el.quoteInput.value || "Dein Zitat", textX, H / 2,
          maxW, `${weight} ${fontSize}px ${fontFam}`, lineH, el.textColor.value, currentTextAlign, shadowA);

        // 5. Autor
        const authorRaw = el.authorInput.value.trim();
        if (authorRaw) {
          const aSize = Math.round(fontSize * 0.34);
          const aText = el.authorPrefix.value + authorRaw;
          const aY = H / 2 + textH / 2 + Math.round(fontSize * 0.5);
          const cls = el.authorPreview.className;
          const hA = cls.includes("author-left") ? "left" : cls.includes("author-right") ? "right" : "center";
          const aX = hA === "left" ? padding : hA === "right" ? W - padding : W / 2;
          ctx.letterSpacing = "0px";
          drawText(aText, aX, aY, maxW, `700 ${aSize}px ${fontFam}`,
            Math.round(aSize * 1.4), el.authorColor.value, hA, shadowA * 0.7);
        }

        // 6. Wasserzeichen
        ctx.shadowColor = "transparent";
        ctx.shadowBlur = 0;
        ctx.shadowOffsetY = 0;
        if (el.watermarkToggle.checked && el.watermarkText.value.trim()) {
          const wmSize = Number(el.watermarkSize.value);
          const wmText = el.watermarkText.value;
          ctx.letterSpacing = "0px";
          ctx.font = `800 ${wmSize}px ${fontFam}`;
          ctx.fillStyle = el.watermarkColor.value;
          ctx.globalAlpha = Number(el.watermarkOpacity.value) / 100;
          const posClass = el.watermarkPreview.className;
          if (posClass.includes("wm-center")) {
            ctx.save();
            ctx.translate(W / 2, H / 2);
            ctx.rotate(-18 * Math.PI / 180);
            ctx.font = `800 ${wmSize*2.2}px ${fontFam}`;
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(wmText, 0, 0);
            ctx.restore();
          } else {
            let wx, wy, wa;
            if (posClass.includes("wm-bottom-right")) {
              wx = W - 54;
              wy = H - 46 - wmSize;
              wa = "right";
            } else if (posClass.includes("wm-bottom-left")) {
              wx = 54;
              wy = H - 46 - wmSize;
              wa = "left";
            } else if (posClass.includes("wm-top-right")) {
              wx = W - 54;
              wy = 46;
              wa = "right";
            } else {
              wx = 54;
              wy = 46;
              wa = "left";
            }
            ctx.textAlign = wa;
            ctx.textBaseline = "top";
            ctx.fillText(wmText, wx, wy);
          }
          ctx.globalAlpha = 1;
        }

        // 7. Download
        const mime = format === "png" ? "image/png" : "image/jpeg";
        const ext = format === "png" ? "png" : "jpg";
        const now   = new Date();
        const dd    = String(now.getDate()).padStart(2,'0');
        const mm    = String(now.getMonth()+1).padStart(2,'0');
        const yyyy  = now.getFullYear();
        const fname = `bobaquote-${W}x${H}-${dd}-${mm}-${yyyy}.${ext}`;
        c.toBlob(blob => {
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = fname;
          a.style.display = "none";
          document.body.appendChild(a);
          a.click();
          setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
          }, 2000);
        }, mime, format === "jpg" ? 0.92 : undefined);
      }

      el.downloadPng.addEventListener("click", () => exportImage("png"));
      el.downloadJpg.addEventListener("click", () => exportImage("jpg"));

      renderSwatches();
      setFormat("1080x1080");
      // stage-holder initial korrekt setzen
      const initHolder = document.querySelector(".stage-holder");
      const initScale = calcScale();
      if (initHolder) {
        initHolder.style.width = `${currentFormat.w * initScale}px`;
        initHolder.style.height = `${currentFormat.h * initScale}px`;
      }
      setAuthorPosition("author-right");
      setWatermarkPosition("wm-bottom-right");
      updateAll();
    });
  </script>
</body>

</html>
