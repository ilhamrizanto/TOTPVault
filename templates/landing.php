<?php
$pageTitle = 'TOTPVault — Brankas 2FA Terpusat & Kriptografi Server-Side';
$cfg       = require __DIR__ . '/../config/config.php';
$oauth     = $cfg['oauth'] ?? [];
$appEnv    = $cfg['app_env'] ?? 'production';
$googleOn  = !empty($oauth['google']['client_id']);
$microsoftOn = !empty($oauth['microsoft']['client_id']);

require __DIR__ . '/layout.php';
?>

<style>
/* ─────────────────────────────────────────────────────────────
   UNDERSTATED PRECISION — 1Password / Apple Security Craft
   Clean hairline borders, deep matte obsidian, zero AI slop
───────────────────────────────────────────────────────────── */

:root {
  --up-bg: #07080c;
  --up-surface: #0c0e14;
  --up-surface-elevated: #11141c;
  --up-surface-active: #171b26;
  --up-border: rgba(255, 255, 255, 0.08);
  --up-border-subtle: rgba(255, 255, 255, 0.04);
  --up-border-focus: rgba(16, 185, 129, 0.45);
  --up-text-primary: #f8fafc;
  --up-text-secondary: #94a3b8;
  --up-text-muted: #64748b;
  --up-accent: #10b981;
  --up-accent-dim: rgba(16, 185, 129, 0.12);
  --up-font-mono: 'JetBrains Mono', 'SF Mono', monospace;
}

body {
  background: var(--up-bg);
  color: var(--up-text-primary);
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  line-height: 1.5;
  margin: 0;
  padding: 0;
}

/* ── Container Layout ── */
.up-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  position: relative;
}

.up-container {
  max-width: 1120px;
  margin: 0 auto;
  padding: 0 1.5rem;
  width: 100%;
}

/* ── Precision Floating Island Navbar ── */
.up-navbar {
  position: fixed;
  top: 1.25rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  width: calc(100% - 2.5rem);
  max-width: 1040px;
  background: rgba(12, 14, 20, 0.78);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: var(--r-pill, 9999px);
  height: 58px;
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.8), 0 0 24px rgba(16, 185, 129, 0.08);
  transition: all .2s ease;
}

.up-nav-inner {
  max-width: 100%;
  padding: 0 1.25rem;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.up-brand {
  display: flex;
  align-items: center;
  gap: .75rem;
  text-decoration: none;
  color: var(--up-text-primary);
}

.up-brand-img {
  width: 32px;
  height: 32px;
  object-fit: contain;
  filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.4));
}

.up-brand-name {
  font-weight: 800;
  font-size: 1.05rem;
  letter-spacing: -0.03em;
  color: #ffffff;
}

.up-brand-spec {
  font-size: .7rem;
  color: var(--up-text-muted);
  border-left: 1px solid var(--up-border);
  padding-left: .65rem;
  font-weight: 500;
}

.up-nav-links {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.up-nav-link {
  color: var(--up-text-secondary);
  text-decoration: none;
  font-size: .84rem;
  font-weight: 500;
  transition: color .15s ease;
}

.up-nav-link:hover {
  color: #ffffff;
}

.up-nav-cta {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  background: #ffffff;
  color: #07080c;
  padding: .45rem .95rem;
  border-radius: 8px;
  font-size: .8125rem;
  font-weight: 700;
  text-decoration: none;
  transition: all .15s ease;
  border: 1px solid rgba(255, 255, 255, 0.2);
  cursor: pointer;
}

.up-nav-cta:hover {
  background: #e2e8f0;
  transform: translateY(-1px);
}

/* ── Hero Section ── */
.up-hero {
  padding: 8.5rem 0 3.5rem;
  text-align: center;
}

.up-tag-badge {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .3rem .75rem;
  border-radius: 9999px;
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.22);
  color: var(--up-accent);
  font-size: .75rem;
  font-weight: 600;
  letter-spacing: .03em;
  margin-bottom: 1.5rem;
}

.up-tag-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--up-accent);
  box-shadow: 0 0 8px var(--up-accent);
}

.up-hero-headline {
  font-size: clamp(2.25rem, 4.7vw, 3.75rem);
  font-weight: 800;
  letter-spacing: -0.04em;
  line-height: 1.12;
  color: #ffffff;
  max-width: 860px;
  margin: 0 auto 1.25rem;
}

.up-hero-headline span {
  color: var(--up-accent);
}

.up-hero-sub {
  font-size: clamp(1rem, 1.8vw, 1.15rem);
  color: var(--up-text-secondary);
  max-width: 680px;
  margin: 0 auto 2.25rem;
  line-height: 1.6;
}

.up-hero-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .875rem;
  margin-bottom: 3.5rem;
  flex-wrap: wrap;
}

.up-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: .55rem;
  background: var(--up-accent);
  color: #04140e;
  padding: .75rem 1.45rem;
  border-radius: 9px;
  font-size: .9rem;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition: all .15s ease;
  box-shadow: 0 0 20px rgba(16, 185, 129, 0.25);
}

.up-btn-primary:hover {
  background: #34d399;
  transform: translateY(-1px);
}

.up-btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: .55rem;
  background: var(--up-surface);
  color: #e2e8f0;
  border: 1px solid var(--up-border);
  padding: .75rem 1.45rem;
  border-radius: 9px;
  font-size: .9rem;
  font-weight: 600;
  text-decoration: none;
  transition: all .15s ease;
}

.up-btn-secondary:hover {
  background: var(--up-surface-elevated);
  border-color: rgba(255, 255, 255, 0.16);
  color: #ffffff;
}

/* ── Trust Metrics Ribbon ── */
.up-metrics-ribbon {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1px;
  background: var(--up-border);
  border: 1px solid var(--up-border);
  border-radius: 12px;
  overflow: hidden;
  max-width: 960px;
  margin: 0 auto;
}

.up-metric-card {
  background: var(--up-surface);
  padding: 1.25rem 1rem;
  text-align: center;
}

.up-metric-value {
  font-family: var(--up-font-mono);
  font-size: 1.35rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: .25rem;
  letter-spacing: -0.02em;
}

.up-metric-label {
  font-size: .75rem;
  color: var(--up-text-muted);
  font-weight: 500;
  line-height: 1.3;
}

/* ── Section: The Vault Workstation Simulator ── */
.up-simulator-section {
  padding: 4.5rem 0;
  border-top: 1px solid var(--up-border-subtle);
}

.up-section-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.up-section-tag {
  font-size: .75rem;
  font-weight: 700;
  color: var(--up-accent);
  text-transform: uppercase;
  letter-spacing: .08em;
  margin-bottom: .5rem;
}

.up-section-title {
  font-size: clamp(1.65rem, 3vw, 2.25rem);
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.03em;
  margin: 0 0 .65rem;
}

.up-section-desc {
  font-size: .95rem;
  color: var(--up-text-secondary);
  max-width: 580px;
  margin: 0 auto;
}

/* ── Interactive Window Mockup Frame ── */
.up-window-frame {
  background: #090b10;
  border: 1px solid var(--up-border);
  border-radius: 14px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.7);
  overflow: hidden;
  max-width: 1040px;
  margin: 0 auto;
}

.up-window-bar {
  background: #0d0f17;
  border-bottom: 1px solid var(--up-border);
  padding: .75rem 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.up-window-dots {
  display: flex;
  align-items: center;
  gap: 6px;
}

.up-window-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #2a2e3d;
}

.up-window-title {
  font-size: .75rem;
  font-family: var(--up-font-mono);
  color: var(--up-text-muted);
  display: flex;
  align-items: center;
  gap: .5rem;
}

.up-window-chip {
  font-size: .7rem;
  padding: .2rem .55rem;
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.2);
  color: var(--up-accent);
  border-radius: 9999px;
  font-family: var(--up-font-mono);
}

/* Toolbar inside simulator */
.up-sim-toolbar {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--up-border-subtle);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  background: #080a0e;
}

.up-sim-search {
  position: relative;
  min-width: 260px;
  flex: 1;
  max-width: 360px;
}

.up-sim-search input {
  width: 100%;
  background: #11141c;
  border: 1px solid var(--up-border);
  border-radius: 7px;
  padding: .5rem .85rem .5rem 2.2rem;
  color: #ffffff;
  font-size: .8125rem;
  outline: none;
  transition: border-color .15s ease;
}

.up-sim-search input:focus {
  border-color: var(--up-border-focus);
}

.up-sim-search i {
  position: absolute;
  left: .85rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--up-text-muted);
  font-size: .75rem;
}

.up-sim-filters {
  display: flex;
  align-items: center;
  gap: .4rem;
}

.up-sim-pill {
  padding: .35rem .75rem;
  border-radius: 6px;
  font-size: .75rem;
  font-weight: 600;
  background: #11141c;
  color: var(--up-text-secondary);
  border: 1px solid var(--up-border);
  cursor: pointer;
  transition: all .15s ease;
}

.up-sim-pill:hover,
.up-sim-pill.active {
  background: #182030;
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.18);
}

.up-sim-pill.active {
  border-color: var(--up-accent);
  color: var(--up-accent);
}

/* ── Token Grid Inside Simulator ── */
.up-sim-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  padding: 1.25rem;
  background: #06070a;
}

.up-token-card {
  background: #0c0e15;
  border: 1px solid var(--up-border);
  border-radius: 10px;
  padding: 1.15rem;
  display: flex;
  flex-direction: column;
  gap: .85rem;
  transition: border-color .15s ease;
}

.up-token-card:hover {
  border-color: rgba(255, 255, 255, 0.16);
}

.up-token-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.up-token-issuer-meta {
  display: flex;
  align-items: center;
  gap: .75rem;
}

.up-issuer-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: #151924;
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.up-issuer-name {
  font-size: .875rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: .15rem;
}

.up-account-label {
  font-size: .75rem;
  color: var(--up-text-muted);
  font-family: var(--up-font-mono);
}

.up-role-badge {
  font-size: .6875rem;
  padding: .2rem .5rem;
  border-radius: 4px;
  font-weight: 600;
  background: #141722;
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: var(--up-text-secondary);
}

.up-role-badge.can-edit {
  color: #38bdf8;
  border-color: rgba(56, 189, 248, 0.25);
  background: rgba(56, 189, 248, 0.08);
}

.up-token-body {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .75rem 1rem;
  background: #06070a;
  border: 1px solid var(--up-border-subtle);
  border-radius: 8px;
}

.up-otp-digits {
  font-family: var(--up-font-mono);
  font-size: 1.6rem;
  font-weight: 700;
  letter-spacing: .08em;
  color: #ffffff;
}

.up-timer-ring-box {
  display: flex;
  align-items: center;
  gap: .5rem;
}

.up-ring-svg {
  width: 24px;
  height: 24px;
  transform: rotate(-90deg);
}

.up-ring-bg {
  fill: none;
  stroke: rgba(255, 255, 255, 0.1);
  stroke-width: 3;
}

.up-ring-bar {
  fill: none;
  stroke: var(--up-accent);
  stroke-width: 3;
  stroke-linecap: round;
  transition: stroke-dashoffset .9s linear, stroke .3s ease;
}

.up-timer-sec {
  font-family: var(--up-font-mono);
  font-size: .78rem;
  font-weight: 700;
  color: var(--up-text-secondary);
}

.up-token-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: .72rem;
  color: var(--up-text-muted);
}

.up-copy-btn {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  padding: .35rem .75rem;
  background: #11141c;
  border: 1px solid var(--up-border);
  color: #cbd5e1;
  border-radius: 6px;
  font-size: .75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .15s ease;
}

.up-copy-btn:hover {
  background: #191e2b;
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.2);
}

.up-sim-footer-note {
  padding: .85rem 1.25rem;
  background: #090b10;
  border-top: 1px solid var(--up-border);
  font-size: .78rem;
  color: var(--up-text-muted);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .75rem;
}

/* ── Section: Why Server-Side Isolation Matters ── */
.up-why-section {
  padding: 5rem 0;
  border-top: 1px solid var(--up-border-subtle);
}

.up-pillars-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-top: 2.5rem;
}

.up-pillar-card {
  background: var(--up-surface);
  border: 1px solid var(--up-border);
  border-radius: 12px;
  padding: 1.75rem 1.5rem;
  display: flex;
  flex-direction: column;
}

.up-pillar-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: #121622;
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: var(--up-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  margin-bottom: 1.25rem;
}

.up-pillar-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 .65rem;
  letter-spacing: -0.02em;
}

.up-pillar-desc {
  font-size: .875rem;
  color: var(--up-text-secondary);
  line-height: 1.6;
  margin-bottom: 1.25rem;
  flex: 1;
}

.up-pillar-spec {
  padding-top: 1rem;
  border-top: 1px solid var(--up-border-subtle);
  font-family: var(--up-font-mono);
  font-size: .75rem;
  color: var(--up-accent);
  display: flex;
  align-items: center;
  gap: .4rem;
}

/* ── Section: Apple-Style Technical Specification Sheet ── */
.up-specs-section {
  padding: 5rem 0;
  border-top: 1px solid var(--up-border-subtle);
}

.up-specs-table {
  max-width: 860px;
  margin: 2.5rem auto 0;
  border: 1px solid var(--up-border);
  border-radius: 12px;
  overflow: hidden;
  background: var(--up-surface);
}

.up-spec-row {
  display: grid;
  grid-template-columns: 260px 1fr;
  padding: 1.15rem 1.5rem;
  border-bottom: 1px solid var(--up-border-subtle);
  align-items: center;
  font-size: .875rem;
}

.up-spec-row:last-child {
  border-bottom: none;
}

.up-spec-label {
  font-weight: 600;
  color: #cbd5e1;
}

.up-spec-value {
  color: var(--up-text-secondary);
  font-family: var(--up-font-mono);
  font-size: .8125rem;
}

.up-spec-value strong {
  color: #ffffff;
  font-weight: 600;
}

/* ── Section: Authentication Console (Dedicated & Clean) ── */
.up-auth-section {
  padding: 5rem 0 6rem;
  border-top: 1px solid var(--up-border-subtle);
}

.up-auth-card {
  max-width: 460px;
  margin: 0 auto;
  background: var(--up-surface);
  border: 1px solid var(--up-border);
  border-radius: 16px;
  padding: 2.25rem 2rem;
  box-shadow: 0 20px 48px rgba(0, 0, 0, 0.6);
  text-align: center;
}

.up-auth-lock-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: #11151f;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--up-accent);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  margin-bottom: 1rem;
}

.up-auth-title {
  font-size: 1.35rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0 0 .35rem;
  letter-spacing: -0.02em;
}

.up-auth-desc {
  font-size: .84rem;
  color: var(--up-text-secondary);
  margin: 0 0 1.5rem;
}

/* Provider buttons */
.up-oauth-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .75rem;
  width: 100%;
  height: 44px;
  border-radius: 8px;
  font-size: .875rem;
  font-weight: 600;
  text-decoration: none;
  margin-bottom: .75rem;
  transition: all .15s ease;
  box-sizing: border-box;
}

.up-google-btn {
  background: #161922;
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.up-google-btn:hover {
  background: #1f2330;
  border-color: rgba(255, 255, 255, 0.22);
}

.up-microsoft-btn {
  background: #161922;
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.up-microsoft-btn:hover {
  background: #1f2330;
  border-color: rgba(255, 255, 255, 0.22);
}

.up-auth-logo-img {
  width: 52px;
  height: 52px;
  object-fit: contain;
  margin-bottom: .85rem;
  filter: drop-shadow(0 0 16px rgba(16, 185, 129, 0.45));
  display: inline-block;
}

.up-auth-footer-notice {
  margin-top: 1.5rem;
  padding: .75rem;
  background: #08090d;
  border: 1px solid var(--up-border-subtle);
  border-radius: 8px;
  font-size: .75rem;
  color: var(--up-text-muted);
  line-height: 1.45;
}

/* ── Modal Dialog for Direct Navbar Login ── */
.up-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(3, 4, 7, 0.78);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  z-index: 1000;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.up-modal-backdrop.open {
  display: flex;
}

.up-modal-window {
  max-width: 440px;
  width: 100%;
  background: var(--up-surface);
  border: 1px solid var(--up-border);
  border-radius: 16px;
  padding: 2rem 1.75rem;
  position: relative;
  box-shadow: 0 32px 72px rgba(0, 0, 0, 0.9);
}

.up-modal-close {
  position: absolute;
  top: 1.15rem;
  right: 1.15rem;
  background: transparent;
  border: none;
  color: var(--up-text-muted);
  font-size: 1.1rem;
  cursor: pointer;
  padding: .35rem;
  transition: color .15s ease;
}

.up-modal-close:hover {
  color: #ffffff;
}

/* ── Footer ── */
.up-footer {
  border-top: 1px solid var(--up-border-subtle);
  padding: 3rem 0;
  background: #050609;
  font-size: .8125rem;
  color: var(--up-text-muted);
}

.up-footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.25rem;
}

.up-footer-links {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.up-footer-link {
  color: var(--up-text-muted);
  text-decoration: none;
  transition: color .15s ease;
}

.up-footer-link:hover {
  color: #ffffff;
}

/* Responsive adjustments */
@media (max-width: 860px) {
  .up-navbar {
    top: .75rem;
    width: calc(100% - 1.25rem);
    height: 52px;
    border-radius: 14px;
  }
  .up-nav-inner {
    padding: 0 .95rem;
  }
  .up-brand-spec {
    display: none;
  }
  .up-hero {
    padding: 6.5rem 0 2.5rem;
  }
  .up-metrics-ribbon {
    grid-template-columns: repeat(2, 1fr);
  }
  .up-sim-grid {
    grid-template-columns: 1fr;
  }
  .up-pillars-grid {
    grid-template-columns: 1fr;
  }
  .up-spec-row {
    grid-template-columns: 1fr;
    gap: .35rem;
  }
  .up-nav-links {
    display: none;
  }
}
</style>

<div class="up-page">

  <!-- ════ PRECISION TOP NAVIGATION ════ -->
  <header class="up-navbar">
    <div class="up-nav-inner">
      <a href="/" class="up-brand">
        <img src="/logo.png" alt="TOTPVault Logo" class="up-brand-img">
        <span class="up-brand-name">TOTPVault</span>
        <span class="up-brand-spec">Server-Side 2FA Enclave</span>
      </a>

      <nav class="up-nav-links">
        <a href="#vault-simulator" class="up-nav-link">Pratinjau Brankas</a>
        <a href="#arsitektur" class="up-nav-link">Mengapa Server-Side</a>
        <a href="#spesifikasi" class="up-nav-link">Spesifikasi Kriptografi</a>
        <a href="#auth-console" class="up-nav-link">Konsol Masuk</a>
      </nav>

      <div>
        <button type="button" class="up-nav-cta" onclick="openAuthModal()">
          <i class="fa-solid fa-lock"></i>
          <span>Masuk ke Brankas</span>
        </button>
      </div>
    </div>
  </header>

  <!-- ════ HERO SECTION ════ -->
  <section class="up-hero">
    <div class="up-container">
      <div class="up-tag-badge">
        <span class="up-tag-dot"></span>
        <span>KEAMANAN 2FA ENTERPRISE &middot; ZERO RAW SECRET EXPOSURE</span>
      </div>

      <h1 class="up-hero-headline">
        Kunci 2FA Tim Anda Tetap di Server.<br>
        <span>Bukan di Laptop atau HP Karyawan.</span>
      </h1>

      <p class="up-hero-sub">
        Platform brankas 2FA terpusat dengan enkripsi AES-256-GCM. Anggota tim hanya menerima kode 6 digit berdurasi 30 detik tanpa pernah melihat atau menyimpan master seed mentah.
      </p>

      <div class="up-hero-actions">
        <button type="button" class="up-btn-primary" onclick="openAuthModal()">
          <i class="fa-solid fa-arrow-right-to-bracket"></i>
          <span>Buka Brankas Tim</span>
        </button>
        <a href="#vault-simulator" class="up-btn-secondary">
          <i class="fa-solid fa-play"></i>
          <span>Uji Coba Simulator Vault</span>
        </a>
      </div>

      <!-- Trust Metrics Ribbon (Precision Style) -->
      <div class="up-metrics-ribbon">
        <div class="up-metric-card">
          <div class="up-metric-value">0.00</div>
          <div class="up-metric-label">Raw Seed Terekspos ke Klien</div>
        </div>
        <div class="up-metric-card">
          <div class="up-metric-value">256-bit</div>
          <div class="up-metric-label">Enkripsi Kunci AES-256-GCM</div>
        </div>
        <div class="up-metric-card">
          <div class="up-metric-value">1-Klik</div>
          <div class="up-metric-label">Pencabutan Hak Akses Tim</div>
        </div>
        <div class="up-metric-card">
          <div class="up-metric-value">RFC 6238</div>
          <div class="up-metric-label">Standar Kompatibilitas Global</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════ SECTION 1: THE AUTHENTIC VAULT SIMULATOR ════ -->
  <section class="up-simulator-section" id="vault-simulator">
    <div class="up-container">
      <div class="up-section-header">
        <div class="up-section-tag">Interactive Precision Preview</div>
        <h2 class="up-section-title">Pratinjau Antarmuka Brankas Nyata</h2>
        <p class="up-section-desc">
          Bukan mockup statis. Uji simulasi pencarian, pembagian kategori, dan penyalinan kode 2FA tersinkronisasi waktu nyata di bawah ini.
        </p>
      </div>

      <!-- Window Frame (macOS / Security Workstation) -->
      <div class="up-window-frame">
        <div class="up-window-bar">
          <div class="up-window-dots">
            <span class="up-window-dot" style="background:#ef4444"></span>
            <span class="up-window-dot" style="background:#f59e0b"></span>
            <span class="up-window-dot" style="background:#10b981"></span>
          </div>
          <div class="up-window-title">
            <img src="/logo.png" alt="TOTPVault" style="width:18px;height:18px;object-fit:contain;vertical-align:middle">
            <span>TOTPVault &mdash; Production Cluster (Live Demo)</span>
          </div>
          <div>
            <span class="up-window-chip"><i class="fa-solid fa-satellite-dish"></i> Sync: RFC 6238 Active</span>
          </div>
        </div>

        <!-- Simulator Toolbar (Search & Filter) -->
        <div class="up-sim-toolbar">
          <div class="up-sim-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="sim-search-input" placeholder="Cari nama token atau issuer..." oninput="handleSimFilter()">
          </div>

          <div class="up-sim-filters">
            <button type="button" class="up-sim-pill active" onclick="setSimCategory('all', this)">Semua (4)</button>
            <button type="button" class="up-sim-pill" onclick="setSimCategory('infra', this)">Infrastruktur (2)</button>
            <button type="button" class="up-sim-pill" onclick="setSimCategory('finance', this)">Finansial (1)</button>
            <button type="button" class="up-sim-pill" onclick="setSimCategory('security', this)">Keamanan (1)</button>
          </div>
        </div>

        <!-- Token Grid Inside Simulator (4 Real Realistic Tokens) -->
        <div class="up-sim-grid" id="sim-cards-grid">
          
          <!-- Token 1: AWS Production -->
          <div class="up-token-card" data-category="infra" data-search="aws amazon web services root infra">
            <div class="up-token-head">
              <div class="up-token-issuer-meta">
                <div class="up-issuer-icon" style="color:#ff9900">
                  <i class="fa-brands fa-aws"></i>
                </div>
                <div>
                  <div class="up-issuer-name">AWS Infrastructure</div>
                  <div class="up-account-label">prod-root-iam@company.corp</div>
                </div>
              </div>
              <span class="up-role-badge">Hanya Lihat (Read-Only)</span>
            </div>

            <div class="up-token-body">
              <div class="up-otp-digits" id="sim-code-1">738 109</div>
              <div class="up-timer-ring-box">
                <svg class="up-ring-svg" viewBox="0 0 32 32">
                  <circle class="up-ring-bg" cx="16" cy="16" r="13"></circle>
                  <circle class="up-ring-bar" id="sim-ring-1" cx="16" cy="16" r="13" stroke-dasharray="81.68" stroke-dashoffset="0"></circle>
                </svg>
                <span class="up-timer-sec" id="sim-sec-1">29s</span>
              </div>
            </div>

            <div class="up-token-foot">
              <span>Algoritma: HMAC-SHA1 &middot; 6-Digit</span>
              <button type="button" class="up-copy-btn" onclick="copySimToken('sim-code-1', 'AWS Infrastructure')">
                <i class="fa-regular fa-clone"></i>
                <span>Salin</span>
              </button>
            </div>
          </div>

          <!-- Token 2: GitHub Enterprise -->
          <div class="up-token-card" data-category="infra" data-search="github enterprise deployer bot devops">
            <div class="up-token-head">
              <div class="up-token-issuer-meta">
                <div class="up-issuer-icon" style="color:#ffffff">
                  <i class="fa-brands fa-github"></i>
                </div>
                <div>
                  <div class="up-issuer-name">GitHub Enterprise</div>
                  <div class="up-account-label">eng-lead@company.corp</div>
                </div>
              </div>
              <span class="up-role-badge can-edit">Boleh Edit (Can Edit)</span>
            </div>

            <div class="up-token-body">
              <div class="up-otp-digits" id="sim-code-2">419 652</div>
              <div class="up-timer-ring-box">
                <svg class="up-ring-svg" viewBox="0 0 32 32">
                  <circle class="up-ring-bg" cx="16" cy="16" r="13"></circle>
                  <circle class="up-ring-bar" id="sim-ring-2" cx="16" cy="16" r="13" stroke-dasharray="81.68" stroke-dashoffset="0"></circle>
                </svg>
                <span class="up-timer-sec" id="sim-sec-2">29s</span>
              </div>
            </div>

            <div class="up-token-foot">
              <span>Algoritma: HMAC-SHA1 &middot; 6-Digit</span>
              <button type="button" class="up-copy-btn" onclick="copySimToken('sim-code-2', 'GitHub Enterprise')">
                <i class="fa-regular fa-clone"></i>
                <span>Salin</span>
              </button>
            </div>
          </div>

          <!-- Token 3: Stripe Production -->
          <div class="up-token-card" data-category="finance" data-search="stripe payments finance billing portal">
            <div class="up-token-head">
              <div class="up-token-issuer-meta">
                <div class="up-issuer-icon" style="color:#6366f1">
                  <i class="fa-brands fa-stripe-s"></i>
                </div>
                <div>
                  <div class="up-issuer-name">Stripe Production</div>
                  <div class="up-account-label">billing-admin@company.corp</div>
                </div>
              </div>
              <span class="up-role-badge">Hanya Lihat (Read-Only)</span>
            </div>

            <div class="up-token-body">
              <div class="up-otp-digits" id="sim-code-3">802 431</div>
              <div class="up-timer-ring-box">
                <svg class="up-ring-svg" viewBox="0 0 32 32">
                  <circle class="up-ring-bg" cx="16" cy="16" r="13"></circle>
                  <circle class="up-ring-bar" id="sim-ring-3" cx="16" cy="16" r="13" stroke-dasharray="81.68" stroke-dashoffset="0"></circle>
                </svg>
                <span class="up-timer-sec" id="sim-sec-3">29s</span>
              </div>
            </div>

            <div class="up-token-foot">
              <span>Algoritma: HMAC-SHA256 &middot; 6-Digit</span>
              <button type="button" class="up-copy-btn" onclick="copySimToken('sim-code-3', 'Stripe Production')">
                <i class="fa-regular fa-clone"></i>
                <span>Salin</span>
              </button>
            </div>
          </div>

          <!-- Token 4: Cloudflare Edge -->
          <div class="up-token-card" data-category="security" data-search="cloudflare zero trust edge sre security">
            <div class="up-token-head">
              <div class="up-token-issuer-meta">
                <div class="up-issuer-icon" style="color:#f97316">
                  <i class="fa-brands fa-cloudflare"></i>
                </div>
                <div>
                  <div class="up-issuer-name">Cloudflare Zero Trust</div>
                  <div class="up-account-label">security-ops@company.corp</div>
                </div>
              </div>
              <span class="up-role-badge can-edit">Boleh Edit (Can Edit)</span>
            </div>

            <div class="up-token-body">
              <div class="up-otp-digits" id="sim-code-4">265 918</div>
              <div class="up-timer-ring-box">
                <svg class="up-ring-svg" viewBox="0 0 32 32">
                  <circle class="up-ring-bg" cx="16" cy="16" r="13"></circle>
                  <circle class="up-ring-bar" id="sim-ring-4" cx="16" cy="16" r="13" stroke-dasharray="81.68" stroke-dashoffset="0"></circle>
                </svg>
                <span class="up-timer-sec" id="sim-sec-4">29s</span>
              </div>
            </div>

            <div class="up-token-foot">
              <span>Algoritma: HMAC-SHA1 &middot; 6-Digit</span>
              <button type="button" class="up-copy-btn" onclick="copySimToken('sim-code-4', 'Cloudflare Zero Trust')">
                <i class="fa-regular fa-clone"></i>
                <span>Salin</span>
              </button>
            </div>
          </div>

        </div>

        <div class="up-sim-footer-note">
          <div>
            <i class="fa-solid fa-lock" style="color:var(--up-accent);margin-right:.4rem"></i>
            <span>Kode di atas dihitung secara server-side. Browser tidak pernah menerima Base32 secret mentah.</span>
          </div>
          <div>
            <span style="font-family:var(--up-font-mono);color:#94a3b8">Kunci Master: [AES-256-GCM Server Enclave]</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════ SECTION 2: WHY SERVER-SIDE ISOLATION MATTERS ════ -->
  <section class="up-why-section" id="arsitektur">
    <div class="up-container">
      <div class="up-section-header">
        <div class="up-section-tag">Arsitektur Kriptografi</div>
        <h2 class="up-section-title">Mengapa Isolasi Server-Side Wajib untuk Tim</h2>
        <p class="up-section-desc">
          Perbedaan mendasar antara menyebarkan kunci rahasia mentah ke aplikasi pengguna vs mengisolasinya di server internal.
        </p>
      </div>

      <div class="up-pillars-grid">
        <!-- Pillar 1 -->
        <div class="up-pillar-card">
          <div class="up-pillar-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          <h3 class="up-pillar-title">Masalah: Kunci Mentah Tersebar</h3>
          <p class="up-pillar-desc">
            Aplikasi 2FA konvensional (Google Authenticator, export Bitwarden) menyimpan kunci Base32 mentah di perangkat setiap karyawan. Saat laptop hilang atau karyawan resign, kunci rahasia tetap berada di tangan mereka selamanya.
          </p>
          <div class="up-pillar-spec">
            <i class="fa-solid fa-xmark" style="color:#ef4444"></i>
            <span>Risiko kebocoran permanen pada endpoint</span>
          </div>
        </div>

        <!-- Pillar 2 -->
        <div class="up-pillar-card">
          <div class="up-pillar-icon">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <h3 class="up-pillar-title">Solusi: Isolasi Server-Side</h3>
          <p class="up-pillar-desc">
            Kunci master Base32 dienkripsi penuh dengan AES-256-GCM dan disimpan eksklusif di server database Anda. Anggota tim hanya menerima output numerik 6 digit berdurasi 30 detik. Kunci master tidak pernah terekspos ke browser.
          </p>
          <div class="up-pillar-spec">
            <i class="fa-solid fa-check"></i>
            <span>AES-256-GCM Server Enclave</span>
          </div>
        </div>

        <!-- Pillar 3 -->
        <div class="up-pillar-card">
          <div class="up-pillar-icon">
            <i class="fa-solid fa-user-xmark"></i>
          </div>
          <h3 class="up-pillar-title">Kendali: Offboarding Instan 1-Klik</h3>
          <p class="up-pillar-desc">
            Saat terjadi mutasi personel atau pemutusan kontrak, administrator cukup mencabut hak akses akun tersebut dalam 1 klik. Seketika itu juga seluruh akses token perusahaan putus tanpa perlu merotasi secret di puluhan penyedia layanan luar.
          </p>
          <div class="up-pillar-spec">
            <i class="fa-solid fa-check"></i>
            <span>Zero Credential Rotation Overhead</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════ SECTION 3: TECHNICAL SPECIFICATIONS (APPLE-STYLE SPEC SHEET) ════ -->
  <section class="up-specs-section" id="spesifikasi">
    <div class="up-container">
      <div class="up-section-header">
        <div class="up-section-tag">Spesifikasi Standar</div>
        <h2 class="up-section-title">Lembar Spesifikasi Kriptografi &amp; Kepatuhan</h2>
        <p class="up-section-desc">
          Rincian teknis standar industri yang diimplementasikan pada arsitektur TOTPVault.
        </p>
      </div>

      <div class="up-specs-table">
        <div class="up-spec-row">
          <div class="up-spec-label">Standar Enkripsi Master</div>
          <div class="up-spec-value"><strong>AES-256-GCM</strong> (Authenticated Encryption with Associated Data)</div>
        </div>
        <div class="up-spec-row">
          <div class="up-spec-label">Standar Algoritma TOTP</div>
          <div class="up-spec-value"><strong>RFC 6238</strong> &middot; HMAC-SHA1, HMAC-SHA256, HMAC-SHA512</div>
        </div>
        <div class="up-spec-row">
          <div class="up-spec-label">Resolusi &amp; Toleransi Drift</div>
          <div class="up-spec-value">Interval 30s / 60s &middot; Toleransi validasi drift &plusmn;1 langkah waktu</div>
        </div>
        <div class="up-spec-row">
          <div class="up-spec-label">Model Kontrol Akses</div>
          <div class="up-spec-value"><strong>Dual-Perspective IAM</strong> &middot; Role <em>Hanya Lihat (Read-Only)</em> vs <em>Boleh Edit (Can Edit)</em></div>
        </div>
        <div class="up-spec-row">
          <div class="up-spec-label">Metode Login</div>
          <div class="up-spec-value"><strong>OAuth 2.0</strong> &middot; Masuk dengan Google &amp; Microsoft (Terproteksi Whitelist)</div>
        </div>
        <div class="up-spec-row">
          <div class="up-spec-label">Proteksi Serangan DoS</div>
          <div class="up-spec-value">In-Memory IP Rate Limiter &middot; Throttling otomatis pada endpoint sensitif</div>
        </div>
        <div class="up-spec-row">
          <div class="up-spec-label">Kedaulatan Data</div>
          <div class="up-spec-value"><strong>100% Self-Hosted</strong> &middot; On-Premise / VPC Private (Nol telemetri eksternal)</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════ SECTION 4: AUTHENTICATION CONSOLE ════ -->
  <section class="up-auth-section" id="auth-console">
    <div class="up-container">
      <div class="up-auth-card">
        <img src="/logo.png" alt="TOTPVault Logo" class="up-auth-logo-img">

        <h2 class="up-auth-title">Masuk ke Akun Anda</h2>
        <p class="up-auth-desc">Gunakan akun Google atau Microsoft Anda yang telah didaftarkan untuk membuka brankas.</p>

        <!-- Error Feedback -->
        <?php if (!empty($_GET['error'])): ?>
        <div style="margin-bottom:1.25rem;padding:.875rem 1rem;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);border-radius:10px;color:#fca5a5;font-size:.84rem;text-align:left;display:flex;gap:.65rem;align-items:flex-start">
          <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;color:#ef4444;font-size:1rem;flex-shrink:0"></i>
          <div>
            <strong style="display:block;color:#fecaca;margin-bottom:2px">Akses Belum Diizinkan</strong>
            <span style="line-height:1.45;display:block"><?= htmlspecialchars($_GET['error']) ?></span>
          </div>
        </div>
        <?php endif; ?>

        <!-- Google Login -->
        <?php if ($googleOn): ?>
        <a href="/auth/login/google" class="up-oauth-btn up-google-btn">
          <svg viewBox="0 0 24 24" style="width:18px;height:18px">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          <span>Masuk dengan Google</span>
        </a>
        <?php endif; ?>

        <!-- Microsoft Login -->
        <?php if ($microsoftOn): ?>
        <a href="/auth/login/microsoft" class="up-oauth-btn up-microsoft-btn">
          <svg viewBox="0 0 23 23" style="width:16px;height:16px">
            <path fill="#f35325" d="M1 1h10v10H1z"/>
            <path fill="#81bc06" d="M12 1h10v10H12z"/>
            <path fill="#05a6f0" d="M1 12h10v10H1z"/>
            <path fill="#ffba08" d="M12 12h10v10H12z"/>
          </svg>
          <span>Masuk dengan Microsoft</span>
        </a>
        <?php endif; ?>

        <!-- Local Development Bypass for instant testing -->
        <?php if ($appEnv === 'development'): ?>
        <div style="margin-top:1rem;padding:.75rem;background:#090a10;border:1px dashed rgba(16,185,129,0.3);border-radius:8px">
          <div style="font-size:.7rem;color:var(--up-accent);font-weight:700;margin-bottom:.35rem">DEVELOPMENT ENVIRONMENT:</div>
          <a href="/auth/dev-login" class="up-btn-primary" style="padding:.45rem .85rem;font-size:.8rem;width:100%;justify-content:center;box-sizing:border-box">
            <i class="fa-solid fa-code"></i> Masuk Instan (Dev Bypass)
          </a>
        </div>
        <?php endif; ?>

        <div class="up-auth-footer-notice">
          <i class="fa-solid fa-shield-halved" style="color:var(--up-accent);margin-right:.35rem"></i>
          Hanya email yang telah didaftarkan di Whitelist yang dapat masuk. Hubungi administrator jika akun Anda belum terdaftar.
        </div>
      </div>
    </div>
  </section>

  <!-- ════ AUTHENTICATION MODAL (INSTANT ACCESS FROM ANYWHERE) ════ -->
  <div class="up-modal-backdrop" id="auth-modal" onclick="handleModalBackdropClick(event)">
    <div class="up-modal-window">
      <button type="button" class="up-modal-close" onclick="closeAuthModal()" aria-label="Tutup Modal">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <div style="text-align:center;margin-bottom:1.5rem">
        <img src="/logo.png" alt="TOTPVault Logo" class="up-auth-logo-img" style="margin-bottom:.65rem">
        <h3 style="font-size:1.25rem;font-weight:800;color:#ffffff;margin:0 0 .25rem">Masuk ke TOTPVault</h3>
        <p style="font-size:.8125rem;color:var(--up-text-secondary);margin:0">Gunakan akun Google atau Microsoft Anda yang telah didaftarkan.</p>
      </div>

      <!-- Error Feedback in Modal -->
      <?php if (!empty($_GET['error'])): ?>
      <div style="margin-bottom:1.25rem;padding:.875rem 1rem;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);border-radius:10px;color:#fca5a5;font-size:.84rem;text-align:left;display:flex;gap:.65rem;align-items:flex-start">
        <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;color:#ef4444;font-size:1rem;flex-shrink:0"></i>
        <div>
          <strong style="display:block;color:#fecaca;margin-bottom:2px">Akses Belum Diizinkan</strong>
          <span style="line-height:1.45;display:block"><?= htmlspecialchars($_GET['error']) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($googleOn): ?>
      <a href="/auth/login/google" class="up-oauth-btn up-google-btn">
        <svg viewBox="0 0 24 24" style="width:18px;height:18px">
          <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
          <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
          <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
          <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        <span>Masuk dengan Google</span>
      </a>
      <?php endif; ?>

      <?php if ($microsoftOn): ?>
      <a href="/auth/login/microsoft" class="up-oauth-btn up-microsoft-btn">
        <svg viewBox="0 0 23 23" style="width:16px;height:16px">
          <path fill="#f35325" d="M1 1h10v10H1z"/>
          <path fill="#81bc06" d="M12 1h10v10H12z"/>
          <path fill="#05a6f0" d="M1 12h10v10H1z"/>
          <path fill="#ffba08" d="M12 12h10v10H12z"/>
        </svg>
        <span>Masuk dengan Microsoft</span>
      </a>
      <?php endif; ?>

      <?php if ($appEnv === 'development'): ?>
      <div style="margin-top:1rem;padding:.75rem;background:#090a10;border:1px dashed rgba(16,185,129,0.3);border-radius:8px">
        <div style="font-size:.7rem;color:var(--up-accent);font-weight:700;margin-bottom:.35rem">DEVELOPMENT ENVIRONMENT:</div>
        <a href="/auth/dev-login" class="up-btn-primary" style="padding:.45rem .85rem;font-size:.8rem;width:100%;justify-content:center;box-sizing:border-box">
          <i class="fa-solid fa-code"></i> Masuk Instan (Dev Bypass)
        </a>
      </div>
      <?php endif; ?>

      <div style="margin-top:1.25rem;text-align:center;font-size:.75rem;color:var(--up-text-muted)">
        Hanya email yang telah terdaftar di whitelist yang dapat masuk.
      </div>
    </div>
  </div>

  <!-- ════ FOOTER ════ -->
  <footer class="up-footer">
    <div class="up-container">
      <div class="up-footer-inner">
        <div style="display:flex;align-items:center;gap:.6rem">
          <img src="/logo.png" alt="TOTPVault" style="width:20px;height:20px;object-fit:contain">
          <div><span style="color:#ffffff;font-weight:700">TOTPVault</span> &middot; Berdasarkan arsitektur <a href="https://github.com/token2/TOTPVault" target="_blank" rel="noopener" style="color:var(--up-accent);text-decoration:none">Token2 TOTPVault (Switzerland)</a> &middot; Lisensi GPL-3.0</div>
        </div>
        <div class="up-footer-links">
          <a href="#vault-simulator" class="up-footer-link">Simulator</a>
          <a href="#arsitektur" class="up-footer-link">Arsitektur</a>
          <a href="#spesifikasi" class="up-footer-link">Spesifikasi</a>
          <a href="/api/health" class="up-footer-link" target="_blank">Health API</a>
        </div>
      </div>
    </div>
  </footer>

</div>

<script>
// ── RFC 6238 Live Simulation Logic ──
const simTokens = [
  { id: 1, base: 738109, codes: ['738 109', '512 893', '904 312', '182 645', '629 401'] },
  { id: 2, base: 419652, codes: ['419 652', '830 194', '275 601', '741 829', '382 910'] },
  { id: 3, base: 802431, codes: ['802 431', '391 720', '648 105', '912 374', '507 863'] },
  { id: 4, base: 265918, codes: ['265 918', '704 532', '189 640', '431 827', '895 203'] }
];

function updateSimulatorTimers() {
  const now = Math.floor(Date.now() / 1000);
  const remaining = 30 - (now % 30);
  const circumference = 81.68;
  const offset = circumference * (1 - (remaining / 30));
  const strokeColor = remaining <= 5 ? '#ef4444' : (remaining <= 10 ? '#f59e0b' : '#10b981');

  for (let i = 1; i <= 4; i++) {
    const secEl = document.getElementById(`sim-sec-${i}`);
    const ringEl = document.getElementById(`sim-ring-${i}`);
    const codeEl = document.getElementById(`sim-code-${i}`);

    if (secEl) secEl.textContent = `${remaining}s`;
    if (ringEl) {
      ringEl.style.strokeDashoffset = offset;
      ringEl.style.stroke = strokeColor;
    }

    if (remaining === 30 && codeEl) {
      const token = simTokens[i - 1];
      const nextIdx = (Math.floor(now / 30) + i) % token.codes.length;
      codeEl.textContent = token.codes[nextIdx];
    }
  }
}

setInterval(updateSimulatorTimers, 1000);
updateSimulatorTimers();

// ── Copy Function with Instant Toast ──
function copySimToken(codeElementId, tokenName) {
  const codeEl = document.getElementById(codeElementId);
  if (!codeEl) return;
  const digits = codeEl.textContent.replace(/\s+/g, '');
  navigator.clipboard.writeText(digits).then(() => {
    toast(`Kode 2FA ${tokenName} (${digits}) disalin ke clipboard!`, 'success');
  }).catch(() => {
    toast('Gagal menyalin kode ke clipboard', 'error');
  });
}

// ── Interactive Category & Search Filter ──
let currentCategory = 'all';

function setSimCategory(cat, btn) {
  currentCategory = cat;
  document.querySelectorAll('.up-sim-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  handleSimFilter();
}

function handleSimFilter() {
  const query = (document.getElementById('sim-search-input')?.value || '').toLowerCase().trim();
  const cards = document.querySelectorAll('.up-token-card');

  cards.forEach(card => {
    const cardCat = card.getAttribute('data-category');
    const cardSearch = (card.getAttribute('data-search') || '').toLowerCase();
    const matchesCat = (currentCategory === 'all' || cardCat === currentCategory);
    const matchesQuery = (!query || cardSearch.includes(query) || card.textContent.toLowerCase().includes(query));

    card.style.display = (matchesCat && matchesQuery) ? 'flex' : 'none';
  });
}

// ── Modal Auth Console Controls ──
function openAuthModal() {
  const modal = document.getElementById('auth-modal');
  if (modal) {
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}

function closeAuthModal() {
  const modal = document.getElementById('auth-modal');
  if (modal) {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }
}

function handleModalBackdropClick(event) {
  if (event.target.id === 'auth-modal') {
    closeAuthModal();
  }
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeAuthModal();
});

window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  if (params.has('error')) {
    const authEl = document.getElementById('auth-console');
    if (authEl) {
      setTimeout(() => {
        authEl.scrollIntoView({ behavior: 'smooth' });
      }, 150);
    }
  }
});
</script>
</body>
</html>
