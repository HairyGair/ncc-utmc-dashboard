<?php require_once __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>UTMC Dashboards — Newcastle City Council</title>
<style>
  :root {
    --navy: #1B2A4A;
    --navy-dark: #111E35;
    --navy-light: #2A3F6B;
    --accent: #2E7DCC;
    --accent-light: #E8F2FB;
    --bg: #F0F2F5;
    --white: #FFFFFF;
    --border: #DDE1E9;
    --text: #1A1A2E;
    --text-mid: #4A5568;
    --text-light: #718096;
    --green: #1A7F4B;
    --green-bg: #EAF7F0;
    --amber: #92400E;
    --amber-bg: #FFFBEB;
    --amber-border: #FDE68A;
    --red: #B91C1C;
    --red-bg: #FEF2F2;
    --red-border: #FECACA;
    --radius: 12px;
    --radius-sm: 8px;
    --shadow: 0 2px 8px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    font-size: 14px;
    line-height: 1.5;
  }

  /* ── HEADER ── */
  .header {
    background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 50%, var(--navy-light) 100%);
    color: white;
    padding: 20px 24px 18px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
  }

  .header-top {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .ncc-badge {
    flex-shrink: 0;
    background: white;
    padding: 6px 10px;
    border-radius: var(--radius-sm);
    line-height: 1;
  }

  .ncc-badge-text {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.06em;
    color: var(--navy-dark);
    text-transform: uppercase;
    line-height: 1.2;
  }

  .ncc-badge-sub {
    display: block;
    font-size: 9px;
    font-weight: 500;
    letter-spacing: 0.04em;
    color: var(--text-mid);
    text-transform: uppercase;
    margin-top: 1px;
  }

  .header-titles { flex: 1; }

  .header-titles h1 {
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.2;
  }

  .header-titles p {
    font-size: 0.8rem;
    opacity: 0.75;
    margin-top: 3px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
  }

  /* ── MAIN CONTENT ── */
  .main {
    max-width: 860px;
    margin: 0 auto;
    padding: 36px 20px 60px;
  }

  .section-label {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--text-light);
    margin-bottom: 16px;
  }

  /* ── CARD GRID ── */
  .card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
  }

  /* ── DASHBOARD CARD ── */
  .dash-card {
    display: block;
    text-decoration: none;
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    border-left-width: 4px;
    padding: 22px 20px 20px;
    transition: box-shadow 0.18s ease, transform 0.18s ease;
    position: relative;
    overflow: hidden;
  }

  .dash-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
  }

  .dash-card:focus-visible {
    outline: 3px solid var(--accent);
    outline-offset: 2px;
  }

  /* Theme: amber (Warning Tracker) */
  .dash-card--amber {
    border-left-color: #D97706;
  }

  .dash-card--amber .card-icon {
    background: var(--amber-bg);
    color: #D97706;
    border: 1px solid var(--amber-border);
  }

  .dash-card--amber .card-tag {
    background: var(--amber-bg);
    color: var(--amber);
    border: 1px solid var(--amber-border);
  }

  /* Theme: accent/blue (Router Installations) */
  .dash-card--accent {
    border-left-color: var(--accent);
  }

  .dash-card--accent .card-icon {
    background: var(--accent-light);
    color: var(--accent);
    border: 1px solid #B8D8F0;
  }

  .dash-card--accent .card-tag {
    background: var(--accent-light);
    color: #1A5D9A;
    border: 1px solid #B8D8F0;
  }

  /* Theme: red (Site Visits Offline) */
  .dash-card--red {
    border-left-color: var(--red);
  }

  .dash-card--red .card-icon {
    background: var(--red-bg);
    color: var(--red);
    border: 1px solid var(--red-border);
  }

  .dash-card--red .card-tag {
    background: var(--red-bg);
    color: var(--red);
    border: 1px solid var(--red-border);
  }

  .card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
  }

  .card-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .card-icon svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
  }

  .card-tag {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 4px;
    white-space: nowrap;
    margin-top: 2px;
  }

  .card-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
    letter-spacing: -0.01em;
    line-height: 1.25;
    margin-bottom: 6px;
  }

  .card-desc {
    font-size: 0.82rem;
    color: var(--text-mid);
    line-height: 1.5;
    margin-bottom: 18px;
  }

  .card-footer {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-light);
  }

  .card-footer svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
  }

  .dash-card:hover .card-footer {
    color: var(--text-mid);
  }

  /* ── FOOTER ── */
  .page-footer {
    text-align: center;
    padding: 0 20px 32px;
    font-size: 0.75rem;
    color: var(--text-light);
  }

  /* ── RESPONSIVE ── */
  @media (max-width: 520px) {
    .header { padding: 16px 16px 14px; }
    .header-titles h1 { font-size: 1.1rem; }
    .main { padding: 24px 16px 48px; }
    .card-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<header class="header" role="banner">
  <div class="header-top">
    <div class="ncc-badge" aria-hidden="true">
      <span class="ncc-badge-text">NCC</span>
      <span class="ncc-badge-sub">Traffic</span>
    </div>
    <div class="header-titles">
      <h1>UTMC Dashboards</h1>
      <p>Newcastle City Council &mdash; Traffic Signal Monitoring</p>
    </div>
  </div>
</header>

<main class="main" role="main">
  <p class="section-label">Select a dashboard</p>

  <nav class="card-grid" aria-label="Dashboard pages">

    <!-- Warning Tracker -->
    <a href="warnings.php" class="dash-card dash-card--amber" aria-label="Warning Tracker — Online sites with active warnings and alerts">
      <div class="card-header">
        <div class="card-icon" aria-hidden="true">
          <!-- Warning / alert triangle icon -->
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </div>
        <span class="card-tag">Warnings</span>
      </div>
      <div class="card-title">Warning Tracker</div>
      <div class="card-desc">Online sites with active warnings and alerts</div>
      <div class="card-footer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
        Open dashboard
      </div>
    </a>

    <!-- Router Installations -->
    <a href="router-installations.php" class="dash-card dash-card--accent" aria-label="Router Installations — UTMC router deployment and installation tracking">
      <div class="card-header">
        <div class="card-icon" aria-hidden="true">
          <!-- Network / router icon -->
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="14" width="20" height="6" rx="2"/>
            <path d="M6 14V9a6 6 0 0 1 12 0v5"/>
            <circle cx="12" cy="17" r="1" fill="currentColor" stroke="none"/>
            <line x1="18" y1="17" x2="18" y2="17.01"/>
            <line x1="6" y1="17" x2="6" y2="17.01"/>
          </svg>
        </div>
        <span class="card-tag">Installations</span>
      </div>
      <div class="card-title">Router Installations</div>
      <div class="card-desc">UTMC router deployment and installation tracking</div>
      <div class="card-footer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
        Open dashboard
      </div>
    </a>

    <!-- Site Visits — Offline -->
    <a href="site-visits-offline.php" class="dash-card dash-card--red" aria-label="Site Visits Offline — Offline site visit reports and status">
      <div class="card-header">
        <div class="card-icon" aria-hidden="true">
          <!-- Clipboard / site visit icon -->
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
            <line x1="9" y1="12" x2="15" y2="12"/>
            <line x1="9" y1="16" x2="13" y2="16"/>
          </svg>
        </div>
        <span class="card-tag">Offline</span>
      </div>
      <div class="card-title">Site Visits &mdash; Offline</div>
      <div class="card-desc">Offline site visit reports and status</div>
      <div class="card-footer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
        Open dashboard
      </div>
    </a>

  </nav>
</main>

<footer class="page-footer" role="contentinfo">
  Newcastle City Council &mdash; UTMC Traffic Signal Monitoring
</footer>

</body>
</html>
