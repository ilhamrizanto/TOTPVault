<?php
// templates/landing.php — Apple-Grade Product Showcase & Zero-Trust Access Portal
$pageTitle = 'TOTPVault — Brankas 2FA Terpusat & Kriptografi Server-Side';
$cfg         = require __DIR__ . '/../config/config.php';
$oauth       = $cfg['oauth'] ?? [];
$appEnv      = $cfg['app_env'] ?? 'production';
$googleOn    = !empty($oauth['google']['client_id']);
$microsoftOn = !empty($oauth['microsoft']['client_id']);

require __DIR__ . '/layout.php';
?>

<style>
/* ─────────────────────────────────────────────────────────────
   APPLE-DESIGN PRODUCT SHOWCASE — PARSIMONY & RESTRAINT
   Zero neon slop, crisp 1px hairlines, tabular-nums, fluid UI
───────────────────────────────────────────────────────────── */

.lp-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--canvas);
  color: var(--ink);
  position: relative;
}

.lp-container {
  width: 100%;
  max-width: 1080px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* ── Translucent Top Navigation Bar ── */
.lp-nav {
  position: sticky;
  top: 0;
  z-index: 100;
  height: 56px;
  background: rgba(9, 9, 11, 0.78);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border-bottom: 1px solid var(--border);
}

.lp-nav-inner {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.lp-brand {
  display: inline-flex;
  align-items: center;
  gap: .625rem;
  font-weight: 700;
  font-size: .9375rem;
  letter-spacing: -0.02em;
  color: var(--ink);
}

.lp-brand img {
  width: 24px;
  height: 24px;
  object-fit: contain;
}

.lp-brand-tag {
  font-family: 'JetBrains Mono', monospace;
  font-size: .6875rem;
  font-weight: 500;
  color: var(--ink-3);
  padding: .1rem .45rem;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
}

.lp-nav-links {
  display: flex;
  align-items: center;
  gap: 1.75rem;
}

.lp-nav-link {
  font-size: .8125rem;
  font-weight: 500;
  color: var(--ink-3);
  transition: color 150ms ease;
}

@media (hover: hover) and (pointer: fine) {
  .lp-nav-link:hover {
    color: var(--ink);
  }
}

.lp-nav-actions {
  display: flex;
  align-items: center;
  gap: .625rem;
}

@media (max-width: 768px) {
  .lp-nav-links { display: none; }
}

/* ── Hero Section ── */
.lp-hero {
  padding: 5rem 0 3.5rem;
  text-align: center;
}

.lp-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .25rem .75rem;
  border-radius: var(--r-pill);
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border);
  font-size: .75rem;
  font-weight: 500;
  color: var(--ink-2);
  margin-bottom: 1.5rem;
}

.lp-eyebrow-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--emerald);
}

.lp-title {
  font-size: clamp(2.15rem, 4.6vw, 3.5rem);
  font-weight: 700;
  letter-spacing: -0.035em;
  line-height: 1.08;
  color: var(--ink);
  max-width: 780px;
  margin: 0 auto 1.25rem;
}

.lp-subtitle {
  font-size: clamp(.95rem, 1.5vw, 1.125rem);
  color: var(--ink-3);
  max-width: 600px;
  margin: 0 auto 2rem;
  line-height: 1.6;
}

.lp-hero-cta {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .75rem;
  flex-wrap: wrap;
  margin-bottom: 3.5rem;
}

/* ── Interactive macOS Workstation Window Preview ── */
.lp-window {
  background: var(--card);
  border: 1px solid rgba(255, 255, 255, 0.09);
  border-radius: 16px;
  box-shadow: var(--shadow-lg), var(--specular-top);
  overflow: hidden;
  text-align: left;
}

.lp-window-bar {
  height: 42px;
  padding: 0 1rem;
  background: #0d0d11;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.lp-window-dots {
  display: flex;
  align-items: center;
  gap: 7px;
}

.lp-window-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.14);
}

.lp-window-title {
  font-family: 'JetBrains Mono', monospace;
  font-size: .72rem;
  color: var(--ink-3);
  display: flex;
  align-items: center;
  gap: .45rem;
}

.lp-window-status {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  font-family: 'JetBrains Mono', monospace;
  font-size: .72rem;
  color: var(--ink-2);
}

.lp-window-body {
  display: grid;
  grid-template-columns: 210px 1fr;
  min-height: 360px;
}

@media (max-width: 820px) {
  .lp-window-body {
    grid-template-columns: 1fr;
  }
  .lp-mock-sidebar {
    display: none;
  }
}

.lp-mock-sidebar {
  background: #0c0c10;
  border-right: 1px solid var(--border);
  padding: 1rem .75rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.lp-mock-nav-group {
  display: flex;
  flex-direction: column;
  gap: .25rem;
}

.lp-mock-nav-label {
  font-size: .65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--ink-4);
  padding: .25rem .5rem;
}

.lp-mock-nav-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .45rem .6rem;
  border-radius: 8px;
  font-size: .78125rem;
  font-weight: 500;
  color: var(--ink-3);
  cursor: pointer;
  border: 1px solid transparent;
  background: transparent;
  width: 100%;
  text-align: left;
  transition: background-color 140ms ease, color 140ms ease;
}

.lp-mock-nav-item.active {
  background: rgba(255, 255, 255, 0.06);
  color: var(--ink);
  border-color: var(--border);
}

.lp-mock-canvas {
  padding: 1.25rem;
  background: var(--canvas);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.lp-mock-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .75rem;
  flex-wrap: wrap;
}

.lp-mock-search {
  display: flex;
  align-items: center;
  gap: .5rem;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0 .75rem;
  height: 34px;
  flex: 1;
  min-width: 200px;
  max-width: 320px;
}

.lp-mock-search input {
  background: transparent;
  border: none;
  outline: none;
  color: var(--ink);
  font-size: .78125rem;
  width: 100%;
}

.lp-mock-kbd {
  font-family: 'JetBrains Mono', monospace;
  font-size: .65rem;
  color: var(--ink-4);
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 1px 5px;
}

.lp-mock-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: .75rem;
}

/* ── Parsimonious Preview Token Card ── */
.lp-token-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: .9rem 1rem;
  box-shadow: var(--specular-card);
  cursor: pointer;
  user-select: none;
  display: flex;
  flex-direction: column;
  gap: .75rem;
  transition: transform 150ms var(--ease-out), border-color 150ms ease, background-color 150ms ease;
}

@media (hover: hover) and (pointer: fine) {
  .lp-token-card:hover {
    background: var(--card-hover);
    border-color: var(--border-hover);
  }
}

.lp-token-card:active {
  transform: scale(0.98);
}

.lp-token-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .625rem;
}

.lp-token-id {
  display: flex;
  align-items: center;
  gap: .625rem;
  min-width: 0;
}

.lp-token-icon {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--ink);
  font-size: .9rem;
  flex-shrink: 0;
}

.lp-token-name {
  font-size: .8125rem;
  font-weight: 600;
  color: var(--ink);
  line-height: 1.25;
}

.lp-token-issuer {
  font-size: .7rem;
  color: var(--ink-3);
  line-height: 1.25;
}

.lp-token-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: .625rem;
  border-top: 1px solid var(--border-sub);
}

.lp-token-code {
  font-family: 'JetBrains Mono', monospace;
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: var(--ink);
  font-variant-numeric: tabular-nums;
}

.lp-token-CopyState {
  font-size: .72rem;
  font-weight: 600;
  color: var(--emerald-light);
  display: none;
  align-items: center;
  gap: .3rem;
}

.lp-token-card.copied {
  border-color: var(--emerald-border);
}
.lp-token-card.copied .lp-token-code {
  display: none;
}
.lp-token-card.copied .lp-token-CopyState {
  display: inline-flex;
}

/* ── Section Layout ── */
.lp-section {
  padding: 4.5rem 0;
  border-top: 1px solid var(--border-sub);
}

.lp-section-header {
  max-width: 600px;
  margin-bottom: 2.5rem;
}

.lp-section-kicker {
  font-family: 'JetBrains Mono', monospace;
  font-size: .72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--emerald-light);
  margin-bottom: .5rem;
}

.lp-section-title {
  font-size: clamp(1.5rem, 2.8vw, 2rem);
  font-weight: 700;
  letter-spacing: -0.03em;
  color: var(--ink);
  margin-bottom: .625rem;
}

.lp-section-desc {
  font-size: .9375rem;
  color: var(--ink-3);
  line-height: 1.6;
}

/* ── Bento Architecture Grid ── */
.lp-bento {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

@media (max-width: 860px) {
  .lp-bento {
    grid-template-columns: 1fr;
  }
}

.lp-bento-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--r-card);
  padding: 1.5rem;
  box-shadow: var(--specular-card);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 1rem;
}

.lp-bento-icon {
  width: 36px;
  height: 36px;
  border-radius: 9px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--ink-2);
  font-size: .9rem;
  margin-bottom: .25rem;
}

.lp-bento-title {
  font-size: 1rem;
  font-weight: 650;
  color: var(--ink);
  letter-spacing: -0.015em;
  margin-bottom: .375rem;
}

.lp-bento-text {
  font-size: .84375rem;
  color: var(--ink-3);
  line-height: 1.55;
}

.lp-bento-meta {
  font-family: 'JetBrains Mono', monospace;
  font-size: .72rem;
  color: var(--ink-4);
  padding-top: .75rem;
  border-top: 1px solid var(--border-sub);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* ── Spec Sheet & Login Split Section ── */
.lp-access-grid {
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 2rem;
  align-items: start;
}

@media (max-width: 860px) {
  .lp-access-grid {
    grid-template-columns: 1fr;
  }
}

.lp-spec-table {
  width: 100%;
  border-collapse: collapse;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--r-card);
  overflow: hidden;
}

.lp-spec-table tr {
  border-bottom: 1px solid var(--border-sub);
}

.lp-spec-table tr:last-child {
  border-bottom: none;
}

.lp-spec-table th {
  text-align: left;
  padding: .85rem 1.15rem;
  font-size: .78125rem;
  font-weight: 500;
  color: var(--ink-3);
  width: 42%;
  background: rgba(255, 255, 255, 0.01);
}

.lp-spec-table td {
  padding: .85rem 1.15rem;
  font-family: 'JetBrains Mono', monospace;
  font-size: .78125rem;
  color: var(--ink);
}

.lp-auth-card {
  background: var(--card);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 18px;
  padding: 1.75rem;
  box-shadow: var(--shadow-md), var(--specular-top);
}

.lp-auth-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  height: 44px;
  padding: 0 1rem;
  border-radius: 10px;
  background: #18181f;
  border: 1px solid var(--border);
  color: var(--ink);
  font-size: .84375rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 150ms var(--ease-out), background-color 150ms ease, border-color 150ms ease;
}

@media (hover: hover) and (pointer: fine) {
  .lp-auth-btn:hover {
    background: #202029;
    border-color: var(--border-hover);
  }
}

.lp-auth-btn:active {
  transform: scale(0.98);
}

.lp-auth-btn.primary {
  background: var(--ink);
  color: #09090b;
  border-color: #ffffff;
}

@media (hover: hover) and (pointer: fine) {
  .lp-auth-btn.primary:hover {
    background: #e4e4e7;
  }
}

/* ── Footer ── */
.lp-footer {
  margin-top: auto;
  padding: 2rem 0;
  border-top: 1px solid var(--border-sub);
  font-size: .78125rem;
  color: var(--ink-4);
}

.lp-footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}
</style>

<div class="lp-shell">

  <!-- Top Navigation -->
  <header class="lp-nav">
    <div class="lp-container lp-nav-inner">
      <a href="/" class="lp-brand">
        <img src="/logo.png" alt="TOTPVault Logo">
        <span>TOTPVault</span>
        <span class="lp-brand-tag">v2.4</span>
      </a>

      <nav class="lp-nav-links" aria-label="Navigasi Utama">
        <a href="#preview" class="lp-nav-link">Ruang Kerja</a>
        <a href="#architecture" class="lp-nav-link">Arsitektur</a>
        <a href="#specs" class="lp-nav-link">Spesifikasi</a>
        <a href="https://github.com/ilhamrizanto/TOTPVault" target="_blank" rel="noopener" class="lp-nav-link">GitHub</a>
      </nav>

      <div class="lp-nav-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAuthSheet()">
          <span>Masuk ke Brankas</span>
          <i class="fa-solid fa-arrow-right" style="font-size:.68rem"></i>
        </button>
      </div>
    </div>
  </header>

  <main>
    <!-- Hero Section -->
    <section class="lp-hero">
      <div class="lp-container">
        <div class="lp-eyebrow">
          <span class="lp-eyebrow-dot"></span>
          <span>RFC 6238 Server-Side TOTP &middot; AES-256-GCM Vault</span>
        </div>

        <h1 class="lp-title">
          Otentikasi dua faktor tim, dirancang dengan presisi.
        </h1>

        <p class="lp-subtitle">
          Brankas 2FA mandiri untuk tim engineering dan operasional. Kunci rahasia dienkripsi di server dengan AES-256-GCM—hanya kode 6-digit sesaat yang dikirim ke layar Anda.
        </p>

        <div class="lp-hero-cta">
          <button type="button" class="btn btn-primary btn-lg" onclick="openAuthSheet()">
            <i class="fa-solid fa-key" style="font-size:.8rem"></i>
            <span>Buka Brankas</span>
          </button>
          <a href="#architecture" class="btn btn-secondary btn-lg">
            <span>Pelajari Kriptografi</span>
          </a>
        </div>

        <!-- Interactive Live Workstation Preview -->
        <div id="preview" class="lp-window">
          <div class="lp-window-bar">
            <div class="lp-window-dots">
              <span class="lp-window-dot"></span>
              <span class="lp-window-dot"></span>
              <span class="lp-window-dot"></span>
            </div>
            <div class="lp-window-title">
              <i class="fa-solid fa-lock" style="font-size:.65rem;color:var(--emerald)"></i>
              <span>vault.internal / tokens</span>
            </div>
            <div class="lp-window-status">
              <span>Siklus</span>
              <span id="lp-live-timer" style="color:var(--emerald-light);font-weight:700">30s</span>
            </div>
          </div>

          <div class="lp-window-body">
            <!-- Mock Sidebar -->
            <div class="lp-mock-sidebar">
              <div class="lp-mock-nav-group">
                <div class="lp-mock-nav-label">Ruang Kerja</div>
                <button type="button" class="lp-mock-nav-item active" onclick="filterMockTokens('all', this)">
                  <span><i class="fa-solid fa-key" style="width:18px"></i> Semua Token</span>
                  <span class="mono" style="font-size:.7rem;color:var(--ink-3)">6</span>
                </button>
                <button type="button" class="lp-mock-nav-item" onclick="filterMockTokens('infra', this)">
                  <span><i class="fa-solid fa-server" style="width:18px"></i> Infrastruktur</span>
                  <span class="mono" style="font-size:.7rem;color:var(--ink-3)">3</span>
                </button>
                <button type="button" class="lp-mock-nav-item" onclick="filterMockTokens('shared', this)">
                  <span><i class="fa-solid fa-user-group" style="width:18px"></i> Dibagikan</span>
                  <span class="mono" style="font-size:.7rem;color:var(--ink-3)">3</span>
                </button>
              </div>

              <div style="padding:.6rem;border-radius:8px;background:rgba(255,255,255,0.02);border:1px solid var(--border-sub);font-size:.7rem;color:var(--ink-3);line-height:1.45">
                <div style="color:var(--ink-2);font-weight:600;margin-bottom:.15rem">Simulasi Interaktif</div>
                Klik kartu token mana pun untuk menyalin kode OTP.
              </div>
            </div>

            <!-- Mock Main Canvas -->
            <div class="lp-mock-canvas">
              <div class="lp-mock-toolbar">
                <div class="lp-mock-search">
                  <i class="fa-solid fa-magnifying-glass" style="font-size:.75rem;color:var(--ink-4)"></i>
                  <input type="text" id="lp-mock-search-input" placeholder="Cari layanan atau akun..." oninput="searchMockTokens(this.value)" aria-label="Cari token simulasi">
                  <span class="lp-mock-kbd">/</span>
                </div>
                <div style="font-size:.75rem;color:var(--ink-3)">
                  Klik kartu untuk menyalin
                </div>
              </div>

              <div class="lp-mock-grid" id="lp-mock-grid">
                <!-- Card 1 -->
                <div class="lp-token-card" data-cat="infra" data-search="aws production root amazon" onclick="copyMockCard(this)">
                  <div class="lp-token-top">
                    <div class="lp-token-id">
                      <div class="lp-token-icon"><i class="fa-brands fa-aws"></i></div>
                      <div class="truncate">
                        <div class="lp-token-name truncate">AWS Root Console</div>
                        <div class="lp-token-issuer truncate">sec-ops@company.id</div>
                      </div>
                    </div>
                    <span class="badge badge-neutral">Infra</span>
                  </div>
                  <div class="lp-token-bottom">
                    <span class="lp-token-code" data-seed="492817">492 817</span>
                    <span class="lp-token-CopyState"><i class="fa-solid fa-check"></i> Tersalin</span>
                    <span class="mono" style="font-size:.7rem;color:var(--ink-4)">SHA1</span>
                  </div>
                </div>

                <!-- Card 2 -->
                <div class="lp-token-card" data-cat="infra" data-search="cloudflare zero trust dns" onclick="copyMockCard(this)">
                  <div class="lp-token-top">
                    <div class="lp-token-id">
                      <div class="lp-token-icon"><i class="fa-solid fa-cloud"></i></div>
                      <div class="truncate">
                        <div class="lp-token-name truncate">Cloudflare Zero Trust</div>
                        <div class="lp-token-issuer truncate">netadmin@company.id</div>
                      </div>
                    </div>
                    <span class="badge badge-neutral">Infra</span>
                  </div>
                  <div class="lp-token-bottom">
                    <span class="lp-token-code" data-seed="830194">830 194</span>
                    <span class="lp-token-CopyState"><i class="fa-solid fa-check"></i> Tersalin</span>
                    <span class="mono" style="font-size:.7rem;color:var(--ink-4)">SHA1</span>
                  </div>
                </div>

                <!-- Card 3 -->
                <div class="lp-token-card" data-cat="shared" data-search="github organization enterprise" onclick="copyMockCard(this)">
                  <div class="lp-token-top">
                    <div class="lp-token-id">
                      <div class="lp-token-icon"><i class="fa-brands fa-github"></i></div>
                      <div class="truncate">
                        <div class="lp-token-name truncate">GitHub Enterprise</div>
                        <div class="lp-token-issuer truncate">release-bot@company.id</div>
                      </div>
                    </div>
                    <span class="badge badge-green">Tim</span>
                  </div>
                  <div class="lp-token-bottom">
                    <span class="lp-token-code" data-seed="105729">105 729</span>
                    <span class="lp-token-CopyState"><i class="fa-solid fa-check"></i> Tersalin</span>
                    <span class="mono" style="font-size:.7rem;color:var(--ink-4)">SHA1</span>
                  </div>
                </div>

                <!-- Card 4 -->
                <div class="lp-token-card" data-cat="shared" data-search="stripe live treasury finance" onclick="copyMockCard(this)">
                  <div class="lp-token-top">
                    <div class="lp-token-id">
                      <div class="lp-token-icon"><i class="fa-brands fa-stripe-s"></i></div>
                      <div class="truncate">
                        <div class="lp-token-name truncate">Stripe Treasury</div>
                        <div class="lp-token-issuer truncate">finance@company.id</div>
                      </div>
                    </div>
                    <span class="badge badge-green">Tim</span>
                  </div>
                  <div class="lp-token-bottom">
                    <span class="lp-token-code" data-seed="674208">674 208</span>
                    <span class="lp-token-CopyState"><i class="fa-solid fa-check"></i> Tersalin</span>
                    <span class="mono" style="font-size:.7rem;color:var(--ink-4)">SHA256</span>
                  </div>
                </div>

                <!-- Card 5 -->
                <div class="lp-token-card" data-cat="infra" data-search="digitalocean dokploy cluster" onclick="copyMockCard(this)">
                  <div class="lp-token-top">
                    <div class="lp-token-id">
                      <div class="lp-token-icon"><i class="fa-solid fa-cubes"></i></div>
                      <div class="truncate">
                        <div class="lp-token-name truncate">Dokploy Production</div>
                        <div class="lp-token-issuer truncate">devops@company.id</div>
                      </div>
                    </div>
                    <span class="badge badge-neutral">Infra</span>
                  </div>
                  <div class="lp-token-bottom">
                    <span class="lp-token-code" data-seed="918342">918 342</span>
                    <span class="lp-token-CopyState"><i class="fa-solid fa-check"></i> Tersalin</span>
                    <span class="mono" style="font-size:.7rem;color:var(--ink-4)">SHA1</span>
                  </div>
                </div>

                <!-- Card 6 -->
                <div class="lp-token-card" data-cat="shared" data-search="google workspace superadmin" onclick="copyMockCard(this)">
                  <div class="lp-token-top">
                    <div class="lp-token-id">
                      <div class="lp-token-icon"><i class="fa-brands fa-google"></i></div>
                      <div class="truncate">
                        <div class="lp-token-name truncate">Google SuperAdmin</div>
                        <div class="lp-token-issuer truncate">it-admin@company.id</div>
                      </div>
                    </div>
                    <span class="badge badge-green">Tim</span>
                  </div>
                  <div class="lp-token-bottom">
                    <span class="lp-token-code" data-seed="351906">351 906</span>
                    <span class="lp-token-CopyState"><i class="fa-solid fa-check"></i> Tersalin</span>
                    <span class="mono" style="font-size:.7rem;color:var(--ink-4)">SHA1</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- Architecture Section -->
    <section id="architecture" class="lp-section">
      <div class="lp-container">
        <div class="lp-section-header">
          <div class="lp-section-kicker">Arsitektur Keamanan</div>
          <h2 class="lp-section-title">Kunci rahasia tidak pernah meninggalkan server.</h2>
          <p class="lp-section-desc">
            Aplikasi authenticator biasa mengekspor atau menyimpan Base32 secret di perangkat pengguna. TOTPVault mendekripsi seed hanya di memori RAM server saat menghitung kode OTP.
          </p>
        </div>

        <div class="lp-bento">
          <div class="lp-bento-card">
            <div>
              <div class="lp-bento-icon"><i class="fa-solid fa-shield-halved"></i></div>
              <h3 class="lp-bento-title">Enkripsi Authenticated AES-256-GCM</h3>
              <p class="lp-bento-text">
                Setiap secret TOTP dienkripsi dengan IV acak 96-bit unik dan tag autentikasi 128-bit. Manipulasi bit pada database akan langsung ditolak sebelum dekripsi.
              </p>
            </div>
            <div class="lp-bento-meta">
              <span>Cipher</span>
              <span style="color:var(--ink-2)">AES-256-GCM (OpenSSL)</span>
            </div>
          </div>

          <div class="lp-bento-card">
            <div>
              <div class="lp-bento-icon"><i class="fa-solid fa-user-check"></i></div>
              <h3 class="lp-bento-title">Zero-Trust Email Whitelist</h3>
              <p class="lp-bento-text">
                Autentikasi OAuth 2.0 melalui Google Workspace dan Microsoft Entra ID diverifikasi secara ketat terhadap daftar email yang telah diotorisasi oleh Administrator.
              </p>
            </div>
            <div class="lp-bento-meta">
              <span>Identitas</span>
              <span style="color:var(--ink-2)">OAuth 2.0 + Whitelist</span>
            </div>
          </div>

          <div class="lp-bento-card">
            <div>
              <div class="lp-bento-icon"><i class="fa-solid fa-sliders"></i></div>
              <h3 class="lp-bento-title">Delegasi Akses Granular</h3>
              <p class="lp-bento-text">
                Bagikan akses token ke anggota tim dengan izin <em>Read-Only</em> (hanya salin OTP) atau <em>Can Edit</em>, lengkap dengan matriks audit dan pencabutan akses seketika.
              </p>
            </div>
            <div class="lp-bento-meta">
              <span>Kontrol</span>
              <span style="color:var(--ink-2)">RBAC &amp; Matriks IAM</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Technical Specs & Access Section -->
    <section id="specs" class="lp-section">
      <div class="lp-container">
        <div class="lp-access-grid">
          <div>
            <div class="lp-section-kicker">Spesifikasi Teknis</div>
            <h2 class="lp-section-title" style="margin-bottom:1.25rem">Standar kriptografi terbuka.</h2>

            <table class="lp-spec-table">
              <tbody>
                <tr>
                  <th>Standar Algoritma OTP</th>
                  <td>RFC 6238 (TOTP) &middot; SHA1, SHA256, SHA512</td>
                </tr>
                <tr>
                  <th>Enkripsi Penyimpanan</th>
                  <td>AES-256-GCM (96-bit IV + 128-bit Auth Tag)</td>
                </tr>
                <tr>
                  <th>Panjang Digit &amp; Interval</th>
                  <td>6 atau 8 Digit &middot; Siklus 30s / 60s</td>
                </tr>
                <tr>
                  <th>Migrasi Massal</th>
                  <td>Decoder Protobuf QR Google Authenticator</td>
                </tr>
                <tr>
                  <th>Perlindungan Sesi</th>
                  <td>CSRF Token, HttpOnly Cookie, Rate Limiter</td>
                </tr>
                <tr>
                  <th>Lisensi &amp; Kepatuhan</th>
                  <td>GNU GPL v3.0 (Berbasis Token2 TOTPVault)</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Direct Sign-In Card -->
          <div class="lp-auth-card" id="access-portal">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
              <div style="display:flex;align-items:center;gap:.5rem">
                <img src="/logo.png" alt="TOTPVault" style="width:20px;height:20px;object-fit:contain">
                <span style="font-weight:700;font-size:.9375rem;color:var(--ink)">Masuk ke TOTPVault</span>
              </div>
              <span class="badge badge-green">Terenkripsi</span>
            </div>

            <p style="font-size:.8125rem;color:var(--ink-3);line-height:1.55;margin-bottom:1.25rem">
              Gunakan identitas organisasi Anda yang telah terdaftar di daftar Whitelist untuk membuka brankas token.
            </p>

            <div style="display:flex;flex-direction:column;gap:.625rem">
              <a href="/auth/login/google" class="lp-auth-btn primary">
                <span style="display:flex;align-items:center;gap:.625rem">
                  <i class="fa-brands fa-google"></i>
                  <span>Lanjutkan dengan Google</span>
                </span>
                <i class="fa-solid fa-arrow-right" style="font-size:.75rem;opacity:.7"></i>
              </a>

              <?php if ($microsoftOn): ?>
              <a href="/auth/login/microsoft" class="lp-auth-btn">
                <span style="display:flex;align-items:center;gap:.625rem">
                  <i class="fa-brands fa-microsoft"></i>
                  <span>Lanjutkan dengan Microsoft Entra ID</span>
                </span>
                <i class="fa-solid fa-arrow-right" style="font-size:.75rem;color:var(--ink-3)"></i>
              </a>
              <?php endif; ?>

              <?php if ($appEnv === 'development'): ?>
              <a href="/auth/dev-login" class="lp-auth-btn" style="border-style:dashed">
                <span style="display:flex;align-items:center;gap:.625rem">
                  <i class="fa-solid fa-terminal" style="color:var(--emerald)"></i>
                  <span>Masuk Sesi Development</span>
                </span>
                <span class="mono" style="font-size:.68rem;color:var(--emerald-light)">DEV</span>
              </a>
              <?php endif; ?>
            </div>

            <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border-sub);font-size:.75rem;color:var(--ink-4);line-height:1.5">
              Akses dibatasi secara eksklusif untuk akun yang telah diotorisasi oleh Administrator sistem.
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="lp-footer">
    <div class="lp-container lp-footer-inner">
      <div style="display:flex;align-items:center;gap:.5rem">
        <img src="/logo.png" alt="TOTPVault" style="width:16px;height:16px;object-fit:contain;opacity:.8">
        <span>TOTPVault &middot; Kriptografi 2FA Terpusat</span>
      </div>
      <div style="display:flex;align-items:center;gap:1.25rem">
        <a href="https://github.com/token2/TOTPVault" target="_blank" rel="noopener" style="color:var(--ink-3)">Hulu: Token2 Switzerland</a>
        <a href="https://github.com/ilhamrizanto/TOTPVault" target="_blank" rel="noopener" style="color:var(--ink-3)">Lisensi GPL-3.0</a>
      </div>
    </div>
  </footer>

</div>

<!-- Clean Sign-In Sheet Modal -->
<div id="lp-auth-modal" class="modal-backdrop hidden" onclick="if(event.target===this) closeAuthSheet()">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <div style="display:flex;align-items:center;gap:.6rem">
        <img src="/logo.png" alt="TOTPVault" style="width:22px;height:22px;object-fit:contain">
        <div>
          <div style="font-weight:700;font-size:.9375rem;color:var(--ink)">Otentikasi Brankas</div>
          <div style="font-size:.75rem;color:var(--ink-3)">Pilih penyedia identitas OAuth 2.0</div>
        </div>
      </div>
      <button type="button" class="btn btn-ghost btn-icon" onclick="closeAuthSheet()" aria-label="Tutup">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="modal-body" style="display:flex;flex-direction:column;gap:.625rem">
      <a href="/auth/login/google" class="lp-auth-btn primary">
        <span style="display:flex;align-items:center;gap:.625rem">
          <i class="fa-brands fa-google"></i>
          <span>Masuk dengan Google</span>
        </span>
        <i class="fa-solid fa-arrow-right" style="font-size:.75rem"></i>
      </a>

      <?php if ($microsoftOn): ?>
      <a href="/auth/login/microsoft" class="lp-auth-btn">
        <span style="display:flex;align-items:center;gap:.625rem">
          <i class="fa-brands fa-microsoft"></i>
          <span>Masuk dengan Microsoft</span>
        </span>
        <i class="fa-solid fa-arrow-right" style="font-size:.75rem;color:var(--ink-3)"></i>
      </a>
      <?php endif; ?>

      <?php if ($appEnv === 'development'): ?>
      <a href="/auth/dev-login" class="lp-auth-btn" style="border-style:dashed">
        <span style="display:flex;align-items:center;gap:.625rem">
          <i class="fa-solid fa-terminal" style="color:var(--emerald)"></i>
          <span>Sesi Development Lokal</span>
        </span>
        <span class="mono" style="font-size:.68rem;color:var(--emerald-light)">DEV</span>
      </a>
      <?php endif; ?>
    </div>

    <div class="modal-footer" style="justify-content:space-between">
      <span style="font-size:.72rem;color:var(--ink-4)">Zero-Trust Whitelist Enforcement</span>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeAuthSheet()">Tutup</button>
    </div>
  </div>
</div>

<script>
function openAuthSheet() {
  document.getElementById('lp-auth-modal')?.classList.remove('hidden');
}

function closeAuthSheet() {
  document.getElementById('lp-auth-modal')?.classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeAuthSheet();
  if (e.key === '/' && document.activeElement?.tagName !== 'INPUT') {
    e.preventDefault();
    document.getElementById('lp-mock-search-input')?.focus();
  }
});

function copyMockCard(card) {
  const codeEl = card.querySelector('.lp-token-code');
  const raw = (codeEl?.textContent || '').replace(/\s+/g, '');
  if (navigator.clipboard && raw) {
    navigator.clipboard.writeText(raw).catch(() => {});
  }
  card.classList.add('copied');
  setTimeout(() => card.classList.remove('copied'), 1200);
}

let activeMockCat = 'all';
function filterMockTokens(cat, btn) {
  activeMockCat = cat;
  document.querySelectorAll('.lp-mock-nav-item').forEach(el => el.classList.remove('active'));
  if (btn) btn.classList.add('active');
  applyMockFilters();
}

function searchMockTokens() {
  applyMockFilters();
}

function applyMockFilters() {
  const q = (document.getElementById('lp-mock-search-input')?.value || '').toLowerCase().trim();
  document.querySelectorAll('#lp-mock-grid .lp-token-card').forEach(card => {
    const matchCat = activeMockCat === 'all' || card.dataset.cat === activeMockCat;
    const matchQ = !q || (card.dataset.search || '').includes(q);
    card.style.display = (matchCat && matchQ) ? '' : 'none';
  });
}

// Live 30s cycle simulation for preview window
function tickMockVault() {
  const now = Math.floor(Date.now() / 1000);
  const rem = 30 - (now % 30);
  const timerEl = document.getElementById('lp-live-timer');
  if (timerEl) timerEl.textContent = `${rem}s`;

  if (rem === 30) {
    document.querySelectorAll('.lp-token-code').forEach((el, idx) => {
      const n = String((Math.floor(now / 30) * 13789 + (idx + 1) * 94321) % 900000 + 100000);
      el.textContent = n.slice(0, 3) + ' ' + n.slice(3);
    });
  }
}
setInterval(tickMockVault, 1000);
tickMockVault();
</script>
</body>
</html>
