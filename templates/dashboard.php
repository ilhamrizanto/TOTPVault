<?php
$viewTitles = [
    'tokens'    => 'Tokens — TOTPVault',
    'access'    => 'Kelola Akses — TOTPVault',
    'whitelist' => 'Whitelist Akun — TOTPVault',
];
$currentView = $currentView ?? 'tokens';
$pageTitle = $viewTitles[$currentView] ?? 'Tokens — TOTPVault';
require __DIR__ . '/layout.php';
$csrfToken = csrf_token();
$avatar = $user['avatar_url'] ? htmlspecialchars($user['avatar_url']) : null;
$initial = strtoupper(substr($user['name'] ?? $user['email'], 0, 1));
$currentView = $currentView ?? 'tokens';

$FA_BRAND_ICONS = [
  'fa-github','fa-google','fa-microsoft','fa-apple','fa-amazon','fa-facebook',
  'fa-twitter','fa-instagram','fa-linkedin','fa-slack','fa-discord','fa-telegram',
  'fa-whatsapp','fa-dropbox','fa-spotify','fa-twitch','fa-youtube','fa-reddit',
  'fa-gitlab','fa-bitbucket','fa-docker','fa-aws','fa-cloudflare','fa-digital-ocean',
  'fa-stripe','fa-paypal','fa-shopify','fa-wordpress','fa-jenkins','fa-jira',
  'fa-confluence','fa-trello','fa-npm','fa-node','fa-react','fa-vuejs','fa-angular',
  'fa-laravel','fa-php','fa-python','fa-java','fa-swift','fa-android','fa-windows',
  'fa-linux','fa-ubuntu','fa-firefox','fa-chrome','fa-safari','fa-steam',
  'fa-playstation','fa-xbox',
];

$FA_SOLID_ICONS = [
  'fa-shield-halved','fa-key','fa-lock','fa-lock-open','fa-user','fa-users',
  'fa-building','fa-globe','fa-server','fa-database','fa-cloud','fa-code',
  'fa-terminal','fa-mobile','fa-laptop','fa-desktop','fa-wifi','fa-envelope',
  'fa-bell','fa-star','fa-bolt','fa-fire','fa-gear','fa-wrench',
  'fa-briefcase','fa-chart-bar','fa-credit-card','fa-wallet','fa-shop',
];

function resolveIconClass(?string $icon, string $issuer = '', string $name = ''): string {
  static $brandIcons = [
    'fa-github','fa-google','fa-microsoft','fa-apple','fa-amazon','fa-facebook',
    'fa-twitter','fa-instagram','fa-linkedin','fa-slack','fa-discord','fa-telegram',
    'fa-whatsapp','fa-dropbox','fa-spotify','fa-twitch','fa-youtube','fa-reddit',
    'fa-gitlab','fa-bitbucket','fa-docker','fa-aws','fa-cloudflare','fa-digital-ocean',
    'fa-stripe','fa-paypal','fa-shopify','fa-wordpress','fa-jenkins','fa-jira',
    'fa-confluence','fa-trello','fa-npm','fa-node','fa-react','fa-vuejs','fa-angular',
    'fa-laravel','fa-php','fa-python','fa-java','fa-swift','fa-android','fa-windows',
    'fa-linux','fa-ubuntu','fa-firefox','fa-chrome','fa-safari','fa-steam',
    'fa-playstation','fa-xbox',
  ];
  $icon = trim($icon ?? '');
  if (!$icon || $icon === 'shield') {
    $haystack = strtolower($issuer . ' ' . $name);
    if (str_contains($haystack, 'google') || str_contains($haystack, 'gmail')) return 'fa-brands fa-google';
    if (str_contains($haystack, 'github')) return 'fa-brands fa-github';
    if (str_contains($haystack, 'microsoft') || str_contains($haystack, 'azure') || str_contains($haystack, 'office') || str_contains($haystack, 'outlook')) return 'fa-brands fa-microsoft';
    if (str_contains($haystack, 'amazon') || str_contains($haystack, 'aws')) return 'fa-brands fa-aws';
    if (str_contains($haystack, 'apple') || str_contains($haystack, 'icloud')) return 'fa-brands fa-apple';
    if (str_contains($haystack, 'discord')) return 'fa-brands fa-discord';
    if (str_contains($haystack, 'telegram')) return 'fa-brands fa-telegram';
    if (str_contains($haystack, 'slack')) return 'fa-brands fa-slack';
    if (str_contains($haystack, 'facebook')) return 'fa-brands fa-facebook';
    if (str_contains($haystack, 'twitter') || str_contains($haystack, ' x ')) return 'fa-brands fa-twitter';
    if (str_contains($haystack, 'gitlab')) return 'fa-brands fa-gitlab';
    if (str_contains($haystack, 'bitbucket')) return 'fa-brands fa-bitbucket';
    return 'fa-solid fa-shield-halved';
  }

  if (preg_match('/^(fa-(brands|solid|regular)|fab|fas|far)\s+/', $icon)) {
    return $icon;
  }

  $raw = preg_replace('/^fa-/', '', $icon);
  $faClass = 'fa-' . $raw;

  if (in_array($faClass, $brandIcons, true) || in_array($icon, $brandIcons, true)) {
    return 'fa-brands ' . $faClass;
  }

  return 'fa-solid ' . $faClass;
}
?>

<style>
/* ── Pitch-Black / Deep Obsidian System ── */
.app-shell {
  display: flex;
  width: 100%;
  max-width: 100%;
  min-height: 100vh;
  background: var(--canvas);
  margin: 0;
  padding: 0;
  position: relative;
  overflow-x: hidden;
}

/* ── Deep Dark Left Sidebar Dock (Fixed Viewport Dock) ── */
.sidebar {
  width: 240px;
  flex-shrink: 0;
  background: #08090d;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 1.25rem 1rem 1rem;
  z-index: 150;
  overflow-y: auto;
  overflow-x: hidden;
  border-right: 1px solid rgba(255, 255, 255, 0.06);
}

.sidebar-top {
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .25rem .35rem;
}

.brand-identity {
  display: flex;
  align-items: center;
  gap: .625rem;
}

.brand-icon-box {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.45));
}
.brand-icon-box svg {
  width: 32px;
  height: 32px;
}

.brand-title {
  color: #ffffff;
  font-size: 1.15rem;
  font-weight: 800;
  letter-spacing: -0.03em;
}

.brand-version-chip {
  font-size: .625rem;
  font-weight: 700;
  padding: .1rem .45rem;
  border-radius: var(--r-pill);
  background: rgba(16, 185, 129, 0.12);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
  letter-spacing: .04em;
}

.sidebar-category {
  font-size: .625rem;
  font-weight: 700;
  color: #475569;
  letter-spacing: .1em;
  text-transform: uppercase;
  padding: .5rem .5rem .25rem;
}

.nav-list {
  display: flex;
  flex-direction: column;
  gap: .3rem;
}

.nav-pill {
  display: flex;
  align-items: center;
  gap: .65rem;
  padding: .55rem .85rem;
  border-radius: 12px;
  font-size: .84rem;
  font-weight: 500;
  color: #94a3b8;
  background: transparent;
  border: 1px solid transparent;
  cursor: pointer;
  width: 100%;
  text-align: left;
  transition: all .15s ease;
  text-decoration: none;
}

.nav-pill:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
  transform: translateX(2px);
}

.nav-pill.active {
  background: rgba(16, 185, 129, 0.12);
  border-color: rgba(16, 185, 129, 0.25);
  color: #34d399;
  font-weight: 600;
  box-shadow: 0 0 16px rgba(16, 185, 129, 0.1);
}

.nav-pill-icon {
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.06);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8rem;
  flex-shrink: 0;
  color: #cbd5e1;
}

.nav-pill.active .nav-pill-icon {
  background: #10b981;
  color: #050508;
}

.nav-pill-count {
  margin-left: auto;
  font-size: .6875rem;
  font-weight: 700;
  padding: .1rem .45rem;
  border-radius: var(--r-pill);
  background: #12131b;
  color: #94a3b8;
  border: 1px solid rgba(255, 255, 255, 0.06);
}
.nav-pill.active .nav-pill-count {
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
  border-color: rgba(16, 185, 129, 0.3);
}

/* Utilities Menu */
.sidebar-bottom {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.util-links {
  display: flex;
  flex-direction: column;
  gap: .2rem;
}

.sidebar-util-link {
  display: flex;
  align-items: center;
  gap: .5rem;
  padding: .4rem .6rem;
  border-radius: 8px;
  font-size: .75rem;
  font-weight: 500;
  color: #64748b;
  text-decoration: none;
  transition: all .15s ease;
}
.sidebar-util-link:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.05);
}
.sidebar-util-link i {
  font-size: .75rem;
  width: 14px;
  text-align: center;
}

/* ── Main Content Shell ── */
.main-content-wrapper {
  margin-left: 240px;
  width: calc(100% - 240px);
  max-width: calc(100% - 240px);
  min-width: 0;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--canvas);
  box-sizing: border-box;
  overflow-x: hidden;
}

/* ── Seamless Topbar (Permanently Fixed at Top) ── */
.app-topbar {
  background: rgba(5, 5, 8, 0.94);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  padding: 1.15rem 2.5rem 0.75rem;
  position: fixed;
  top: 0;
  left: 240px;
  right: 0;
  width: calc(100% - 240px);
  z-index: 120;
  box-sizing: border-box;
}

.topbar-inner {
  width: 100%;
  max-width: 1440px;
  margin: 0 auto;
  display: flex;
  box-sizing: border-box;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
}

.topbar-left-section {
  display: flex;
  align-items: center;
  gap: .875rem;
  flex-shrink: 0;
}

.topbar-seamless-title {
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -0.035em;
  color: #f8fafc;
  line-height: 1;
  margin: 0;
}

.mobile-menu-btn {
  display: none;
  background: #0c0d13;
  border: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 1rem;
  color: #f8fafc;
  cursor: pointer;
  padding: .45rem;
  border-radius: 8px;
  align-items: center;
  justify-content: center;
}

/* Desktop Centered Spotlight Omnibar */
.topbar-search-section {
  flex: 1;
  max-width: 440px;
  margin: 0 1rem;
}

.topbar-search-section .search-pill-wrap {
  width: 100%;
  position: relative;
}
.topbar-search-section .search-pill-wrap input,
.mobile-search-box input {
  width: 100%;
  height: 42px;
  padding: 0 2.25rem 0 2.5rem;
  background: #0c0d13;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--r-pill);
  font-size: .84rem;
  color: #f8fafc;
  outline: none;
  transition: all .18s ease;
}
.topbar-search-section .search-pill-wrap input:focus,
.mobile-search-box input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}
.topbar-search-section .search-pill-wrap .search-icon,
.mobile-search-box .search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  font-size: .875rem;
  pointer-events: none;
}
.topbar-search-section .search-pill-wrap .search-kbd {
  position: absolute;
  right: .9rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: .625rem;
  padding: .15rem .45rem;
  background: #141520;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 4px;
  color: #94a3b8;
  font-family: 'JetBrains Mono', monospace;
  pointer-events: none;
}

/* Mobile Search Row */
.mobile-search-row { display: none; }
.mobile-search-box { width: 100%; position: relative; }

/* Right Section: Add Token & Profile */
.topbar-right-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.topbar-add-btn {
  height: 42px;
  padding: 0 1.25rem;
  font-size: .84rem;
  font-weight: 700;
  border-radius: var(--r-pill);
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  background: #10b981;
  color: #050508;
  border: none;
  cursor: pointer;
  box-shadow: 0 0 16px rgba(16, 185, 129, 0.35);
  transition: all .15s ease;
  white-space: nowrap;
}
.topbar-add-btn:hover {
  background: #34d399;
  transform: translateY(-1px);
  box-shadow: 0 0 24px rgba(16, 185, 129, 0.5);
}

.topbar-profile-box {
  display: flex;
  align-items: center;
  gap: .625rem;
  padding: .25rem .75rem .25rem .3rem;
  background: #0c0d13;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--r-pill);
  transition: all .15s ease;
}
.topbar-profile-box:hover {
  border-color: rgba(255, 255, 255, 0.16);
}

.topbar-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #10b981;
  color: #050508;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: .875rem;
  overflow: hidden;
  flex-shrink: 0;
}

.topbar-user-meta {
  display: flex;
  flex-direction: column;
  max-width: 140px;
  line-height: 1.2;
}

.topbar-user-name {
  font-size: .8125rem;
  font-weight: 700;
  color: #f8fafc;
}

.topbar-user-role {
  font-size: .6875rem;
  color: #64748b;
}

.topbar-logout-btn {
  color: #64748b;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: .4rem;
  border-radius: 50%;
  transition: all .15s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  margin-left: .15rem;
}
.topbar-logout-btn:hover {
  color: #f43f5e;
  background: rgba(244, 63, 94, 0.15);
}

/* ── Main Canvas ── */
.main-canvas {
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
  background: var(--canvas);
  padding: 5.75rem 2.5rem 4rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  overflow-x: hidden;
}

.main-canvas-inner {
  width: 100%;
  max-width: 1800px;
  margin: 0 auto;
  box-sizing: border-box;
}

/* Filter Tabs & View Switcher Row */
.tokens-filter-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.tokens-tabs-bar {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  background: #0c0d13;
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: .3rem .45rem;
  border-radius: var(--r-pill);
}

.tokens-tab-btn {
  padding: .5rem 1.25rem;
  font-size: .84rem;
  font-weight: 600;
  border-radius: var(--r-pill);
  border: 1px solid transparent;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
  transition: all .15s ease;
  display: inline-flex;
  align-items: center;
  gap: .5rem;
}
.tokens-tab-btn:hover { color: #f8fafc; }
.tokens-tab-btn.active {
  background: #151722;
  border-color: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);
}

.tab-badge-num {
  font-size: .6875rem;
  font-weight: 700;
  padding: .1rem .5rem;
  border-radius: var(--r-pill);
  background: #141520;
  color: #94a3b8;
  border: 1px solid rgba(255, 255, 255, 0.06);
}
.tokens-tab-btn.active .tab-badge-num {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border-color: rgba(16, 185, 129, 0.3);
}

.tab-lbl-desktop { display: inline; }
.tab-lbl-mobile { display: none; }

/* Dual View Switcher (Grid vs Dense Table) */
.view-switch-bar {
  display: inline-flex;
  align-items: center;
  background: #0c0d13;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--r-pill);
  padding: .25rem;
  gap: .2rem;
}
.view-switch-btn {
  background: transparent;
  border: 1px solid transparent;
  color: #64748b;
  padding: .4rem .65rem;
  border-radius: var(--r-pill);
  cursor: pointer;
  font-size: .875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s ease;
}
.view-switch-btn:hover { color: #f8fafc; }
.view-switch-btn.active {
  background: #151722;
  border-color: rgba(255, 255, 255, 0.1);
  color: #10b981;
}

/* Tokens Grid: Dynamic Responsive Columns (Adaptive, Max 5 Columns) */
.tokens-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 1.25rem;
  width: 100%;
  box-sizing: border-box;
}

@media (max-width: 1550px) {
  .tokens-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1.15rem;
  }
}

@media (max-width: 1220px) {
  .tokens-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
  }
}

@media (max-width: 920px) {
  .tokens-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .85rem;
  }
}

/* Pitch-Black Token Card with Luxury Cyber Vault Accent */
.token-card {
  background: linear-gradient(165deg, #0e111a 0%, #07090e 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 1.25rem 1.35rem;
  box-shadow: 0 10px 28px -6px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  gap: .85rem;
  transition: box-shadow .2s ease, border-color .2s ease;
  position: relative;
  box-sizing: border-box;
  user-select: none;
}

.token-card:hover {
  border-color: rgba(255, 255, 255, 0.16);
  box-shadow: 0 12px 30px -6px rgba(0, 0, 0, 0.75), inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.token-card.copied-pulse {
  border-color: #10b981 !important;
  box-shadow: 0 0 25px rgba(16, 185, 129, 0.45), inset 0 1px 0 rgba(16, 185, 129, 0.2) !important;
}

/* Drag and Drop Reordering (Icon-Only Handle Trigger) */
.token-card.is-dragging {
  opacity: 0.45 !important;
  border-color: #10b981 !important;
  box-shadow: 0 0 25px rgba(16, 185, 129, 0.4) !important;
  transform: scale(0.985);
}
.token-drag-handle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 32px;
  color: #64748b;
  cursor: grab !important;
  border-radius: 6px;
  transition: all .15s ease;
  flex-shrink: 0;
  user-select: none;
  touch-action: none !important;
}
.token-drag-handle:hover {
  color: #10b981;
  background: rgba(16, 185, 129, 0.15);
}
.token-drag-handle:active {
  cursor: grabbing !important;
}

body.vault-dragging,
body.vault-dragging * {
  user-select: none !important;
  -webkit-user-select: none !important;
  cursor: grabbing !important;
}

.token-row.is-dragging {
  opacity: 0.55 !important;
  background: rgba(16, 185, 129, 0.18) !important;
  outline: 2px dashed #10b981 !important;
}
.token-row-drag-handle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 32px;
  color: #64748b;
  cursor: grab !important;
  margin-right: .35rem;
  border-radius: 6px;
  transition: all .15s ease;
  flex-shrink: 0;
  user-select: none;
  -webkit-user-select: none;
  touch-action: none !important;
}
.token-row-drag-handle:hover {
  color: #10b981;
  background: rgba(16, 185, 129, 0.15);
}
.token-row-drag-handle:active {
  cursor: grabbing !important;
}

/* Reset Order Button */
#btn-reset-order {
  display: none;
  align-items: center;
  gap: .35rem;
  border-radius: var(--r-pill);
  font-weight: 600;
  font-size: .8125rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.25);
  color: #f87171;
  transition: all .15s ease;
}
#btn-reset-order:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.4);
  color: #ffffff;
}
.reset-order-lbl-desktop { display: inline; }
.reset-order-lbl-mobile { display: none; }
@media (max-width: 768px) {
  .reset-order-lbl-desktop { display: none; }
  .reset-order-lbl-mobile { display: inline; }
  #btn-reset-order {
    height: 30px !important;
    padding: 0 .6rem !important;
    font-size: .76rem !important;
  }
}

.token-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .5rem;
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
}

.token-identity {
  display: flex;
  align-items: center;
  gap: .55rem;
  min-width: 0;
  flex: 1 1 auto;
  overflow: hidden;
}

.token-icon-squircle {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

/* Full-Width Issuer Badge Row */
.token-issuer-row {
  display: flex;
  align-items: center;
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  margin-top: .1rem;
  margin-bottom: -.25rem;
}

.token-issuer-badge {
  display: inline-flex;
  align-items: center;
  gap: .38rem;
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: #38bdf8;
  background: rgba(56, 189, 248, 0.08);
  border: 1px solid rgba(56, 189, 248, 0.22);
  padding: .2rem .55rem;
  border-radius: 6px;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}

.token-issuer-badge i {
  font-size: .68rem;
  opacity: .85;
}

/* Dedicated Full-Width Token Account Title */
.token-name-section {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  margin: .05rem 0;
}

.token-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: #ffffff;
  letter-spacing: -0.015em;
  line-height: 1.35;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
  width: 100%;
  max-width: 100%;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
}

.token-header-actions {
  display: flex;
  align-items: center;
  gap: .25rem;
  flex-shrink: 0;
  margin-left: auto;
}

.token-action-icon-btn {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.05);
  color: #94a3b8;
  cursor: pointer;
  width: 28px;
  height: 28px;
  padding: 0;
  border-radius: 8px;
  transition: all .15s ease;
  font-size: .78rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.token-action-icon-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.15);
}

/* OTP Digits Hero & Glow (1 Single Line Guaranteed) */
.token-code-hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .75rem 1rem;
  background: #05060a;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  cursor: pointer;
  position: relative;
  transition: all .15s ease;
  user-select: none;
  width: 100%;
  box-sizing: border-box;
  min-width: 0;
  gap: .5rem;
}

.token-code-hero:hover {
  background: #080a11;
  border-color: rgba(16, 185, 129, 0.35);
}

.token-code-hero:active {
  transform: scale(0.99);
}

.token-digits-wrap {
  display: flex;
  flex-direction: column;
  gap: .1rem;
  min-width: 0;
  flex: 1 1 auto;
  overflow: hidden;
  white-space: nowrap !important;
}

.token-hero-digits {
  font-family: 'JetBrains Mono', monospace;
  font-size: 1.55rem;
  font-weight: 700;
  letter-spacing: .08em;
  color: #ffffff;
  line-height: 1;
  white-space: nowrap !important;
  font-variant-numeric: tabular-nums;
  text-shadow: 0 0 12px rgba(16, 185, 129, 0.15);
  display: block;
}

.token-click-hint {
  font-size: .6875rem;
  font-weight: 600;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: .3rem;
  letter-spacing: .02em;
  margin-top: .15rem;
}
.token-code-hero:hover .token-click-hint {
  color: #10b981;
}

/* Circular Timer Gauge */
.token-gauge-box {
  display: flex;
  align-items: center;
  gap: .375rem;
}

.token-gauge-svg {
  width: 28px;
  height: 28px;
  transform: rotate(-90deg);
}
.token-gauge-svg circle {
  fill: none;
  stroke-width: 3.5;
}
.token-gauge-bg { stroke: rgba(255, 255, 255, 0.07); }
.token-gauge-bar {
  stroke: #10b981;
  filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.5));
  stroke-linecap: round;
  transition: stroke-dashoffset .5s linear, stroke .3s;
}

.token-timer-sec {
  font-family: 'JetBrains Mono', monospace;
  font-size: .75rem;
  font-weight: 700;
  color: #94a3b8;
  min-width: 20px;
  text-align: right;
}

/* Card Footer */
.token-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .5rem;
}

.token-meta-pills {
  display: flex;
  align-items: center;
  gap: .35rem;
}

.token-meta-chip {
  font-size: .625rem;
  font-weight: 600;
  padding: .15rem .45rem;
  border-radius: var(--r-pill);
  background: #08090d;
  border: 1px solid rgba(255, 255, 255, 0.06);
  color: #94a3b8;
}

.token-copy-pill {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .3rem .75rem;
  background: #141520;
  color: #cbd5e1;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--r-pill);
  font-size: .6875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .15s ease;
}
.token-copy-pill:hover {
  background: #1e2030;
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.16);
}
.token-copy-pill.is-copied {
  background: #10b981 !important;
  color: #050508 !important;
  border-color: #10b981 !important;
  font-weight: 700;
}

/* ── Dense DevOps Table Mode ── */
.tokens-table-wrap {
  width: 100%;
  background: #0c0d12;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  overflow-x: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}
.tokens-dense-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .8125rem;
  text-align: left;
}
.tokens-dense-table thead tr {
  background: #07080d;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  color: #64748b;
  text-transform: uppercase;
  font-size: .6875rem;
  letter-spacing: .06em;
}
.tokens-dense-table th {
  padding: .85rem 1rem;
  font-weight: 700;
}
.tokens-dense-table tbody tr {
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  transition: background .15s ease;
}
.tokens-dense-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.03);
}
.tokens-dense-table td {
  padding: .85rem 1rem;
  vertical-align: middle;
}
.table-otp-button {
  background: #07080d;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  padding: .35rem .75rem;
  font-family: 'JetBrains Mono', monospace;
  font-size: 1.15rem;
  font-weight: 700;
  letter-spacing: .1em;
  color: #ffffff;
  cursor: pointer;
  transition: all .15s ease;
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  white-space: nowrap !important;
  flex-shrink: 0;
}
.table-otp-button:hover {
  background: #0c1017;
  border-color: rgba(16, 185, 129, 0.4);
  color: #34d399;
}

/* Empty Search State */
.empty-search-state {
  grid-column: 1 / -1;
  background: #0c0d12;
  border: 1px dashed rgba(255, 255, 255, 0.1);
  border-radius: var(--r-card);
  padding: 3rem 1.5rem;
  text-align: center;
}
.empty-search-state i {
  font-size: 1.75rem;
  color: #64748b;
  margin-bottom: .75rem;
}

/* ── Whitelist Page View ── */
.whitelist-page-wrap {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  width: 100%;
}

.whitelist-form-card,
.whitelist-table-card {
  background: #0c0d12;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.table-user-cell {
  display: flex;
  align-items: center;
  gap: .625rem;
}

.table-user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(16, 185, 129, 0.12);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: .8125rem;
  flex-shrink: 0;
}

.table-user-info {
  display: flex;
  flex-direction: column;
  line-height: 1.25;
}

.table-user-name {
  font-weight: 600;
  color: #f8fafc;
  font-size: .84375rem;
}

.table-user-email {
  font-size: .75rem;
  color: #64748b;
}

/* ── Access & Sharing Management View (Admin) ── */
.access-page-wrap {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  width: 100%;
}

.access-top-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.access-subtitle-hint {
  font-size: .78rem;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: .45rem;
}

.matrix-user-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .85rem 1.15rem;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  background: #07080d;
  transition: all .15s ease;
  gap: 1rem;
}
.matrix-user-item:hover {
  border-color: rgba(255, 255, 255, 0.14);
  background: #0d0f17;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}
.matrix-user-item.granted {
  border-color: rgba(16, 185, 129, 0.35);
  background: #081612;
}
.matrix-user-item.is-owner-item {
  background: #090b10;
  border-color: rgba(255, 255, 255, 0.08);
}

.matrix-user-left {
  display: flex;
  align-items: center;
  gap: .875rem;
  min-width: 0;
  flex: 1;
}

.matrix-user-right {
  display: flex;
  align-items: center;
  gap: .75rem;
  flex-shrink: 0;
}

.audit-filter-pill-group {
  display: inline-flex;
  align-items: center;
  background: #06070a;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--r-pill);
  padding: .2rem;
  gap: .2rem;
}
.audit-filter-pill {
  font-size: .75rem;
  font-weight: 600;
  padding: .25rem .75rem;
  border-radius: var(--r-pill);
  border: none;
  background: transparent;
  color: #64748b;
  cursor: pointer;
  transition: all .15s ease;
}
.audit-filter-pill.active {
  background: #151722;
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
  font-weight: 700;
}

.access-card {
  background: #0c0d12;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 1.35rem 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}

.matrix-user-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.matrix-user-card-summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .875rem 1.25rem;
  border-radius: 14px;
  background: #07080d;
  border: 1px solid rgba(255, 255, 255, 0.06);
  flex-wrap: wrap;
  gap: 1rem;
}

.matrix-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .875rem;
}

.matrix-batch-btn-group {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  flex-wrap: wrap;
}

.matrix-batch-btn {
  font-size: .75rem;
  font-weight: 600;
  padding: .35rem .75rem;
  border-radius: var(--r-pill);
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: #12131b;
  color: #cbd5e1;
  cursor: pointer;
  transition: all .15s ease;
}
.matrix-batch-btn:hover {
  background: #1a1c27;
  border-color: rgba(255, 255, 255, 0.18);
  color: #ffffff;
}

.matrix-token-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .85rem 1.15rem;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  background: #07080d;
  transition: all .15s ease;
  gap: 1rem;
}
.matrix-token-item:hover {
  border-color: rgba(255, 255, 255, 0.14);
  background: #0d0f17;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}
.matrix-token-item.granted {
  border-color: rgba(16, 185, 129, 0.35);
  background: #081612;
}
.matrix-token-item.is-owner-item {
  background: #090b10;
  border-color: rgba(255, 255, 255, 0.08);
}

.matrix-token-left {
  display: flex;
  align-items: center;
  gap: .875rem;
  min-width: 0;
  flex: 1;
}

.matrix-token-right {
  display: flex;
  align-items: center;
  gap: .75rem;
  flex-shrink: 0;
}

.perm-pill-toggle {
  display: inline-flex;
  align-items: center;
  background: #06070a;
  border: 1px solid rgba(255, 255, 255, 0.06);
  padding: .2rem;
  border-radius: var(--r-pill);
  gap: .2rem;
}

.perm-pill-btn {
  font-size: .72rem;
  font-weight: 600;
  padding: .25rem .65rem;
  border-radius: var(--r-pill);
  border: none;
  background: transparent;
  color: #64748b;
  cursor: pointer;
  transition: all .15s ease;
}
.perm-pill-btn.active {
  background: #151722;
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
  font-weight: 700;
}

.matrix-sticky-footer {
  position: sticky;
  bottom: 1.25rem;
  background: #0e1017;
  color: #ffffff;
  padding: .85rem 1.5rem;
  border-radius: var(--r-pill);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  z-index: 110;
  border: 1px solid rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(12px);
}

/* ── Multi-Share (Banyak Token ke Banyak Akun) ── */
.multishare-columns-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}
@media (max-width: 960px) {
  .multishare-columns-grid {
    grid-template-columns: 1fr;
  }
}

.multishare-scroll-list {
  max-height: 480px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: .5rem;
  padding-right: .35rem;
}

.multishare-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .75rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  background: #07080d;
  cursor: pointer;
  transition: all .15s ease;
  user-select: none;
  gap: .75rem;
}
.multishare-item:hover {
  border-color: rgba(255, 255, 255, 0.14);
  background: #0d0f17;
}
.multishare-item.selected {
  border-color: #10b981;
  background: #081813;
}
.multishare-item-left {
  display: flex;
  align-items: center;
  gap: .75rem;
  min-width: 0;
  flex: 1;
}

.multishare-control-card {
  background: #0c0d13;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 1.15rem 1.5rem;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.9);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: sticky;
  bottom: 1rem;
  z-index: 100;
}
.multishare-action-config {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  padding-bottom: .875rem;
}
.multishare-action-radios {
  display: flex;
  align-items: center;
  gap: .625rem;
  flex-wrap: wrap;
}
.multishare-radio-label {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .45rem .875rem;
  border-radius: var(--r-pill);
  border: 1.5px solid rgba(255, 255, 255, 0.08);
  background: #07080d;
  cursor: pointer;
  font-size: .8125rem;
  color: #cbd5e1;
  transition: all .15s ease;
}
.multishare-radio-label:hover {
  border-color: rgba(255, 255, 255, 0.18);
  color: #ffffff;
}
.multishare-radio-label.active.grant {
  border-color: #10b981;
  background: rgba(16, 185, 129, 0.12);
  color: #34d399;
}
.multishare-radio-label.active.revoke {
  border-color: #f43f5e;
  background: rgba(244, 63, 94, 0.15);
  color: #fb7185;
}
.multishare-execute-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}
.multishare-impact-preview {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .84rem;
  color: #94a3b8;
  font-weight: 600;
}

/* ── Token Card Bulk Selection Mode ── */
.token-card.bulk-mode {
  padding-top: 1.5rem;
  position: relative;
}
.token-card.bulk-selected {
  border-color: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.35);
  background: #081712;
}
.token-card-bulk-check {
  display: none;
  position: absolute;
  top: .75rem;
  left: .75rem;
  width: 18px;
  height: 18px;
  accent-color: #10b981;
  cursor: pointer;
  z-index: 5;
}
.token-card.bulk-mode .token-card-bulk-check {
  display: block;
}
.tokens-bulk-floating-bar {
  position: fixed;
  bottom: 1.5rem;
  left: calc(240px + (100% - 240px) / 2);
  transform: translateX(-50%);
  background: #0e1017;
  color: #ffffff;
  padding: .75rem 1.5rem;
  border-radius: var(--r-pill);
  box-shadow: 0 16px 44px rgba(0, 0, 0, 0.95);
  display: flex;
  align-items: center;
  gap: 1.25rem;
  z-index: 150;
  border: 1px solid rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(12px);
  animation: slideUpFloating .2s ease-out;
  max-width: calc(100% - 280px);
  box-sizing: border-box;
}
@keyframes slideUpFloating {
  from { opacity: 0; transform: translate(-50%, 20px); }
  to { opacity: 1; transform: translate(-50%, 0); }
}

/* ── Slide-Over Drawer for Modal ── */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.82);
  backdrop-filter: blur(14px);
  z-index: 1000;
  display: flex;
  align-items: stretch;
  justify-content: flex-end; /* Slides in from right side! */
  padding: 0;
  animation: fadeIn .2s ease;
}
.modal {
  background: #08090e;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  border-top: none;
  border-bottom: none;
  border-right: none;
  border-radius: 0;
  box-shadow: -20px 0 60px rgba(0, 0, 0, 0.95);
  width: 100%;
  max-width: 620px;
  height: 100vh;
  max-height: 100vh;
  display: flex;
  flex-direction: column;
  animation: slideDrawerRight .26s cubic-bezier(0.16, 1, 0.3, 1);
  overflow-y: auto;
}
@keyframes slideDrawerRight {
  from { transform: translateX(100%); }
  to { transform: translateX(0); }
}

.modal-header {
  padding: 1.5rem 1.75rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  background: #06070a;
}
.modal-body {
  padding: 1.75rem;
  background: #08090e;
  flex: 1;
  overflow-y: auto;
}
.modal-footer {
  padding: 1.25rem 1.75rem;
  display: flex;
  justify-content: flex-end;
  gap: .75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
  background: #06070a;
}

/* ── Mobile Drawer & Responsive Overrides ── */
.sidebar-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  z-index: 180;
}

.sidebar-close-btn {
  display: none;
  background: transparent;
  border: none;
  color: #34d399;
  font-size: 1.15rem;
  cursor: pointer;
  padding: .2rem .4rem;
  border-radius: 6px;
}
.sidebar-close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

.sidebar-mobile-user {
  display: none;
  align-items: center;
  gap: .625rem;
  padding: .625rem .75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  margin-top: .5rem;
}
.sidebar-mobile-logout {
  color: #f43f5e;
  background: rgba(244, 63, 94, 0.15);
  border-radius: 50%;
  padding: .4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
}

.whitelist-form-grid {
  display: grid;
  grid-template-columns: 1.5fr 1.2fr 1.2fr auto;
  gap: .75rem;
  align-items: center;
}

/* Form Section */
.form-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem 1.75rem; align-items: start; }
.form-2col .col { display: flex; flex-direction: column; gap: .875rem; }
.color-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: .4rem; }
.color-swatch { width: 26px; height: 26px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: transform .15s; }
.color-swatch.selected, .color-swatch:hover { transform: scale(1.2); border-color: #ffffff; }

/* ── Tablet & Mobile Breakpoint (<= 1024px) ── */
@media (max-width: 1024px) {
  .app-shell { flex-direction: column; width: 100%; max-width: 100%; }

  /* Mobile/Tablet Drawer (Sidebar) */
  .sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    width: 280px;
    max-width: 84vw;
    background: #08090d;
    transform: translateX(-100%);
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.85);
    z-index: 250;
    padding: 1.25rem 1rem;
    height: 100vh;
  }
  .sidebar.mobile-open { transform: translateX(0); }
  .sidebar-backdrop.mobile-open { display: block; }
  .sidebar-close-btn { display: flex; }
  .sidebar-mobile-user { display: flex; }

  /* Main wrapper full width on tablet/mobile */
  .main-content-wrapper {
    margin-left: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  .mobile-menu-btn {
    display: flex;
    width: 38px;
    height: 38px;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
  }

  .app-topbar {
    position: fixed;
    top: 0;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    padding: .85rem 1.5rem 0.65rem;
  }
  .main-canvas {
    padding: 5.25rem 1.5rem 3rem !important;
  }
  .topbar-search-section { max-width: 300px; }
  .tokens-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.15rem;
  }

  .tokens-bulk-floating-bar {
    left: 50%;
    transform: translateX(-50%);
    max-width: calc(100% - 2rem);
  }
}

/* ── Mobile Bottom Navigation Bar (Hidden on Desktop) ── */
.mobile-bottom-nav {
  display: none;
}

/* ── Mobile Phone Breakpoint (<= 768px) ── */
@media (max-width: 768px) {
  /* 1. Sleek Compact Fixed Mobile Topbar */
  .app-topbar {
    position: fixed;
    top: 0;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    padding: .6rem .875rem .55rem;
    background: rgba(5, 5, 8, 0.96);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    z-index: 120;
    display: flex;
    flex-direction: column;
    gap: .45rem;
    box-sizing: border-box;
  }
  .topbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    min-height: 38px;
  }
  .topbar-left-section {
    display: flex;
    align-items: center;
    gap: .65rem;
    min-width: 0;
  }
  .mobile-menu-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #0f1118;
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    cursor: pointer;
    flex-shrink: 0;
  }
  .topbar-seamless-title {
    font-size: 1.15rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .topbar-search-section { display: none !important; }
  .topbar-right-section {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-shrink: 0;
  }
  .topbar-add-btn {
    width: 36px !important;
    height: 36px !important;
    padding: 0 !important;
    border-radius: 50% !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #10b981 !important;
    color: #04100c !important;
    box-shadow: 0 2px 10px rgba(16, 185, 129, 0.35) !important;
    flex-shrink: 0;
    border: none !important;
  }
  .topbar-add-btn.hidden,
  .topbar-add-btn[style*="display: none"],
  .topbar-add-btn[style*="display:none"] {
    display: none !important;
  }
  .topbar-add-btn span { display: none !important; }
  .topbar-add-btn i { font-size: .95rem; }
  .topbar-profile-box {
    padding: 0;
    background: transparent;
    border: none;
    box-shadow: none;
  }
  .topbar-avatar {
    width: 34px;
    height: 34px;
    font-size: .8125rem;
    border-radius: 50%;
  }
  .topbar-user-meta, .topbar-logout-btn { display: none !important; }

  /* Mobile In-TopBar Search Bar Row */
  .mobile-search-row {
    display: block !important;
    width: 100%;
    margin: 0;
  }
  .mobile-search-box {
    position: relative;
    width: 100%;
  }
  .mobile-search-box .search-icon {
    position: absolute;
    left: .85rem;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-size: .8125rem;
    pointer-events: none;
  }
  .mobile-search-box input {
    height: 38px;
    width: 100%;
    box-sizing: border-box;
    padding: 0 2.25rem 0 2.25rem;
    font-size: 14px;
    border-radius: var(--r-pill);
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: #0b0c11;
    color: #f8fafc;
    outline: none;
    transition: all .15s ease;
  }
  .mobile-search-box input:focus {
    border-color: rgba(16, 185, 129, 0.4);
    background: #0e1017;
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.15);
  }
  .mobile-search-clear {
    position: absolute;
    right: .5rem;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #64748b;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: .8rem;
  }
  .mobile-search-clear:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.1);
  }

  /* 2. Mobile Main Canvas Clearance */
  .main-canvas {
    padding: calc(7.25rem + env(safe-area-inset-top, 0px)) .75rem calc(7rem + env(safe-area-inset-bottom, 24px)) !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow-x: hidden !important;
  }
  .main-canvas-inner {
    max-width: 100% !important;
    width: 100% !important;
    box-sizing: border-box !important;
    min-width: 0 !important;
  }

  .tab-lbl-desktop { display: none !important; }
  .tab-lbl-mobile { display: inline !important; }

  /* 3. Mobile Filter Tabs Row (Compact Single-Line Bar) */
  .tokens-filter-row {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: .4rem !important;
    margin-bottom: .85rem !important;
    width: 100% !important;
  }
  .tokens-tabs-bar {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: .2rem !important;
    padding: .2rem .25rem !important;
    border-radius: var(--r-pill) !important;
    background: #090a0f !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    box-sizing: border-box !important;
    overflow-x: auto !important;
    white-space: nowrap !important;
    scrollbar-width: none !important;
    -webkit-overflow-scrolling: touch !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
  }
  .tokens-tabs-bar::-webkit-scrollbar {
    display: none !important;
  }
  .tokens-tab-btn {
    flex: 0 0 auto !important;
    justify-content: center !important;
    min-height: 30px !important;
    padding: .25rem .55rem !important;
    font-size: .76rem !important;
    font-weight: 600 !important;
    gap: .25rem !important;
    border-radius: var(--r-pill) !important;
    white-space: nowrap !important;
  }
  .tab-badge-num {
    padding: .08rem .35rem !important;
    font-size: .62rem !important;
  }
  .view-switch-bar {
    display: none !important;
  }
  .tokens-filter-actions {
    display: flex !important;
    align-items: center !important;
    gap: .35rem !important;
    flex-shrink: 0 !important;
    width: auto !important;
  }
  #btn-toggle-bulk-tokens {
    width: auto !important;
    height: 30px !important;
    padding: 0 .6rem !important;
    justify-content: center !important;
    font-size: .76rem !important;
    font-weight: 600 !important;
    border-radius: var(--r-pill) !important;
    background: #0e1017 !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    color: #cbd5e1 !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    gap: .35rem !important;
  }
  .bulk-lbl-desktop { display: none !important; }
  .bulk-lbl-mobile { display: inline !important; }

  /* 4. Compact & Scannable Mobile 2FA Cards */
  .tokens-grid {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: .65rem !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
  }
  .token-card {
    display: flex !important;
    flex-direction: column !important;
    padding: .85rem .95rem !important;
    border-radius: 16px !important;
    gap: .65rem !important;
    background: #08090d !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5) !important;
    cursor: pointer;
    position: relative;
    box-sizing: border-box !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    overflow: hidden !important;
    transition: all .15s ease !important;
  }
  .token-card:active {
    transform: scale(0.985);
    background: #0f1118 !important;
  }
  .token-card.copied-pulse {
    border-color: #10b981 !important;
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.4) !important;
    background: #071510 !important;
  }
  .token-card-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: .5rem !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
  }
  .token-identity {
    display: flex !important;
    align-items: center !important;
    gap: .55rem !important;
    min-width: 0 !important;
    flex: 1 1 auto !important;
    overflow: hidden !important;
  }
  .token-icon-squircle {
    width: 34px !important;
    height: 34px !important;
    border-radius: 9px !important;
    font-size: .95rem !important;
    flex-shrink: 0 !important;
  }
  .token-issuer-row {
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    margin-top: .05rem !important;
    margin-bottom: -.2rem !important;
  }
  .token-issuer-badge {
    font-size: .66rem !important;
    font-weight: 700 !important;
    letter-spacing: .05em !important;
    color: #38bdf8 !important;
    background: rgba(56, 189, 248, 0.08) !important;
    border: 1px solid rgba(56, 189, 248, 0.2) !important;
    padding: .15rem .45rem !important;
    border-radius: 5px !important;
    line-height: 1.1 !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    max-width: 100% !important;
  }
  .token-name-section {
    width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    margin: 0 !important;
  }
  .token-title {
    font-size: .95rem !important;
    font-weight: 600 !important;
    color: #f8fafc !important;
    line-height: 1.25 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
  }
  .token-header-actions {
    display: flex !important;
    align-items: center !important;
    gap: .25rem !important;
    flex-shrink: 0 !important;
    margin-left: auto !important;
  }
  .token-action-icon-btn {
    width: 28px !important;
    height: 28px !important;
    padding: 0 !important;
    font-size: .78rem !important;
    border-radius: 50% !important;
    color: #64748b !important;
    flex-shrink: 0 !important;
    background: transparent !important;
  }
  .token-action-icon-btn:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.08) !important;
  }

  /* Compact Hero Code Box */
  .token-code-hero {
    padding: .65rem .85rem !important;
    border-radius: 12px !important;
    background: #05060a !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    min-width: 0 !important;
  }
  .token-digits-wrap {
    gap: 0 !important;
    min-width: 0 !important;
    flex: 1 1 auto !important;
    overflow: hidden !important;
  }
  .token-hero-digits {
    font-family: 'JetBrains Mono', monospace !important;
    font-size: 1.55rem !important;
    letter-spacing: .08em !important;
    line-height: 1 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    white-space: nowrap !important;
  }
  .token-gauge-box {
    display: flex !important;
    align-items: center !important;
    gap: .35rem !important;
    flex-shrink: 0 !important;
    margin-left: auto !important;
  }
  .token-gauge-svg {
    width: 26px !important;
    height: 26px !important;
    flex-shrink: 0 !important;
  }
  .token-gauge-bg {
    stroke: rgba(255, 255, 255, 0.12) !important;
    stroke-width: 3.5 !important;
  }
  .token-gauge-bar {
    stroke-width: 3.5 !important;
    stroke: #10b981 !important;
  }
  .token-timer-sec {
    font-family: 'JetBrains Mono', monospace !important;
    font-size: .8rem !important;
    font-weight: 700 !important;
    color: #cbd5e1 !important;
    min-width: 26px !important;
    text-align: right !important;
    flex-shrink: 0 !important;
    white-space: nowrap !important;
  }

  /* Hide redundant footer & click-to-copy hint on mobile */
  .token-card-footer {
    display: none !important;
  }
  .token-click-hint {
    display: none !important;
  }

  /* Mobile Bulk Selection Mode */
  .token-card.bulk-mode {
    padding-left: 2.6rem !important;
  }
  .token-card-bulk-check {
    width: 20px !important;
    height: 20px !important;
    left: .75rem !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
  }
  .tokens-bulk-floating-bar {
    left: .75rem !important;
    right: .75rem !important;
    bottom: calc(4.65rem + env(safe-area-inset-bottom, 0px)) !important;
    transform: none !important;
    max-width: calc(100% - 1.5rem) !important;
    width: auto !important;
    padding: .65rem .875rem !important;
    gap: .5rem !important;
    flex-wrap: wrap !important;
    font-size: .8125rem !important;
    border-radius: 14px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.9) !important;
    justify-content: space-between !important;
    z-index: 105 !important;
    background: #0e1017 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
  }

  /* 5. Native Mobile Bottom Sheet Modals */
  .modal-backdrop {
    align-items: flex-end !important;
    justify-content: center !important;
    padding: 0 !important;
    background: rgba(0, 0, 0, 0.85) !important;
  }
  .modal {
    width: 100% !important;
    max-width: 100% !important;
    border-radius: 22px 22px 0 0 !important;
    border-left: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-right: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-top: 1px solid rgba(255, 255, 255, 0.18) !important;
    border-bottom: none !important;
    height: auto !important;
    max-height: 88vh !important;
    box-shadow: 0 -16px 48px rgba(0, 0, 0, 0.95) !important;
    animation: slideUpBottomSheet .28s cubic-bezier(0.16, 1, 0.3, 1) !important;
    display: flex !important;
    flex-direction: column !important;
  }
  @keyframes slideUpBottomSheet {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
  }
  .modal-header {
    position: relative !important;
    padding: 1.5rem 1.25rem 1rem !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: #06070a !important;
  }
  .modal-header::before {
    content: '';
    position: absolute;
    top: 9px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 4px;
    background: rgba(255, 255, 255, 0.28);
    border-radius: 9999px;
  }
  .modal-body {
    padding: 1.25rem !important;
    background: #08090e !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
  }
  .modal-footer {
    display: flex !important;
    flex-direction: column-reverse !important;
    gap: .5rem !important;
    padding: 1rem 1.25rem !important;
    padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px)) !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: #06070a !important;
  }
  .modal-footer .btn {
    width: 100% !important;
    min-height: 44px !important;
    font-size: .9375rem !important;
    justify-content: center !important;
  }

  /* Modal Form 2-Column Responsive Collapse */
  .form-2col {
    display: flex !important;
    flex-direction: column !important;
    gap: 1.25rem !important;
  }
  .form-2col .col {
    display: flex !important;
    flex-direction: column !important;
    gap: .875rem !important;
    width: 100% !important;
  }

  /* 6. Native Mobile Bottom Navigation Bar */
  .mobile-bottom-nav {
    display: flex !important;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 58px;
    background: rgba(6, 7, 10, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    z-index: 100;
    align-items: center;
    justify-content: space-around;
    padding: 0 .25rem;
    padding-bottom: env(safe-area-inset-bottom, 0px);
    box-sizing: border-box;
  }
  .mobile-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    height: 100%;
    color: #64748b;
    background: transparent;
    border: none;
    text-decoration: none;
    font-size: .6875rem;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    transition: all .15s ease;
    -webkit-tap-highlight-color: transparent;
  }
  .mobile-nav-item:active {
    transform: scale(0.92);
  }
  .mobile-nav-item.active {
    color: #10b981;
  }
  .mobile-nav-item i {
    font-size: 1.15rem;
  }
  .mobile-nav-badge {
    position: absolute;
    top: 5px;
    right: calc(50% - 18px);
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    background: #10b981;
    color: #04100c;
    border-radius: 9999px;
    font-size: .625rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
  }

  /* 7. Whitelist & Access Views on Mobile */
  .whitelist-form-card { padding: 1rem; }
  .whitelist-form-grid {
    display: flex !important;
    flex-direction: column !important;
    gap: .75rem !important;
    width: 100% !important;
  }
  .whitelist-form-grid .input,
  .whitelist-form-grid select,
  .whitelist-form-grid button {
    width: 100% !important;
    height: 44px !important;
    font-size: 15px !important;
  }
  .whitelist-table-card { padding: 1rem; }
  #page-whitelist-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0 -0.5rem;
    padding: 0 0.5rem;
  }
  #page-whitelist-table-wrap table { min-width: 520px; }

  #view-access-wrap .tokens-tabs-bar {
    overflow-x: auto !important;
    white-space: nowrap !important;
    display: flex !important;
    flex-wrap: nowrap !important;
    -webkit-overflow-scrolling: touch !important;
    scrollbar-width: none !important;
    padding: .3rem !important;
    width: 100% !important;
  }
  #view-access-wrap .tokens-tabs-bar::-webkit-scrollbar {
    display: none !important;
  }
  #view-access-wrap .tokens-tab-btn {
    flex: 0 0 auto !important;
    white-space: nowrap !important;
    padding: .45rem .875rem !important;
    min-height: 38px !important;
  }
  .matrix-user-header {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: .75rem !important;
  }
  .matrix-user-header > div:last-child {
    min-width: 100% !important;
    max-width: 100% !important;
  }
  .matrix-user-card-summary {
    flex-direction: column;
    align-items: flex-start;
    gap: .75rem;
  }
  .matrix-token-item {
    flex-direction: column;
    align-items: stretch;
    gap: .75rem;
  }
  .matrix-token-right {
    justify-content: space-between;
    width: 100%;
    padding-top: .5rem;
    border-top: 1px dashed rgba(255, 255, 255, 0.08);
  }
  .matrix-sticky-footer {
    border-radius: 14px;
    flex-direction: column;
    gap: .75rem;
    text-align: center;
    padding: 1rem;
    bottom: calc(4.75rem + env(safe-area-inset-bottom, 0px));
  }
  .matrix-sticky-footer .btn { width: 100%; }
}

/* Narrow Screen Phones (<= 420px) */
@media (max-width: 420px) {
  .tokens-filter-row {
    gap: .3rem !important;
  }
  .tokens-tabs-bar {
    padding: .15rem .2rem !important;
    gap: .15rem !important;
  }
  .tokens-tab-btn {
    padding: .22rem .48rem !important;
    font-size: .74rem !important;
    gap: .2rem !important;
  }
  .tab-badge-num {
    padding: .06rem .3rem !important;
    font-size: .6rem !important;
  }
  #btn-toggle-bulk-tokens {
    padding: 0 .5rem !important;
    font-size: .74rem !important;
    gap: .25rem !important;
  }
}

/* Ultra-compact Phones (<= 360px, e.g. Samsung Galaxy, TECNO Spark, Fold) */
@media (max-width: 360px) {
  .main-canvas {
    padding-left: .625rem !important;
    padding-right: .625rem !important;
  }
  .tokens-filter-row {
    gap: .25rem !important;
  }
  .tokens-tabs-bar {
    padding: .12rem .18rem !important;
    gap: .12rem !important;
  }
  .tokens-tab-btn {
    padding: .18rem .38rem !important;
    font-size: .7rem !important;
    gap: .18rem !important;
    min-height: 28px !important;
  }
  .tab-badge-num {
    padding: .05rem .25rem !important;
    font-size: .56rem !important;
  }
  #btn-toggle-bulk-tokens {
    padding: 0 .4rem !important;
    font-size: .7rem !important;
    gap: .2rem !important;
    height: 28px !important;
  }
  .token-card {
    padding: .75rem .85rem !important;
    border-radius: 14px !important;
  }
  .token-icon-squircle {
    width: 32px !important;
    height: 32px !important;
    border-radius: 8px !important;
    font-size: .9rem !important;
  }
  .token-title {
    font-size: .88rem !important;
  }
  .token-code-hero {
    padding: .6rem .75rem !important;
  }
  .token-hero-digits {
    font-size: 1.4rem !important;
    letter-spacing: .06em !important;
  }
  .token-gauge-svg {
    width: 24px !important;
    height: 24px !important;
  }
  .token-timer-sec {
    font-size: .75rem !important;
    min-width: 24px !important;
  }
  .token-action-icon-btn {
    width: 26px !important;
    height: 26px !important;
    font-size: .72rem !important;
  }
  .topbar-seamless-title {
    font-size: 1.05rem !important;
  }
}

/* Extreme Micro Phones (<= 330px) */
@media (max-width: 330px) {
  .tokens-tab-btn {
    padding: .16rem .32rem !important;
    font-size: .68rem !important;
  }
  .tab-badge-num {
    padding: .04rem .22rem !important;
    font-size: .54rem !important;
  }
  #btn-toggle-bulk-tokens {
    padding: 0 .32rem !important;
    font-size: .68rem !important;
  }
  .token-card {
    padding: .7rem .75rem !important;
  }
  .token-title {
    font-size: .82rem !important;
  }
  .token-action-icon-btn {
    width: 24px !important;
    height: 24px !important;
    font-size: .68rem !important;
  }
  .token-hero-digits {
    font-size: 1.25rem !important;
    letter-spacing: .04em !important;
  }
}
</style>

<div class="app-shell">

  <!-- Mobile Backdrop -->
  <div class="sidebar-backdrop" id="sidebar-backdrop" onclick="toggleSidebarMobile()"></div>

  <!-- ── Left Sidebar (Full Height 240px) ── -->
  <aside class="sidebar" id="app-sidebar">
    <div class="sidebar-top">
      <!-- Brand & Version -->
      <div class="sidebar-brand">
        <div class="brand-identity">
          <div class="brand-icon-box">
            <img src="/logo.png" alt="TOTPVault Logo" style="width:30px;height:30px;object-fit:contain">
          </div>
          <span class="brand-title">TOTPVault</span>
        </div>
        <div style="display:flex;align-items:center;gap:.35rem">
          <span class="brand-version-chip">v2.4</span>
          <button class="sidebar-close-btn" onclick="toggleSidebarMobile()" aria-label="Tutup menu">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <!-- Navigation Section -->
      <div>
        <div class="sidebar-category">NAVIGATION</div>
        <div class="nav-list">
          <button class="nav-pill <?= $currentView === 'tokens' ? 'active' : '' ?>" onclick="switchView('tokens')" id="nav-tokens">
            <div class="nav-pill-icon"><i class="fa-solid fa-key"></i></div>
            <span>Tokens</span>
            <span class="nav-pill-count" id="count-all"><?= count($profiles) ?></span>
          </button>

          <?php if (!empty($user['is_admin'])): ?>
          <button class="nav-pill <?= $currentView === 'access' ? 'active' : '' ?>" onclick="switchView('access')" id="nav-access">
            <div class="nav-pill-icon"><i class="fa-solid fa-users-gear"></i></div>
            <span>Kelola Akses</span>
            <span class="nav-pill-count" id="access-badge-count">…</span>
          </button>

          <button class="nav-pill <?= $currentView === 'whitelist' ? 'active' : '' ?>" onclick="switchView('whitelist')" id="nav-whitelist">
            <div class="nav-pill-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <span>Whitelist Akun</span>
            <span class="nav-pill-count" id="wl-badge-count">…</span>
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Utilities Menu -->
    <div class="sidebar-bottom">
      <div>
        <div class="sidebar-category" style="padding-left:.35rem">UTILITIES</div>
        <div class="util-links">
          <a href="/tools/import-google-auth" class="sidebar-util-link">
            <i class="fa-solid fa-file-import"></i>
            <span>Import Authenticator</span>
          </a>
          <a href="/api/export-profiles" class="sidebar-util-link">
            <i class="fa-solid fa-file-export"></i>
            <span>Export Profiles</span>
          </a>
          <a href="https://github.com/token2/TOTPVault" target="_blank" rel="noopener" class="sidebar-util-link" title="Open source base engine by Token2">
            <i class="fa-brands fa-github"></i>
            <span>Base: Token2 (GPL-3.0)</span>
          </a>
        </div>
      </div>

      <!-- Mobile User Footer inside Drawer -->
      <div class="sidebar-mobile-user">
        <div class="topbar-avatar"><?= $initial ?></div>
        <div style="display:flex;flex-direction:column;min-width:0;flex:1">
          <span style="color:#ffffff;font-weight:700;font-size:.84rem" class="truncate"><?= htmlspecialchars($user['name'] ?? 'User') ?></span>
          <span style="color:#7a9c95;font-size:.72rem" class="truncate"><?= htmlspecialchars($user['email']) ?></span>
        </div>
        <a href="/auth/logout" class="sidebar-mobile-logout" title="Sign out / Keluar">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>

    </div>
  </aside>

  <!-- ── Main Content Shell (Topbar + Main Canvas) ── -->
  <div class="main-content-wrapper">

    <!-- Seamless Topbar: Title -> Searchbar -> Add Token -> Profile -->
    <header class="app-topbar">
      <div class="topbar-inner">
        <!-- 1. Title (+ Mobile Toggle) -->
        <div class="topbar-left-section">
          <button class="mobile-menu-btn" onclick="toggleSidebarMobile()" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
          </button>
          <h1 class="topbar-seamless-title" id="topbar-main-title"><?= $currentView === 'whitelist' ? 'Whitelist Akun' : ($currentView === 'access' ? 'Kelola Akses' : 'Tokens') ?></h1>
        </div>

        <!-- 2. Searchbar (Desktop) -->
        <div class="topbar-search-section">
          <div class="search-pill-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="global-search" placeholder="Cari token 2FA… (Ctrl + K atau /)" oninput="handleGlobalSearch(this.value)">
            <span class="search-kbd">/</span>
          </div>
        </div>

        <!-- 3. Add Token & 4. Profile -->
        <div class="topbar-right-section">
          <button class="btn btn-dark topbar-add-btn" onclick="openAddModal()" id="topbar-add-token-btn" style="<?= in_array($currentView, ['whitelist', 'access']) ? 'display:none' : '' ?>">
            <i class="fa-solid fa-plus"></i>
            <span>Add Token</span>
          </button>

          <div class="topbar-profile-box">
            <div class="topbar-avatar" id="topbar-user-avatar">
              <?php if ($avatar): ?>
                <img src="<?= $avatar ?>" alt="" style="width:100%;height:100%;object-fit:cover" onerror="this.remove();document.getElementById('topbar-user-avatar').textContent='<?= $initial ?>';">
              <?php else: ?>
                <?= $initial ?>
              <?php endif; ?>
            </div>
            <div class="topbar-user-meta">
              <span class="topbar-user-name truncate"><?= htmlspecialchars($user['name'] ?? 'User') ?></span>
              <span class="topbar-user-role truncate"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <a href="/auth/logout" class="topbar-logout-btn" title="Sign out / Keluar">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Mobile In-TopBar Search Bar (stays fixed at top) -->
      <div class="mobile-search-row">
        <div class="mobile-search-box">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input type="text" id="mobile-search" placeholder="Cari token 2FA…" oninput="handleGlobalSearch(this.value)">
          <button type="button" class="mobile-search-clear" id="mobile-search-clear" onclick="clearSearch()" aria-label="Clear search">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>
    </header>

    <!-- ── Main Canvas (Seamless Flow with Topbar) ── -->
    <main class="main-canvas">
      <div class="main-canvas-inner">

      <!-- ════ VIEW 1: TOKENS (DIRECT CARD GRID WITH TABNATION) ════ -->
      <div id="view-tokens-wrap" style="<?= $currentView === 'whitelist' ? 'display:none' : '' ?>">

      <!-- Dedicated Filter Tabs Row (Separated with elegant breathing room) -->
      <div class="tokens-filter-row">
        <div class="tokens-tabs-bar">
          <button class="tokens-tab-btn active" onclick="filterTab('all')" id="tab-all">
            <span>All</span>
            <span class="tab-badge-num" id="tab-count-all"><?= count($profiles) ?></span>
          </button>
          <button class="tokens-tab-btn" onclick="filterTab('mine')" id="tab-mine">
            <span class="tab-lbl-desktop">My Tokens</span>
            <span class="tab-lbl-mobile">Mine</span>
            <span class="tab-badge-num" id="tab-count-mine"><?= count(array_filter($profiles, fn($p)=>$p['is_owner'])) ?></span>
          </button>
          <button class="tokens-tab-btn" onclick="filterTab('shared')" id="tab-shared">
            <span>Shared</span>
            <span class="tab-badge-num" id="tab-count-shared"><?= count(array_filter($profiles, fn($p)=>!$p['is_owner'])) ?></span>
          </button>
        </div>

        <div class="tokens-filter-actions">
          <!-- Dual View Switcher (Cards Grid vs Dense DevOps Table) -->
          <div class="view-switch-bar">
            <button class="view-switch-btn active" id="btn-view-grid" onclick="setTokensViewMode('grid')" title="Tampilan Kartu Visual">
              <i class="fa-solid fa-grip"></i>
            </button>
            <button class="view-switch-btn" id="btn-view-table" onclick="setTokensViewMode('table')" title="Tampilan Tabel DevOps Compact">
              <i class="fa-solid fa-table-list"></i>
            </button>
          </div>
 
          <!-- Reset Custom Order Button (appears when user has custom sorted tokens) -->
          <button class="btn btn-secondary btn-sm" id="btn-reset-order" onclick="resetTokenOrder()" title="Kembalikan tata urutan token ke default">
            <i class="fa-solid fa-arrow-rotate-left"></i>
            <span class="reset-order-lbl-desktop">Reset Urutan</span>
            <span class="reset-order-lbl-mobile">Reset</span>
          </button>

          <?php if (!empty($user['is_admin'])): ?>
          <button class="btn btn-secondary btn-sm" id="btn-toggle-bulk-tokens" onclick="toggleTokensBulkMode()" title="Pilih banyak token untuk sharing sekaligus">
            <i class="fa-solid fa-list-check"></i>
            <span class="bulk-lbl-desktop">Pilih Banyak Token</span>
            <span class="bulk-lbl-mobile">Pilih</span>
          </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- DIRECTLY THE TOKENS GRID -->
      <?php if (empty($profiles)): ?>
      <div style="background:#0c0d12;border:1px dashed rgba(255,255,255,0.1);border-radius:16px;padding:3rem 1.5rem;text-align:center;max-width:440px;margin:1rem auto">
        <div style="width:48px;height:48px;border-radius:50%;background:rgba(16,185,129,0.15);color:#10b981;display:inline-flex;align-items:center;justify-content:center;font-size:1.35rem;margin-bottom:.75rem">
          <i class="fa-solid fa-key"></i>
        </div>
        <h2 style="font-size:1.1rem;font-weight:800;color:#ffffff;margin-bottom:.35rem">Belum ada token 2FA</h2>
        <p style="color:#7a9c95;font-size:.8125rem;margin-bottom:1.25rem">
          Tambahkan token 2FA pertama Anda. Kunci rahasia master disimpan aman di database.
        </p>
        <button class="btn btn-dark btn-sm" onclick="openAddModal()">+ Add Token</button>
      </div>
      <?php else: ?>
      <div class="tokens-grid" id="profiles-grid">
        <?php foreach ($profiles as $p): ?>
        <?php
          $pId     = (int)$p['id'];
          $pName   = htmlspecialchars($p['name']);
          $pIssuer = htmlspecialchars($p['issuer'] ?? '');
          $pColor  = htmlspecialchars($p['color']);
          $pAlgo   = htmlspecialchars($p['algorithm']);
          $pDigits = (int)$p['digits'];
          $pPeriod = (int)$p['period'];
          $isOwner = (bool)$p['is_owner'];
          $canEdit = (bool)$p['can_edit'];
          $pHide   = (int)($p['hide_code'] ?? 0);
          $pIcon   = htmlspecialchars($p['icon'] ?? 'fa-shield-halved');

          $initCode = $p['initial_code'] ?? '';
          $initRem  = (int)($p['initial_remaining'] ?? $pPeriod);
          $formatted = '--- ---';
          if ($initCode) {
              if ($pDigits === 6) {
                  $formatted = substr($initCode, 0, 3) . ' ' . substr($initCode, 3);
              } elseif ($pDigits === 8) {
                  $formatted = substr($initCode, 0, 4) . ' ' . substr($initCode, 4);
              } else {
                  $formatted = $initCode;
              }
          }
          $circ = 81.68;
          $initOffset = $circ * (1 - ($initRem / ($pPeriod ?: 30)));
        ?>
        <div class="token-card" id="card-<?= $pId ?>"
             data-id="<?= $pId ?>"
             data-period="<?= $pPeriod ?>"
             data-owner="<?= $isOwner ? '1' : '0' ?>"
             data-name="<?= strtolower($pName) ?>"
             data-issuer="<?= strtolower($pIssuer) ?>"
             onclick="handleCardClick(event, <?= $pId ?>)">

          <?php if (!empty($user['is_admin'])): ?>
          <input type="checkbox" class="token-card-bulk-check" id="token-check-<?= $pId ?>" onchange="onTokenGridCheckChange(<?= $pId ?>, this.checked)" onclick="event.stopPropagation()">
          <?php endif; ?>

          <!-- Header Topbar: Service Identity & Action Buttons -->
          <div class="token-card-header">
            <div class="token-identity">
              <div class="token-drag-handle" title="Tahan dan geser ikon ini untuk mengatur urutan" onclick="event.stopPropagation()">
                <i class="fa-solid fa-grip-vertical"></i>
              </div>
              <div class="token-icon-squircle" style="background:<?= $pColor ?>">
                <i class="<?= resolveIconClass($pIcon, $pIssuer, $pName) ?>"></i>
              </div>
            </div>

            <!-- Action Menu -->
            <div class="token-header-actions">
              <?php if ($isOwner): ?>
              <button class="token-action-icon-btn" onclick="openShareModal(<?= $pId ?>, '<?= addslashes($pName) ?>'); event.stopPropagation();" title="Share with team">
                <i class="fa-solid fa-share-nodes"></i>
              </button>
              <?php endif; ?>
              <?php if ($canEdit): ?>
              <button class="token-action-icon-btn" onclick="openEditModal(<?= $pId ?>); event.stopPropagation();" title="Edit token">
                <i class="fa-solid fa-pen"></i>
              </button>
              <?php endif; ?>
              <?php if ($isOwner): ?>
              <button class="token-action-icon-btn" onclick="deleteProfile(<?= $pId ?>, '<?= addslashes($pName) ?>'); event.stopPropagation();" title="Delete" style="color:#ef4444">
                <i class="fa-solid fa-trash-can"></i>
              </button>
              <?php endif; ?>
            </div>
          </div>

          <!-- Full-Width Issuer Badge Row -->
          <div class="token-issuer-row">
            <span class="token-issuer-badge" title="<?= $pIssuer ?: 'Service' ?>">
              <i class="fa-solid fa-shield-halved"></i>
              <?= $pIssuer ?: 'Service' ?>
            </span>
          </div>

          <!-- Dedicated Full-Width Token Account Title -->
          <div class="token-name-section">
            <h3 class="token-title" title="<?= $pName ?>"><?= $pName ?></h3>
          </div>

          <!-- Heroic OTP Digits (Click to Copy Anywhere) -->
          <div class="token-code-hero" title="Click anywhere to copy OTP code">
            <div class="token-digits-wrap">
              <div class="token-hero-digits" id="code-<?= $pId ?>"
                   data-raw-code="<?= htmlspecialchars($initCode) ?>"
                   <?= $pHide ? 'data-hidden="1"' : '' ?>>
                <?= $pHide ? '••••••' : htmlspecialchars($formatted) ?>
              </div>
              <span class="token-click-hint" id="hint-<?= $pId ?>">
                <i class="fa-regular fa-clone"></i> Click to copy
              </span>
            </div>

            <div class="token-gauge-box">
              <svg class="token-gauge-svg" viewBox="0 0 32 32">
                <circle class="token-gauge-bg" cx="16" cy="16" r="13"></circle>
                <circle class="token-gauge-bar" id="gauge-<?= $pId ?>" cx="16" cy="16" r="13"
                        stroke-dasharray="81.68" stroke-dashoffset="<?= number_format($initOffset, 2) ?>"></circle>
              </svg>
              <span class="token-timer-sec" id="timer-<?= $pId ?>"><?= $initRem ?>s</span>
            </div>
          </div>

          <!-- Footer: Meta & Instant Copy -->
          <div class="token-card-footer">
            <div class="token-meta-pills">
              <span class="token-meta-chip"><?= $pAlgo ?></span>
              <span class="token-meta-chip"><?= $pPeriod ?>s</span>
              <span class="token-meta-chip" style="color:<?= $isOwner ? 'var(--emerald-h)' : 'var(--teal-700)' ?>">
                <?= $isOwner ? 'Personal' : 'Shared' ?>
              </span>
            </div>

            <button class="token-copy-pill" id="copy-btn-<?= $pId ?>" onclick="copyCode(<?= $pId ?>); event.stopPropagation();">
              <i class="fa-regular fa-clone"></i>
              <span id="copy-lbl-<?= $pId ?>">Copy</span>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Dense DevOps Table View -->
      <div class="tokens-table-wrap" id="profiles-table-wrap" style="display:none">
        <table class="tokens-dense-table" id="profiles-table">
          <thead>
            <tr>
              <?php if (!empty($user['is_admin'])): ?>
              <th style="width:40px;text-align:center">
                <input type="checkbox" id="token-table-check-all" onchange="bulkSelectAllTokensTable(this.checked)" title="Pilih Semua">
              </th>
              <?php endif; ?>
              <th>Service / Akun</th>
              <th>Live OTP Code (Click to Copy)</th>
              <th>Countdown</th>
              <th>Period &amp; Algo</th>
              <th>Status</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($profiles as $p): ?>
            <?php
              $pId     = (int)$p['id'];
              $pName   = htmlspecialchars($p['name']);
              $pIssuer = htmlspecialchars($p['issuer'] ?? '');
              $pColor  = htmlspecialchars($p['color']);
              $pAlgo   = htmlspecialchars($p['algorithm']);
              $pDigits = (int)$p['digits'];
              $pPeriod = (int)$p['period'];
              $isOwner = (bool)$p['is_owner'];
              $canEdit = (bool)$p['can_edit'];
              $pHide   = (int)($p['hide_code'] ?? 0);
              $pIcon   = htmlspecialchars($p['icon'] ?? 'fa-shield-halved');

              $initCode = $p['initial_code'] ?? '';
              $initRem  = (int)($p['initial_remaining'] ?? $pPeriod);
              $formatted = '--- ---';
              if ($initCode) {
                  if ($pDigits === 6) {
                      $formatted = substr($initCode, 0, 3) . ' ' . substr($initCode, 3);
                  } elseif ($pDigits === 8) {
                      $formatted = substr($initCode, 0, 4) . ' ' . substr($initCode, 4);
                  } else {
                      $formatted = $initCode;
                  }
              }
              $circ = 81.68;
              $initOffset = $circ * (1 - ($initRem / ($pPeriod ?: 30)));
            ?>
            <tr class="token-row" id="row-<?= $pId ?>"
                data-id="<?= $pId ?>"
                data-period="<?= $pPeriod ?>"
                data-owner="<?= $isOwner ? '1' : '0' ?>"
                data-name="<?= strtolower($pName) ?>"
                data-issuer="<?= strtolower($pIssuer) ?>">
              <?php if (!empty($user['is_admin'])): ?>
              <td style="text-align:center">
                <input type="checkbox" class="token-table-bulk-check" id="token-table-check-<?= $pId ?>" onchange="onTokenTableCheckChange(<?= $pId ?>, this.checked)">
              </td>
              <?php endif; ?>
              <td>
                <div style="display:flex;align-items:center;gap:.6rem">
                  <span class="token-row-drag-handle" title="Tahan dan geser untuk ubah urutan">
                    <i class="fa-solid fa-grip-vertical"></i>
                  </span>
                  <div class="token-icon-squircle" style="background:<?= $pColor ?>;width:34px;height:34px;font-size:.9rem;border-radius:9px;flex-shrink:0">
                    <i class="<?= resolveIconClass($pIcon, $pIssuer, $pName) ?>"></i>
                  </div>
                  <div style="min-width:0">
                    <div style="font-weight:700;color:#ffffff;font-size:.875rem" class="truncate"><?= $pName ?></div>
                    <div style="font-size:.72rem;color:#7a9c95"><?= $pIssuer ?: 'Service' ?></div>
                  </div>
                </div>
              </td>
              <td>
                <button type="button" class="table-otp-button" id="table-code-btn-<?= $pId ?>" onclick="copyCode(<?= $pId ?>)" title="Click to copy OTP code">
                  <span id="table-code-<?= $pId ?>" style="white-space:nowrap" data-raw-code="<?= htmlspecialchars($initCode) ?>" <?= $pHide ? 'data-hidden="1"' : '' ?>>
                    <?= $pHide ? '••••••' : htmlspecialchars($formatted) ?>
                  </span>
                  <i class="fa-regular fa-clone" style="font-size:.78rem;color:#7a9c95"></i>
                </button>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:.5rem">
                  <div class="token-gauge-box" style="width:26px;height:26px">
                    <svg class="token-gauge-svg" viewBox="0 0 32 32">
                      <circle class="token-gauge-bg" cx="16" cy="16" r="13"></circle>
                      <circle class="token-gauge-bar" id="table-gauge-<?= $pId ?>" cx="16" cy="16" r="13"
                              stroke-dasharray="81.68" stroke-dashoffset="<?= number_format($initOffset, 2) ?>"></circle>
                    </svg>
                  </div>
                  <span style="font-family:'JetBrains Mono',monospace;font-size:.8rem;font-weight:700;color:#cbd5e1" id="table-timer-<?= $pId ?>"><?= $initRem ?>s</span>
                </div>
              </td>
              <td>
                <span class="token-meta-chip"><?= $pAlgo ?></span>
                <span class="token-meta-chip"><?= $pPeriod ?>s</span>
              </td>
              <td>
                <span class="token-meta-chip" style="color:<?= $isOwner ? 'var(--emerald-h)' : 'var(--teal-700)' ?>">
                  <?= $isOwner ? 'Personal' : 'Shared' ?>
                </span>
              </td>
              <td style="text-align:right">
                <div style="display:inline-flex;align-items:center;gap:.35rem">
                  <button class="token-action-icon-btn" onclick="copyCode(<?= $pId ?>)" title="Copy Code" style="background:#151722">
                    <i class="fa-regular fa-clone"></i>
                  </button>
                  <?php if ($isOwner): ?>
                  <button class="token-action-icon-btn" onclick="openShareModal(<?= $pId ?>, '<?= addslashes($pName) ?>')" title="Share with team" style="background:#151722">
                    <i class="fa-solid fa-share-nodes"></i>
                  </button>
                  <?php endif; ?>
                  <?php if ($canEdit): ?>
                  <button class="token-action-icon-btn" onclick="openEditModal(<?= $pId ?>)" title="Edit token" style="background:#151722">
                    <i class="fa-solid fa-pen"></i>
                  </button>
                  <?php endif; ?>
                  <?php if ($isOwner): ?>
                  <button class="token-action-icon-btn" onclick="deleteProfile(<?= $pId ?>, '<?= addslashes($pName) ?>')" title="Delete" style="background:#151722;color:#ef4444">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Floating Bulk Selection Bar for Tokens Grid -->
      <?php if (!empty($user['is_admin'])): ?>
      <div id="tokens-grid-bulk-bar" class="tokens-bulk-floating-bar" style="display:none">
        <div style="display:flex;align-items:center;gap:.625rem">
          <i class="fa-solid fa-layer-group" style="color:var(--emerald-border);font-size:1.1rem"></i>
          <span style="font-weight:700;font-size:.9rem"><span id="tokens-grid-selected-count">0</span> Token Dipilih</span>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem">
          <button class="btn btn-secondary btn-sm" onclick="bulkSelectAllTokensGrid(true)" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.25)">
            Pilih Semua
          </button>
          <button class="btn btn-secondary btn-sm" onclick="bulkSelectAllTokensGrid(false)" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.25)">
            Batal
          </button>
          <button class="btn btn-primary btn-sm" onclick="proceedTokensGridToMultiShare()" style="background:#10b981;border-color:#10b981;color:#fff;font-weight:700;box-shadow:0 2px 10px rgba(16,185,129,0.4)">
            <i class="fa-solid fa-share-nodes"></i>
            <span>Atur Sharing ke Banyak Akun &rarr;</span>
          </button>
        </div>
      </div>
      <?php endif; ?>

    </div>


    <!-- ════ VIEW 2: WHITELIST AKUN (DEDICATED FULL-PAGE VIEW, NO POPUP) ════ -->
    <div id="view-whitelist-wrap" style="<?= $currentView !== 'whitelist' ? 'display:none' : '' ?>" class="whitelist-page-wrap">
      <!-- Whitelist Subtitle / Security Badge -->
      <div style="margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem;">
        <span class="badge badge-green" style="font-size:.75rem; padding: .35rem .75rem;">
          <i class="fa-solid fa-shield-halved"></i> Google OAuth Protected — Otorisasi akun Google yang diizinkan untuk mengakses instance TOTPVault secara dinamis.
        </span>
      </div>

      <!-- Top Card: Form Otorisasi Akun Baru -->
      <div class="whitelist-form-card">
        <div style="font-size:.9375rem;font-weight:700;color:#ffffff;margin-bottom:.875rem;display:flex;align-items:center;gap:.5rem">
          <i class="fa-solid fa-user-plus" style="color:var(--emerald)"></i>
          <span>Otorisasi Akun Google Baru</span>
        </div>
        <div class="whitelist-form-grid">
          <input class="input" id="page-wl-email" type="email" placeholder="email.google@gmail.com" style="font-size:.875rem">
          <input class="input" id="page-wl-name" type="text" placeholder="Nama Lengkap / Panggilan" style="font-size:.875rem">
          <select class="input" id="page-wl-role" style="font-size:.875rem;cursor:pointer">
            <option value="user">Pengguna Standar</option>
            <option value="admin">Administrator</option>
          </select>
          <button class="btn btn-dark" onclick="addWhitelistUserFromPage()" style="padding:0 1.25rem;font-weight:600">
            + Otorisasi Akun
          </button>
        </div>
        <div style="font-size:.72rem;color:var(--ink-4);margin-top:.5rem">
          * Akun yang ditambahkan di sini dapat langsung login melalui Google OAuth tanpa perlu konfigurasi ulang server.
        </div>
      </div>

      <!-- Bottom Card: SaaS Table of Whitelisted Users -->
      <div class="whitelist-table-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.625rem">
          <div style="font-size:1rem;font-weight:700;color:var(--ink)">
            Akun Terdaftar (<span id="page-wl-total">0</span>)
          </div>
          <div style="width:240px">
            <input class="input" type="text" id="page-wl-search" placeholder="Cari nama atau email…" oninput="filterWhitelistTable(this.value)" style="height:34px;font-size:.8125rem">
          </div>
        </div>

        <div id="page-whitelist-table-wrap" style="overflow-x:auto">
          <div style="padding:2rem;text-align:center;color:var(--ink-4)">Memuat data whitelist…</div>
        </div>
      </div>
    </div>

    <!-- ════ VIEW 3: KELOLA AKSES (DUAL PERSPECTIVE IAM HUB) ════ -->
    <div id="view-access-wrap" style="<?= $currentView !== 'access' ? 'display:none' : '' ?>" class="access-page-wrap">
      
      <!-- Sub-Tabs Bar: Dual Perspective Hub (Pengguna vs Token vs Master Audit) -->
      <div class="access-top-toolbar">
        <div class="tokens-tabs-bar">
          <button class="tokens-tab-btn active" id="subtab-btn-user" onclick="switchAccessSubTab('user')">
            <i class="fa-solid fa-user-shield"></i>
            <span>Berdasarkan Pengguna</span>
          </button>
          <button class="tokens-tab-btn" id="subtab-btn-token" onclick="switchAccessSubTab('token')">
            <i class="fa-solid fa-key"></i>
            <span>Berdasarkan Token</span>
          </button>
          <button class="tokens-tab-btn" id="subtab-btn-audit" onclick="switchAccessSubTab('audit')">
            <i class="fa-solid fa-list-check"></i>
            <span>Master Audit & Cabut Massal</span>
            <span class="tab-badge-num" id="badge-audit-count">0</span>
          </button>
        </div>
        <div class="access-subtitle-hint">
          <i class="fa-solid fa-shield-halved" style="color:var(--teal-600)"></i>
          <span>Zero-Trust Access Control & Secret Delegation Hub</span>
        </div>
      </div>

      <!-- ════ SUB-VIEW 1: AKSES PER PENGGUNA (USER-CENTRIC) ════ -->
      <div id="access-subview-user" style="display:flex;flex-direction:column;gap:1.25rem">
        
        <!-- User Selector & Summary Card -->
        <div class="access-card">
          <div class="matrix-user-header">
            <div>
              <div style="font-size:1rem;font-weight:700;color:var(--ink)">Pilih Pengguna Target</div>
              <div style="font-size:.75rem;color:var(--ink-3);margin-top:.15rem">Pilih akun kolaborator untuk melihat dan mengubah seluruh hak akses tokennya secara massal.</div>
            </div>
            <div style="min-width:280px;max-width:400px;width:100%">
              <select id="access-user-select" class="input" onchange="onAccessUserSelected(this.value)" style="height:42px;font-size:.875rem;font-weight:600;cursor:pointer">
                <option value="" disabled selected>Memuat daftar pengguna…</option>
              </select>
            </div>
          </div>

          <!-- Target User Profile Summary Banner -->
          <div class="matrix-user-card-summary" id="access-user-summary-box" style="display:none">
            <div style="display:flex;align-items:center;gap:.75rem">
              <div class="table-user-avatar" id="matrix-user-avatar" style="width:40px;height:40px;font-size:1rem">U</div>
              <div>
                <div style="display:flex;align-items:center;gap:.5rem">
                  <span style="font-weight:700;color:var(--ink);font-size:.95rem" id="matrix-user-name">-</span>
                  <span id="matrix-user-role-badge" class="badge badge-teal" style="font-size:.6875rem">Member</span>
                </div>
                <span style="color:var(--ink-3);font-size:.78rem" id="matrix-user-email">-</span>
              </div>
            </div>

            <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
              <span class="badge badge-green" id="matrix-access-summary-badge" style="font-size:.78rem;padding:.35rem .75rem">
                Memiliki akses ke 0 dari 0 token
              </span>
              <button class="btn btn-ghost btn-sm" style="color:var(--red);background:var(--red-l);border:1px solid rgba(239,68,68,0.2)" onclick="revokeAllAccessCurrentTarget()">
                <i class="fa-solid fa-user-xmark"></i> Cabut Semua Akses (Offboarding)
              </button>
            </div>
          </div>
        </div>

        <!-- Token Matrix Checklist Card -->
        <div class="access-card" id="access-matrix-container" style="display:none">
          <div class="matrix-toolbar">
            <div style="display:flex;align-items:center;gap:.75rem;flex:1;max-width:340px">
              <input type="text" id="matrix-search" class="input" placeholder="Cari token atau issuer…" oninput="filterMatrixTokens(this.value)" style="height:36px;font-size:.8125rem">
            </div>

            <div class="matrix-batch-btn-group">
              <button type="button" class="matrix-batch-btn" onclick="matrixSelectAll(true)">
                <i class="fa-regular fa-square-check" style="color:var(--emerald-h)"></i> Berikan Semua
              </button>
              <button type="button" class="matrix-batch-btn" onclick="matrixSelectAll(false)">
                <i class="fa-regular fa-square"></i> Cabut Semua
              </button>
              <button type="button" class="matrix-batch-btn" onclick="matrixSetAllPermissions(0)">
                <i class="fa-solid fa-eye"></i> Set Semua Read-Only
              </button>
              <button type="button" class="matrix-batch-btn" onclick="matrixSetAllPermissions(1)">
                <i class="fa-solid fa-pen"></i> Set Semua Full Edit
              </button>
            </div>
          </div>

          <!-- List of Tokens in Matrix -->
          <div id="matrix-tokens-list" style="display:flex;flex-direction:column;gap:.625rem">
            <div style="padding:2rem;text-align:center;color:var(--ink-4)">Memuat token matrix…</div>
          </div>

          <!-- Sticky Action Bar -->
          <div class="matrix-sticky-footer" id="matrix-footer-bar">
            <div style="display:flex;align-items:center;gap:.625rem">
              <div style="width:10px;height:10px;border-radius:50%;background:#10b981;box-shadow:0 0 8px #10b981"></div>
              <span id="matrix-status-text" style="font-size:.84rem;font-weight:600">Sesuaikan centang dan hak akses di atas, lalu simpan perubahan.</span>
            </div>
            <div style="display:flex;align-items:center;gap:.625rem">
              <button class="btn btn-secondary btn-sm" onclick="reloadCurrentMatrix()">
                Reset
              </button>
              <button class="btn btn-primary btn-sm" id="btn-save-matrix" onclick="saveAccessMatrix()" style="padding:.5rem 1.25rem">
                <i class="fa-solid fa-check"></i> Simpan Akses Pengguna
              </button>
            </div>
          </div>

        </div>

      </div>

      <!-- ════ SUB-VIEW 2: AKSES PER TOKEN (TOKEN-CENTRIC) ════ -->
      <div id="access-subview-token" style="display:none;flex-direction:column;gap:1.25rem">
        
        <!-- Token Selector & Summary Card -->
        <div class="access-card">
          <div class="matrix-user-header">
            <div>
              <div style="font-size:1rem;font-weight:700;color:var(--ink)">Pilih Token Vault</div>
              <div style="font-size:.75rem;color:var(--ink-3);margin-top:.15rem">Audit dan delegasikan hak akses kunci 2FA ini ke personil atau anggota tim yang berwenang.</div>
            </div>
            <div style="min-width:280px;max-width:420px;width:100%">
              <select id="access-token-select" class="input" onchange="onAccessTokenSelected(this.value)" style="height:42px;font-size:.875rem;font-weight:600;cursor:pointer">
                <option value="" disabled selected>Memuat daftar token…</option>
              </select>
            </div>
          </div>

          <!-- Target Token Profile Summary Banner -->
          <div class="matrix-user-card-summary" id="access-token-summary-box" style="display:none">
            <div style="display:flex;align-items:center;gap:.75rem">
              <div class="token-icon-squircle" id="token-summary-squircle" style="width:40px;height:40px;font-size:1.15rem;background:#0b332d">
                <i id="token-summary-icon" class="fa-solid fa-key"></i>
              </div>
              <div>
                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                  <span style="font-weight:700;color:var(--ink);font-size:.95rem" id="token-summary-name">-</span>
                  <span id="token-summary-issuer-badge" class="token-issuer-tag" style="font-size:.6875rem">-</span>
                </div>
                <span style="color:var(--ink-3);font-size:.78rem" id="token-summary-owner">Dibuat oleh: -</span>
              </div>
            </div>

            <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
              <span class="badge badge-green" id="token-access-summary-badge" style="font-size:.78rem;padding:.35rem .75rem">
                Dibagikan ke 0 dari 0 anggota
              </span>
            </div>
          </div>
        </div>

        <!-- Token Member Delegation Checklist Card -->
        <div class="access-card" id="access-token-matrix-container" style="display:none">
          <div class="matrix-toolbar">
            <div style="display:flex;align-items:center;gap:.75rem;flex:1;max-width:340px">
              <input type="text" id="token-matrix-search" class="input" placeholder="Cari nama atau email anggota…" oninput="filterTokenMatrixUsers(this.value)" style="height:36px;font-size:.8125rem">
            </div>

            <div class="matrix-batch-btn-group">
              <button type="button" class="matrix-batch-btn" onclick="tokenMatrixSelectAll(true)">
                <i class="fa-regular fa-square-check" style="color:var(--emerald-h)"></i> Berikan ke Semua
              </button>
              <button type="button" class="matrix-batch-btn" onclick="tokenMatrixSelectAll(false)">
                <i class="fa-regular fa-square"></i> Cabut Semua
              </button>
              <button type="button" class="matrix-batch-btn" onclick="tokenMatrixSetAllPermissions(0)">
                <i class="fa-solid fa-eye"></i> Set Semua Read-Only
              </button>
              <button type="button" class="matrix-batch-btn" onclick="tokenMatrixSetAllPermissions(1)">
                <i class="fa-solid fa-pen"></i> Set Semua Full Edit
              </button>
            </div>
          </div>

          <!-- List of Users for this Token -->
          <div id="token-matrix-users-list" style="display:flex;flex-direction:column;gap:.625rem">
            <div style="padding:2rem;text-align:center;color:var(--ink-4)">Memuat anggota…</div>
          </div>

          <!-- Sticky Action Bar -->
          <div class="matrix-sticky-footer" id="token-matrix-footer-bar">
            <div style="display:flex;align-items:center;gap:.625rem">
              <div style="width:10px;height:10px;border-radius:50%;background:#10b981;box-shadow:0 0 8px #10b981"></div>
              <span id="token-matrix-status-text" style="font-size:.84rem;font-weight:600">Sesuaikan akses anggota untuk token ini, lalu simpan perubahan.</span>
            </div>
            <div style="display:flex;align-items:center;gap:.625rem">
              <button class="btn btn-secondary btn-sm" onclick="reloadCurrentTokenMatrix()">
                Reset
              </button>
              <button class="btn btn-primary btn-sm" id="btn-save-token-matrix" onclick="saveTokenMatrix()" style="padding:.5rem 1.25rem">
                <i class="fa-solid fa-check"></i> Simpan Delegasi Token
              </button>
            </div>
          </div>
        </div>

      </div>

      <!-- ════ SUB-VIEW C: MULTI-SHARE MASSAL (BANYAK TOKEN KE BANYAK AKUN) ════ -->
      <div id="access-subview-multishare" style="display:none;flex-direction:column;gap:1.25rem">
        
        <!-- Header Info Card -->
        <div style="background:linear-gradient(135deg, rgba(6,78,59,0.06) 0%, rgba(13,148,136,0.08) 100%);border:1px solid rgba(13,148,136,0.25);border-radius:14px;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
          <div style="display:flex;align-items:center;gap:.75rem">
            <div style="width:38px;height:38px;border-radius:10px;background:var(--emerald-h);color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;box-shadow:0 2px 8px rgba(5,150,105,0.3);flex-shrink:0">
              <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
              <div style="font-weight:700;font-size:.925rem;color:var(--teal-900)">Multi-Share Massal: Banyak Token ke Banyak Akun Sekaligus</div>
              <div style="font-size:.78rem;color:var(--ink-3);margin-top:.15rem">Pilih beberapa token di kolom kiri, lalu tentukan akun-akun kolaborator di kolom kanan. Tentukan izin dan eksekusi serentak.</div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:.5rem">
            <button class="btn btn-secondary btn-sm" onclick="resetMultiShareSelection()">
              <i class="fa-solid fa-rotate-left"></i> Reset Pilihan
            </button>
          </div>
        </div>

        <!-- 2-Column Split Workspace -->
        <div class="multishare-columns-grid">
          
          <!-- LEFT COLUMN: PILIH TOKEN 2FA -->
          <div class="access-card" style="padding:1.25rem;gap:.875rem">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap">
              <div>
                <div style="font-size:.95rem;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:.5rem">
                  <span>1. Pilih Token 2FA</span>
                  <span class="badge badge-teal" id="multishare-token-count-badge">0 Terpilih</span>
                </div>
                <div style="font-size:.75rem;color:var(--ink-4);margin-top:.15rem">Centang token yang ingin didistribusikan</div>
              </div>
              <div style="display:flex;align-items:center;gap:.35rem">
                <button type="button" class="matrix-batch-btn" onclick="multiShareSelectAllTokens(true)">
                  <i class="fa-regular fa-square-check" style="color:var(--emerald-h)"></i> Semua
                </button>
                <button type="button" class="matrix-batch-btn" onclick="multiShareSelectAllTokens(false)">
                  <i class="fa-regular fa-square"></i> Kosongkan
                </button>
              </div>
            </div>

            <!-- Search Token -->
            <div style="position:relative">
              <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--ink-4);font-size:.8rem"></i>
              <input type="text" id="multishare-token-search" class="input" placeholder="Cari token atau issuer…" oninput="filterMultiShareTokens(this.value)" style="padding-left:2.2rem;height:38px;font-size:.8125rem">
            </div>

            <!-- Scrollable Token Items -->
            <div id="multishare-tokens-list" class="multishare-scroll-list">
              <div style="padding:2.5rem;text-align:center;color:var(--ink-4)"><i class="fa-solid fa-spinner fa-spin" style="margin-right:.5rem"></i>Memuat daftar token…</div>
            </div>
          </div>

          <!-- RIGHT COLUMN: PILIH AKUN KOLABORATOR -->
          <div class="access-card" style="padding:1.25rem;gap:.875rem">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap">
              <div>
                <div style="font-size:.95rem;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:.5rem">
                  <span>2. Pilih Akun Penerima</span>
                  <span class="badge badge-teal" id="multishare-user-count-badge">0 Terpilih</span>
                </div>
                <div style="font-size:.75rem;color:var(--ink-4);margin-top:.15rem">Centang akun anggota yang akan menerima akses</div>
              </div>
              <div style="display:flex;align-items:center;gap:.35rem">
                <button type="button" class="matrix-batch-btn" onclick="multiShareSelectAllUsers(true)">
                  <i class="fa-regular fa-square-check" style="color:var(--emerald-h)"></i> Semua
                </button>
                <button type="button" class="matrix-batch-btn" onclick="multiShareSelectAllUsers(false)">
                  <i class="fa-regular fa-square"></i> Kosongkan
                </button>
              </div>
            </div>

            <!-- Search User -->
            <div style="position:relative">
              <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--ink-4);font-size:.8rem"></i>
              <input type="text" id="multishare-user-search" class="input" placeholder="Cari nama atau email akun…" oninput="filterMultiShareUsers(this.value)" style="padding-left:2.2rem;height:38px;font-size:.8125rem">
            </div>

            <!-- Scrollable User Items -->
            <div id="multishare-users-list" class="multishare-scroll-list">
              <div style="padding:2.5rem;text-align:center;color:var(--ink-4)"><i class="fa-solid fa-spinner fa-spin" style="margin-right:.5rem"></i>Memuat daftar akun…</div>
            </div>
          </div>

        </div>

        <!-- STICKY CONTROL & EXECUTION BAR -->
        <div class="multishare-control-card">
          <div class="multishare-action-config">
            
            <div class="multishare-action-radios">
              <label class="multishare-radio-label active grant" id="label-action-grant">
                <input type="radio" name="multishare_action" value="grant" checked onchange="onMultiShareActionChange('grant')">
                <i class="fa-solid fa-user-plus" style="color:var(--emerald-h)"></i>
                <span style="font-weight:700">Bagikan / Update Akses</span>
              </label>
              
              <label class="multishare-radio-label" id="label-action-revoke">
                <input type="radio" name="multishare_action" value="revoke" onchange="onMultiShareActionChange('revoke')">
                <i class="fa-solid fa-user-minus" style="color:var(--red)"></i>
                <span style="font-weight:700">Cabut Akses (Unshare)</span>
              </label>
            </div>

            <!-- Permission Level Toggle (when action == grant) -->
            <div id="multishare-permission-selector" style="display:flex;align-items:center;gap:.75rem">
              <span style="font-size:.8125rem;font-weight:600;color:var(--ink-2)">Tingkat Hak:</span>
              <div class="perm-pill-toggle">
                <button type="button" class="perm-pill-btn active" id="btn-perm-readonly" onclick="setMultiSharePerm(0)">
                  <i class="fa-solid fa-eye"></i> Hanya Lihat
                </button>
                <button type="button" class="perm-pill-btn" id="btn-perm-canedit" onclick="setMultiSharePerm(1)">
                  <i class="fa-solid fa-pen"></i> Boleh Edit
                </button>
              </div>
            </div>

          </div>

          <!-- Impact Preview & Execute Button -->
          <div class="multishare-execute-row">
            <div class="multishare-impact-preview" id="multishare-impact-text">
              <i class="fa-solid fa-circle-info" style="color:var(--teal-600)"></i>
              <span>Pilih minimal 1 token dan 1 akun di atas untuk melanjutkan.</span>
            </div>

            <button class="btn btn-primary" id="btn-exec-multishare" onclick="executeBulkMultiShare()" disabled style="padding:.65rem 1.75rem;font-weight:700">
              <i class="fa-solid fa-bolt"></i>
              <span>Terapkan Sharing Massal</span>
            </button>
          </div>

        </div>

      </div>

      <!-- ════ SUB-VIEW 3: MASTER AUDIT SHARING TABLE ════ -->
      <div id="access-subview-audit" style="display:none;flex-direction:column;gap:1.25rem">
        <div class="access-card">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
            <div>
              <div style="font-size:1rem;font-weight:700;color:var(--ink)">Master Relasi Sharing Aktif (<span id="audit-table-total">0</span>)</div>
              <div style="font-size:.75rem;color:var(--ink-3);margin-top:.15rem">Daftar lengkap seluruh token yang saat ini sedang dibagikan di sistem.</div>
            </div>
            <div style="display:flex;align-items:center;gap:.625rem;flex-wrap:wrap">
              <div class="audit-filter-pill-group">
                <button type="button" class="audit-filter-pill active" id="audit-filter-all" onclick="setAuditPermissionFilter('all')">Semua</button>
                <button type="button" class="audit-filter-pill" id="audit-filter-readonly" onclick="setAuditPermissionFilter('readonly')"><i class="fa-solid fa-eye"></i> Hanya Lihat</button>
                <button type="button" class="audit-filter-pill" id="audit-filter-canedit" onclick="setAuditPermissionFilter('canedit')"><i class="fa-solid fa-pen"></i> Boleh Edit</button>
              </div>
              <div style="width:220px">
                <input type="text" id="audit-search-input" class="input" placeholder="Cari token atau email…" oninput="filterAuditShares(this.value)" style="height:36px;font-size:.8125rem">
              </div>
              <button class="btn btn-secondary btn-sm" onclick="toggleMultiShareDrawer()" title="Buka form distribusi massal banyak token ke banyak akun">
                <i class="fa-solid fa-layer-group"></i> Multi-Share Massal
              </button>
              <button class="btn btn-ghost btn-sm" id="btn-bulk-revoke-shares" style="color:var(--red);background:var(--red-l);border:1px solid rgba(239,68,68,0.2);display:none" onclick="bulkRevokeSelectedShares()">
                <i class="fa-solid fa-trash-can"></i> Cabut Terpilih (<span id="audit-selected-count">0</span>)
              </button>
            </div>
          </div>

          <div id="audit-table-wrap" style="overflow-x:auto;margin-top:.5rem">
            <div style="padding:2.5rem;text-align:center;color:var(--ink-4)">Memuat riwayat sharing…</div>
          </div>
        </div>
      </div>

        <!-- Mobile bottom clearance spacer -->
        <div class="mobile-bottom-spacer" style="height: 72px; width: 100%; pointer-events: none;"></div>
      </div><!-- /.main-canvas-inner -->
  </main>
</div><!-- /.main-content-wrapper -->

  <!-- ── Mobile Fixed Bottom Navigation Bar ── -->
  <nav class="mobile-bottom-nav" id="mobile-bottom-nav">
    <button class="mobile-nav-item <?= $currentView === 'tokens' ? 'active' : '' ?>" onclick="switchView('tokens')" id="mob-nav-tokens">
      <i class="fa-solid fa-key"></i>
      <span>Tokens</span>
      <span class="mobile-nav-badge" id="mob-badge-tokens"><?= count($profiles) ?></span>
    </button>

    <?php if (!empty($user['is_admin'])): ?>
    <button class="mobile-nav-item <?= $currentView === 'access' ? 'active' : '' ?>" onclick="switchView('access')" id="mob-nav-access">
      <i class="fa-solid fa-users-gear"></i>
      <span>Akses</span>
    </button>

    <button class="mobile-nav-item <?= $currentView === 'whitelist' ? 'active' : '' ?>" onclick="switchView('whitelist')" id="mob-nav-whitelist">
      <i class="fa-solid fa-shield-halved"></i>
      <span>Whitelist</span>
    </button>
    <?php endif; ?>

    <a href="/tools/import-google-auth" class="mobile-nav-item" id="mob-nav-import">
      <i class="fa-solid fa-file-import"></i>
      <span>Import</span>
    </a>

    <button class="mobile-nav-item" onclick="toggleSidebarMobile()" id="mob-nav-menu">
      <i class="fa-solid fa-bars"></i>
      <span>Menu</span>
    </button>
  </nav>

</div><!-- /.app-shell -->

<!-- ── Add/Edit Token Modal ── -->
<div id="profile-modal" class="modal-backdrop hidden">
  <div class="modal" style="max-width:640px">
    <div class="modal-header">
      <div>
        <h2 class="text-lg font-bold" id="modal-title">Add Token</h2>
        <p class="text-xs text-ink-3" style="margin-top:.25rem">Masukkan kunci rahasia Base32 untuk token OTP baru</p>
      </div>
      <button class="btn btn-ghost btn-icon" onclick="closeModal('profile-modal')">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="modal-body">
      <input type="hidden" id="edit-id" value="">

      <!-- Quick QR Upload / URI Auto-Parser Shortcut -->
      <div style="background:#07080d;border:1px dashed rgba(255,255,255,0.12);border-radius:12px;padding:.65rem .875rem;margin-bottom:1.15rem;display:flex;align-items:center;justify-content:space-between;gap:.65rem;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:.55rem">
          <div style="width:28px;height:28px;border-radius:8px;background:rgba(16,185,129,0.15);color:#10b981;display:flex;align-items:center;justify-content:center;font-size:.9rem">
            <i class="fa-solid fa-qrcode"></i>
          </div>
          <div>
            <div style="font-size:.8125rem;font-weight:700;color:#ffffff">Scan Gambar QR / Paste otpauth://</div>
            <div style="font-size:.6875rem;color:#7a9c95">Ekstrak kunci rahasia &amp; parameter otomatis</div>
          </div>
        </div>
        <div style="display:flex;gap:.4rem">
          <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('qr-upload-input').click()" style="font-size:.75rem;height:30px;padding:0 .75rem;border-radius:var(--r-pill)">
            <i class="fa-solid fa-file-arrow-up"></i> Upload QR
          </button>
          <input type="file" id="qr-upload-input" accept="image/*" style="display:none" onchange="handleQrImageUpload(this)">
        </div>
      </div>

      <div class="form-2col">
        <!-- LEFT column -->
        <div class="col">
          <div class="label" style="font-weight:700;color:var(--emerald);letter-spacing:.03em;text-transform:uppercase;font-size:.75rem">Token Details</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.625rem">
            <div class="field">
              <label class="label">Name *</label>
              <input class="input" id="f-name" placeholder="e.g. Production Admin…">
            </div>
            <div class="field">
              <label class="label">Issuer / Service</label>
              <input class="input" id="f-issuer" placeholder="e.g. Cloud Portal">
            </div>
          </div>

          <div class="field">
            <label class="label">Secret Key *</label>
            <div style="display:flex;gap:.5rem;align-items:center">
              <input class="input" id="f-secret" placeholder="Base32 secret…" style="flex:1;font-family:'JetBrains Mono',monospace;letter-spacing:.05em">
              <button class="btn btn-secondary btn-sm" onclick="generateSecret()" title="Generate random secret">Gen</button>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem">
            <div class="field">
              <label class="label">Algo</label>
              <select class="input" id="f-algorithm">
                <option value="SHA1">SHA1</option>
                <option value="SHA256">SHA256</option>
                <option value="SHA512">SHA512</option>
              </select>
            </div>
            <div class="field">
              <label class="label">Digits</label>
              <select class="input" id="f-digits">
                <option value="6">6</option>
                <option value="8">8</option>
                <option value="10">10</option>
              </select>
            </div>
            <div class="field">
              <label class="label">Period (s)</label>
              <input class="input" id="f-period" type="number" value="30" min="15" max="300">
            </div>
          </div>

          <div class="field" style="margin-top:.25rem">
            <label style="display:flex;align-items:center;gap:.5rem;font-size:.8125rem;cursor:pointer;color:#94a3b8">
              <input type="checkbox" id="f-hide" style="width:15px;height:15px">
              <span>Mask code until clicked</span>
            </label>
          </div>
        </div>

        <!-- RIGHT column -->
        <div class="col">
          <div class="label" style="font-weight:700;color:var(--emerald);letter-spacing:.03em;text-transform:uppercase;font-size:.75rem">Appearance</div>
          <div class="field">
            <label class="label">Colour theme</label>
            <div class="color-grid" id="color-grid"></div>
            <input type="hidden" id="f-color" value="#0b332d">
          </div>

          <div class="field">
            <label class="label">Icon</label>
            <input class="input" id="icon-search" placeholder="Search: shield, user, key..." oninput="filterIcons(this.value)" autocomplete="off">
            <div id="icon-grid" style="display:flex;flex-wrap:wrap;gap:5px;margin-top:6px;max-height:180px;overflow-y:auto;padding:2px 0"></div>
            <input type="hidden" id="f-icon" value="fa-shield-halved">
            <input type="hidden" id="f-icon-prefix" value="fa-solid">
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary btn-sm" onclick="closeModal('profile-modal')">Cancel</button>
      <button class="btn btn-dark btn-sm" id="save-btn" onclick="saveProfile()">Save Token</button>
    </div>
  </div>
</div>

<!-- ── Share Modal ── -->
<div id="share-modal" class="modal-backdrop hidden">
  <div class="modal" style="max-width:500px">
    <div class="modal-header">
      <div>
        <h2 class="text-lg font-bold">Share Token</h2>
        <p class="text-xs text-ink-3" style="margin-top:.25rem">Kolaborator melihat kode real-time tanpa melihat secret key.</p>
      </div>
      <button class="btn btn-ghost btn-icon" onclick="closeModal('share-modal')">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="share-profile-id">
      <div style="display:flex;gap:.5rem;margin-bottom:.75rem">
        <input class="input" id="share-email" type="email" placeholder="colleague.google@gmail.com" style="flex:1">
        <button class="btn btn-dark btn-sm" onclick="addShare()">+ Invite</button>
      </div>
      <label style="display:flex;align-items:center;gap:.5rem;font-size:.8125rem;cursor:pointer;margin-bottom:1rem;color:var(--ink-2)">
        <input type="checkbox" id="share-can-edit" style="width:15px;height:15px">
        Allow editing token name
      </label>
      <div id="share-list-wrap">
        <div class="text-xs text-ink-4">Loading shares…</div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary btn-sm" onclick="closeModal('share-modal')">Done</button>
    </div>
  </div>
</div>

<script>
const CSRF = <?= json_encode($csrfToken) ?>;
let activeView = <?= json_encode($currentView) ?>;
let whitelistCache = [];

const IS_ADMIN = <?= json_encode(!empty($user['is_admin'])) ?>;

// ── View Switcher (Tokens vs Whitelist vs Access Full Page) ──
function switchView(viewName) {
  if ((viewName === 'whitelist' || viewName === 'access') && !IS_ADMIN) {
    toast('Akses terbatas: Hanya Administrator yang berhak mengakses menu ini', 'error');
    return;
  }
  // Auto-close mobile drawer if open
  const sidebar = document.getElementById('app-sidebar');
  const backdrop = document.getElementById('sidebar-backdrop');
  if (sidebar && sidebar.classList.contains('mobile-open')) {
    sidebar.classList.remove('mobile-open');
    if (backdrop) backdrop.classList.remove('mobile-open');
    document.body.style.overflow = '';
  }

  activeView = viewName;
  document.getElementById('nav-tokens')?.classList.toggle('active', viewName === 'tokens');
  document.getElementById('nav-access')?.classList.toggle('active', viewName === 'access');
  document.getElementById('nav-whitelist')?.classList.toggle('active', viewName === 'whitelist');
  document.getElementById('mob-nav-tokens')?.classList.toggle('active', viewName === 'tokens');
  document.getElementById('mob-nav-access')?.classList.toggle('active', viewName === 'access');
  document.getElementById('mob-nav-whitelist')?.classList.toggle('active', viewName === 'whitelist');

  const tokensWrap = document.getElementById('view-tokens-wrap');
  const accessWrap = document.getElementById('view-access-wrap');
  const whitelistWrap = document.getElementById('view-whitelist-wrap');
  const topbarTitle = document.getElementById('topbar-main-title');
  const addBtn = document.getElementById('topbar-add-token-btn');
  const searchInput = document.getElementById('global-search');
  const mobileSearch = document.getElementById('mobile-search');

  if (viewName === 'tokens') {
    if (tokensWrap) tokensWrap.style.display = '';
    if (accessWrap) accessWrap.style.display = 'none';
    if (whitelistWrap) whitelistWrap.style.display = 'none';
    if (topbarTitle) topbarTitle.textContent = 'Tokens';
    if (addBtn) {
      addBtn.style.display = '';
      addBtn.classList.remove('hidden');
    }
    if (searchInput) searchInput.placeholder = 'Cari token 2FA… (Ctrl + K atau /)';
    if (mobileSearch) mobileSearch.placeholder = 'Cari token 2FA…';
    history.pushState(null, '', '/tokens');
  } else if (viewName === 'access') {
    if (tokensWrap) tokensWrap.style.display = 'none';
    if (accessWrap) accessWrap.style.display = '';
    if (whitelistWrap) whitelistWrap.style.display = 'none';
    if (topbarTitle) topbarTitle.textContent = 'Kelola Akses';
    if (addBtn) {
      addBtn.style.display = 'none';
      addBtn.classList.add('hidden');
    }
    if (searchInput) searchInput.placeholder = 'Cari pengguna atau token di Kelola Akses…';
    if (mobileSearch) mobileSearch.placeholder = 'Cari pengguna atau token…';
    history.pushState(null, '', '/access');
    initAccessManager();
  } else {
    if (tokensWrap) tokensWrap.style.display = 'none';
    if (accessWrap) accessWrap.style.display = 'none';
    if (whitelistWrap) whitelistWrap.style.display = '';
    if (topbarTitle) topbarTitle.textContent = 'Whitelist Akun';
    if (addBtn) {
      addBtn.style.display = 'none';
      addBtn.classList.add('hidden');
    }
    if (searchInput) searchInput.placeholder = 'Cari akun atau email whitelist… (Ctrl + K)';
    if (mobileSearch) mobileSearch.placeholder = 'Cari akun atau email whitelist…';
    history.pushState(null, '', '/whitelist');
    loadWhitelistData();
  }
}

function handleGlobalSearch(q) {
  const gSearch = document.getElementById('global-search');
  const mSearch = document.getElementById('mobile-search');
  if (gSearch && gSearch.value !== q) gSearch.value = q;
  if (mSearch && mSearch.value !== q) mSearch.value = q;

  const clr = document.getElementById('mobile-search-clear');
  if (clr) clr.style.display = (q && q.trim().length > 0) ? 'flex' : 'none';

  if (activeView === 'whitelist') {
    filterWhitelistTable(q);
  } else if (activeView === 'access') {
    if (activeAccessSubTab === 'matrix') {
      const matSearch = document.getElementById('matrix-search');
      if (matSearch) matSearch.value = q;
      filterMatrixTokens(q);
    } else if (activeAccessSubTab === 'multishare') {
      const msSearch = document.getElementById('multishare-token-search');
      if (msSearch) msSearch.value = q;
      filterMultiShareTokens(q);
      const msUserSearch = document.getElementById('multishare-user-search');
      if (msUserSearch) msUserSearch.value = q;
      filterMultiShareUsers(q);
    } else {
      const auditSearch = document.getElementById('audit-search-input');
      if (auditSearch) auditSearch.value = q;
      filterAuditShares(q);
    }
  } else {
    filterBySearch(q);
  }
}

// ── Color Presets for Token ──
const COLOR_PRESETS = [
  '#0b332d', '#10b981', '#0284c7', '#4f46e5',
  '#7c3aed', '#db2777', '#dc2626', '#d97706',
  '#059669', '#0891b2', '#2563eb', '#9333ea',
  '#c026d3', '#e11d48', '#ea580c', '#65a30d',
];

function initColorGrid(selected) {
  const g = document.getElementById('color-grid');
  g.innerHTML = '';
  COLOR_PRESETS.forEach(c => {
    const s = document.createElement('div');
    s.className = 'color-swatch' + (c.toLowerCase() === selected.toLowerCase() ? ' selected' : '');
    s.style.background = c;
    s.onclick = () => {
      document.querySelectorAll('.color-swatch').forEach(x => x.classList.remove('selected'));
      s.classList.add('selected');
      document.getElementById('f-color').value = c;
    };
    g.appendChild(s);
  });
}

function generateSecret() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  let s = '';
  for (let i = 0; i < 32; i++) s += chars[Math.floor(Math.random() * chars.length)];
  document.getElementById('f-secret').value = s;
}

function parseOtpauthUri(uri) {
  try {
    uri = (uri || '').trim();
    if (!uri.toLowerCase().startsWith('otpauth://')) return false;
    const url = new URL(uri);
    const params = url.searchParams;
    const secret = params.get('secret');
    if (!secret) return false;

    let label = decodeURIComponent(url.pathname.replace(/^\/\/?(totp|hotp)\//i, ''));
    let issuer = params.get('issuer') || '';
    let name = label;
    if (!issuer && label.includes(':')) {
      const parts = label.split(':');
      issuer = parts[0].trim();
      name = parts[1].trim();
    } else if (issuer && name.toLowerCase().startsWith(issuer.toLowerCase() + ':')) {
      name = name.substring(issuer.length + 1).trim();
    }

    document.getElementById('f-secret').value = secret.toUpperCase().replace(/\s+/g, '');
    if (name) document.getElementById('f-name').value = name;
    if (issuer) document.getElementById('f-issuer').value = issuer;
    if (params.get('digits')) document.getElementById('f-digits').value = params.get('digits');
    if (params.get('period')) document.getElementById('f-period').value = params.get('period');
    if (params.get('algorithm')) {
      const a = params.get('algorithm').toUpperCase();
      if (['SHA1', 'SHA256', 'SHA512'].includes(a)) document.getElementById('f-algorithm').value = a;
    }

    toast('✓ Parameter token berhasil diekstrak otomatis dari URI!', 'success');
    return true;
  } catch (e) {
    return false;
  }
}

function handleQrImageUpload(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();
  reader.onload = function(e) {
    const img = new Image();
    img.onload = function() {
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      canvas.width = img.width;
      canvas.height = img.height;
      ctx.drawImage(img, 0, 0, img.width, img.height);
      const imageData = ctx.getImageData(0, 0, img.width, img.height);
      if (typeof jsQR !== 'undefined') {
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        if (code && code.data) {
          if (!parseOtpauthUri(code.data)) {
            document.getElementById('f-secret').value = code.data.trim();
            toast('✓ Data QR berhasil terbaca: ' + code.data.substring(0, 20) + '…', 'info');
          }
        } else {
          toast('Tidak dapat mendeteksi QR code dalam gambar. Silakan gunakan gambar QR yang lebih jelas.', 'error');
        }
      } else {
        toast('Library QR scanner belum siap.', 'error');
      }
      input.value = '';
    };
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
}

// ── Real-Time OTP Fetch & Heroic Gauge Animation ──
const tokenTimers = {};

function initAllCards() {
  document.querySelectorAll('.token-card').forEach(c => {
    const id = parseInt(c.dataset.id);
    const period = parseInt(c.dataset.period) || 30;
    fetchCode(id);
    startGauge(id, period);
  });
}

// ── Dual View Mode (Visual Cards vs Dense DevOps Table) ──
function setTokensViewMode(mode) {
  if (window.innerWidth <= 768) {
    mode = 'grid'; // Always force card view on mobile
  }
  const grid = document.getElementById('profiles-grid');
  const table = document.getElementById('profiles-table-wrap');
  const btnGrid = document.getElementById('btn-view-grid');
  const btnTable = document.getElementById('btn-view-table');

  if (mode === 'table') {
    if (grid) grid.style.display = 'none';
    if (table) table.style.display = 'block';
    if (btnGrid) btnGrid.classList.remove('active');
    if (btnTable) btnTable.classList.add('active');
    if (typeof syncTableRowsToGridOrder === 'function') syncTableRowsToGridOrder();
  } else {
    mode = 'grid';
    if (grid) grid.style.display = 'grid';
    if (table) table.style.display = 'none';
    if (btnGrid) btnGrid.classList.add('active');
    if (btnTable) btnTable.classList.remove('active');
    if (typeof syncGridCardsToTableOrder === 'function') syncGridCardsToTableOrder();
  }
  if (window.innerWidth > 768) {
    localStorage.setItem('totp_view_mode', mode);
  }
}

async function fetchCode(id) {
  try {
    const r = await fetch(`/api/otp?id=${id}`);
    const d = await r.json();
    if (d.error || !d.code) return;
    const digits = parseInt(d.code.length) || 6;
    const formatted = formatHeroCode(d.code, digits);

    // Update Card
    const el = document.getElementById(`code-${id}`);
    if (el) {
      el.dataset.rawCode = d.code;
      if (el.dataset.hidden !== '1' || el.dataset.revealed === '1') {
        el.textContent = formatted;
      }
    }

    // Update Dense Table Row
    const tableEl = document.getElementById(`table-code-${id}`);
    if (tableEl) {
      tableEl.dataset.rawCode = d.code;
      if (tableEl.dataset.hidden !== '1' || tableEl.dataset.revealed === '1') {
        tableEl.textContent = formatted;
      }
    }
  } catch (e) {
    console.error('Fetch code error', e);
  }
}

function formatHeroCode(c, digits) {
  if (!c) return '--- ---';
  if (digits === 6) return c.slice(0, 3) + ' ' + c.slice(3);
  if (digits === 8) return c.slice(0, 4) + ' ' + c.slice(4);
  return c;
}

function startGauge(id, period) {
  if (tokenTimers[id]) clearInterval(tokenTimers[id]);

  let lastStep = Math.floor(Date.now() / 1000 / period);

  function tick() {
    const now = Math.floor(Date.now() / 1000);
    const rem = period - (now % period);
    const currentStep = Math.floor(now / period);
    const timerEl = document.getElementById(`timer-${id}`);
    const gaugeEl = document.getElementById(`gauge-${id}`);
    const tableTimerEl = document.getElementById(`table-timer-${id}`);
    const tableGaugeEl = document.getElementById(`table-gauge-${id}`);

    if (timerEl) timerEl.textContent = `${rem}s`;
    if (tableTimerEl) tableTimerEl.textContent = `${rem}s`;

    const circ = 81.68; // 2 * PI * 13
    const offset = circ * (1 - (rem / period));

    let strokeColor = '#10b981';
    let textColor = '#cbd5e1';
    if (rem <= 5) {
      strokeColor = '#f43f5e';
      textColor = '#f43f5e';
    } else if (rem <= 10) {
      strokeColor = '#f59e0b';
      textColor = '#f59e0b';
    }

    if (timerEl) {
      timerEl.textContent = `${rem}s`;
      timerEl.style.color = textColor;
    }
    if (tableTimerEl) {
      tableTimerEl.textContent = `${rem}s`;
      tableTimerEl.style.color = textColor;
    }

    if (gaugeEl) {
      gaugeEl.style.strokeDashoffset = offset;
      gaugeEl.style.stroke = strokeColor;
    }
    if (tableGaugeEl) {
      tableGaugeEl.style.strokeDashoffset = offset;
      tableGaugeEl.style.stroke = strokeColor;
    }

    if (currentStep !== lastStep) {
      lastStep = currentStep;
      fetchCode(id);
    }
  }

  tick();
  tokenTimers[id] = setInterval(tick, 1000);
}

// ── 1-Click Super Copy with Hero Visual Feedback ──
let lastCopyTime = 0;
let lastCopyId = null;

function copyCode(id) {
  const now = Date.now();
  if (now - lastCopyTime < 600 && lastCopyId === id) {
    return; // Debounce rapid click/touch events
  }
  lastCopyTime = now;
  lastCopyId = id;

  const codeEl = document.getElementById(`code-${id}`) || document.getElementById(`table-code-${id}`);
  const card = document.getElementById(`card-${id}`);
  const row = document.getElementById(`row-${id}`);
  const copyBtn = document.getElementById(`copy-btn-${id}`);
  const copyLbl = document.getElementById(`copy-lbl-${id}`);
  const tableBtn = document.getElementById(`table-code-btn-${id}`);
  const hintEl = document.getElementById(`hint-${id}`);
  const raw = codeEl ? codeEl.dataset.rawCode : null;

  if (!raw) {
    toast('Memuat kode… coba lagi', 'info');
    return;
  }

  navigator.clipboard.writeText(raw).then(() => {
    toast(`Kode ${formatHeroCode(raw, raw.length)} disalin ke clipboard!`, 'success');

    if (navigator.vibrate) {
      try { navigator.vibrate(35); } catch (_) {}
    }

    if (card) {
      card.classList.add('copied-pulse');
      setTimeout(() => card.classList.remove('copied-pulse'), 1400);
    }
    if (row) {
      row.style.background = 'rgba(16, 185, 129, 0.12)';
      setTimeout(() => { row.style.background = ''; }, 1400);
    }

    if (hintEl) {
      const orig = hintEl.innerHTML;
      hintEl.innerHTML = '<span style="color:var(--emerald-h);font-weight:700">✓ Berhasil disalin!</span>';
      setTimeout(() => { hintEl.innerHTML = orig; }, 1400);
    }

    if (copyBtn) {
      copyBtn.classList.add('is-copied');
      if (copyLbl) copyLbl.textContent = 'Copied!';
      setTimeout(() => {
        copyBtn.classList.remove('is-copied');
        if (copyLbl) copyLbl.textContent = 'Copy';
      }, 1400);
    }

    if (tableBtn) {
      tableBtn.style.borderColor = 'var(--emerald)';
      tableBtn.style.color = '#34d399';
      setTimeout(() => {
        tableBtn.style.borderColor = '';
        tableBtn.style.color = '';
      }, 1400);
    }
  }).catch(() => {
    toast('Gagal menyalin kode', 'error');
  });
}

function handleCardClick(e, id) {
  if (isDragOngoing || e.target.closest('button') || e.target.closest('a') || e.target.closest('.token-action-icon-btn') || e.target.closest('.token-drag-handle')) {
    return;
  }
  const el = document.getElementById(`code-${id}`);
  if (el && el.dataset.hidden === '1' && el.dataset.revealed !== '1') {
    revealCode(id);
    return;
  }
  copyCode(id);
}

function revealCode(id) {
  const el = document.getElementById(`code-${id}`);
  const tableEl = document.getElementById(`table-code-${id}`);
  const raw = (el && el.dataset.rawCode) || (tableEl && tableEl.dataset.rawCode);
  if (!raw) return;
  const formatted = formatHeroCode(raw, raw.length);
  if (el) {
    el.dataset.revealed = '1';
    el.textContent = formatted;
  }
  if (tableEl) {
    tableEl.dataset.revealed = '1';
    tableEl.textContent = formatted;
  }
  setTimeout(() => {
    if (el) {
      delete el.dataset.revealed;
      if (el.dataset.hidden === '1') {
        el.textContent = '••••••';
      }
    }
    if (tableEl) {
      delete tableEl.dataset.revealed;
      if (tableEl.dataset.hidden === '1') {
        tableEl.textContent = '••••••';
      }
    }
  }, 10000);
}

// ── Filter Tabs ("Tabnation") ──
let currentTab = 'all';

function filterTab(tabKey) {
  currentTab = tabKey;
  document.querySelectorAll('.tokens-tab-btn').forEach(b => b.classList.remove('active'));
  const btn = document.getElementById(`tab-${tabKey}`);
  if (btn) btn.classList.add('active');
  applyFilters();
}

function filterBySearch(q) {
  applyFilters();
}

function clearSearch() {
  const g = document.getElementById('global-search');
  const m = document.getElementById('mobile-search');
  if (g) g.value = '';
  if (m) m.value = '';
  const clr = document.getElementById('mobile-search-clear');
  if (clr) clr.style.display = 'none';
  applyFilters();
}

function applyFilters() {
  const g = document.getElementById('global-search');
  const m = document.getElementById('mobile-search');
  const q = ((g ? g.value : '') || (m ? m.value : '') || '').toLowerCase().trim();
  let matchCount = 0;

  // Filter both cards and table rows simultaneously
  document.querySelectorAll('.token-card, .token-row').forEach(c => {
    const owner = c.dataset.owner === '1';
    const matchTab = (currentTab === 'all') || (currentTab === 'mine' && owner) || (currentTab === 'shared' && !owner);
    const matchQuery = !q || c.dataset.name.includes(q) || c.dataset.issuer.includes(q);
    const isVisible = matchTab && matchQuery;
    c.style.display = isVisible ? '' : 'none';
    if (isVisible && c.classList.contains('token-card')) matchCount++;
  });

  const grid = document.getElementById('profiles-grid');
  let emptyEl = document.getElementById('empty-search-box');
  if (matchCount === 0 && q) {
    if (!emptyEl && grid) {
      emptyEl = document.createElement('div');
      emptyEl.id = 'empty-search-box';
      emptyEl.className = 'empty-search-state';
      grid.appendChild(emptyEl);
    }
    if (emptyEl) {
      emptyEl.innerHTML = `
        <i class="fa-solid fa-magnifying-glass"></i>
        <h3 style="font-size:1rem;font-weight:700;color:var(--ink);margin-bottom:.35rem">Tidak ada token yang cocok dengan "${escapeHtml(q)}"</h3>
        <p style="font-size:.8125rem;color:var(--ink-3);margin-bottom:.875rem">Coba periksa kata kunci filter Anda.</p>
        <button class="btn btn-secondary btn-sm" onclick="clearSearch()">Reset Pencarian (Esc)</button>
      `;
      emptyEl.style.display = '';
    }
  } else if (emptyEl) {
    emptyEl.style.display = 'none';
  }
}

// ── Modals & Actions ──
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openAddModal() {
  document.getElementById('edit-id').value = '';
  document.getElementById('modal-title').textContent = 'Add Token';
  document.getElementById('save-btn').textContent = 'Save Token';
  document.getElementById('f-name').value = '';
  document.getElementById('f-issuer').value = '';
  document.getElementById('f-secret').value = '';
  document.getElementById('f-algorithm').value = 'SHA1';
  document.getElementById('f-digits').value = '6';
  document.getElementById('f-period').value = '30';
  document.getElementById('f-color').value = '#0b332d';
  document.getElementById('f-hide').checked = false;
  initColorGrid('#0b332d');
  document.getElementById('f-icon').value = 'fa-shield-halved';
  document.getElementById('f-icon-prefix').value = 'fa-solid';
  document.getElementById('icon-search').value = '';
  renderIcons('');
  document.getElementById('profile-modal').classList.remove('hidden');
  document.getElementById('f-name').focus();

  const secInput = document.getElementById('f-secret');
  if (secInput && !secInput.dataset.listenerAttached) {
    secInput.dataset.listenerAttached = '1';
    secInput.addEventListener('input', function() {
      if (this.value.trim().toLowerCase().startsWith('otpauth://')) {
        parseOtpauthUri(this.value);
      }
    });
  }
}

async function openEditModal(id) {
  const r = await fetch(`/api/profiles/${id}`);
  const p = await r.json();
  if (p.error) { toast(p.error, 'error'); return; }
  document.getElementById('edit-id').value = id;
  document.getElementById('modal-title').textContent = 'Edit Token';
  document.getElementById('save-btn').textContent = 'Update Token';
  document.getElementById('f-name').value = p.name;
  document.getElementById('f-issuer').value = p.issuer || '';
  document.getElementById('f-secret').value = '';
  document.getElementById('f-algorithm').value = p.algorithm;
  document.getElementById('f-digits').value = p.digits;
  document.getElementById('f-period').value = p.period;
  document.getElementById('f-color').value = p.color;
  document.getElementById('f-hide').checked = !!parseInt(p.hide_code || 0);
  initColorGrid(p.color);
  document.getElementById('f-icon').value = p.icon || 'fa-shield-halved';
  document.getElementById('f-icon-prefix').value = p.icon_prefix || 'fa-solid';
  document.getElementById('icon-search').value = '';
  renderIcons('');
  document.getElementById('profile-modal').classList.remove('hidden');
}

async function saveProfile() {
  const id = document.getElementById('edit-id').value;
  const body = {
    name:      document.getElementById('f-name').value.trim(),
    issuer:    document.getElementById('f-issuer').value.trim(),
    secret:    document.getElementById('f-secret').value.trim(),
    algorithm: document.getElementById('f-algorithm').value,
    digits:    document.getElementById('f-digits').value,
    period:    document.getElementById('f-period').value,
    color:     document.getElementById('f-color').value,
    icon:      document.getElementById('f-icon').value,
    hide_code: document.getElementById('f-hide').checked ? 1 : 0,
  };
  if (!body.name) { toast('Nama token wajib diisi', 'error'); return; }
  if (!id && !body.secret) { toast('Secret key wajib diisi', 'error'); return; }
  const url = id ? `/api/profiles/${id}` : '/api/profiles';
  const method = id ? 'PUT' : 'POST';
  const r = await apiFetch(url, method, body);
  if (r.error) { toast(r.error, 'error'); return; }
  toast(id ? 'Token berhasil diperbarui' : 'Token berhasil ditambahkan!', 'success');
  closeModal('profile-modal');
  setTimeout(() => location.reload(), 600);
}

async function deleteProfile(id, name) {
  if (!confirm(`Hapus token "${name}"? Kunci rahasia akan dihapus permanen.`)) return;
  const r = await apiFetch(`/api/profiles/${id}`, 'DELETE', {});
  if (r.error) { toast(r.error, 'error'); return; }
  toast(`Token "${name}" dihapus`, 'success');
  const card = document.getElementById(`card-${id}`);
  if (card) card.remove();
  updateCounts();
}

function updateCounts() {
  const cards = [...document.querySelectorAll('.token-card')];
  const allCount = cards.length;
  const mineCount = cards.filter(c => c.dataset.owner === '1').length;
  const sharedCount = cards.filter(c => c.dataset.owner !== '1').length;

  document.getElementById('count-all').textContent = allCount;
  document.getElementById('tab-count-all').textContent = allCount;
  document.getElementById('tab-count-mine').textContent = mineCount;
  document.getElementById('tab-count-shared').textContent = sharedCount;
}

// ── Share Token Modal ──
function openShareModal(id, name) {
  document.getElementById('share-profile-id').value = id;
  document.getElementById('share-email').value = '';
  document.getElementById('share-can-edit').checked = false;
  loadShares(id);
  document.getElementById('share-modal').classList.remove('hidden');
}

async function loadShares(profileId) {
  const wrap = document.getElementById('share-list-wrap');
  wrap.innerHTML = '<div class="text-xs text-ink-4">Memuat kolaborator…</div>';
  const r = await fetch(`/api/profiles/${profileId}/shares`);
  const list = await r.json();
  if (!list.length) {
    wrap.innerHTML = '<div class="text-xs text-ink-4" style="text-align:center;padding:.875rem">Belum ada kolaborator. Undang menggunakan form di atas.</div>';
    return;
  }
  wrap.innerHTML = `
    <div style="display:flex;flex-direction:column;gap:.5rem">
      ${list.map(s => `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;border:1px solid rgba(255,255,255,0.08);border-radius:10px;background:#0d0e15">
          <div>
            <div style="font-weight:600;font-size:.8125rem;color:#f8fafc">${escapeHtml(s.shared_with_email)}</div>
            <div style="font-size:.6875rem;color:#64748b">${s.can_edit ? 'Dapat mengedit' : 'Hanya lihat (Read-only)'}</div>
          </div>
          <button class="btn btn-ghost btn-sm" onclick="deleteShare(${s.id})" style="color:var(--red);padding:.25rem .5rem">Hapus</button>
        </div>
      `).join('')}
    </div>
  `;
}

async function addShare() {
  const profileId = document.getElementById('share-profile-id').value;
  const email = document.getElementById('share-email').value.trim();
  const canEdit = document.getElementById('share-can-edit').checked;
  if (!email) { toast('Masukkan email kolaborator', 'error'); return; }
  const r = await apiFetch(`/api/profiles/${profileId}/shares`, 'POST', { shared_with_email: email, can_edit: canEdit });
  if (r.error) { toast(r.error, 'error'); return; }
  toast(`Undangan dibagikan ke ${email}`, 'success');
  document.getElementById('share-email').value = '';
  loadShares(profileId);
}

async function deleteShare(shareId) {
  const profileId = document.getElementById('share-profile-id').value;
  const r = await apiFetch(`/api/shares/${shareId}`, 'DELETE', {});
  if (r.error) { toast(r.error, 'error'); return; }
  toast('Akses kolaborasi dicabut', 'success');
  loadShares(profileId);
}

// ── Whitelist Page Management (SaaS Grade) ──
async function loadWhitelistData() {
  const wrap = document.getElementById('page-whitelist-table-wrap');
  const badge = document.getElementById('wl-badge-count');
  const totalEl = document.getElementById('page-wl-total');
  try {
    const res = await fetch('/api/whitelist');
    const data = await res.json();
    if (data.error) return;
    whitelistCache = data;
    if (badge) badge.textContent = data.length;
    if (totalEl) totalEl.textContent = data.length;
    renderWhitelistTable(data);
  } catch (err) {
    if (wrap) wrap.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">Gagal memuat: ${err.message}</div>`;
  }
}

function renderWhitelistTable(data) {
  const wrap = document.getElementById('page-whitelist-table-wrap');
  if (!wrap) return;

  if (data.length === 0) {
    wrap.innerHTML = '<div style="padding:3rem;text-align:center;color:var(--ink-4);font-size:.875rem"><i class="fa-solid fa-users" style="font-size:1.75rem;margin-bottom:.5rem;display:block"></i>Belum ada akun di whitelist.</div>';
    return;
  }

  wrap.innerHTML = `
    <table style="width:100%;border-collapse:collapse;font-size:.8125rem;text-align:left">
      <thead>
        <tr style="background:#07080d;border-bottom:1px solid rgba(255,255,255,0.08);color:#64748b">
          <th style="padding:.75rem .875rem;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Pengguna</th>
          <th style="padding:.75rem .875rem;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Peran</th>
          <th style="padding:.75rem .875rem;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Hak Akses</th>
          <th style="padding:.75rem .875rem;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Didaftarkan Oleh</th>
          <th style="padding:.75rem .875rem;text-align:right;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Aksi</th>
        </tr>
      </thead>
      <tbody>
        ${data.map(u => {
          const initial = (u.name || u.email).substring(0, 1).toUpperCase();
          const isCurrentUser = u.email.toLowerCase() === <?= json_encode(strtolower($user['email'])) ?>;
          return `
            <tr style="border-bottom:1px solid rgba(255,255,255,0.04)">
              <td style="padding:.75rem .875rem">
                <div class="table-user-cell">
                  <div class="table-user-avatar">${initial}</div>
                  <div class="table-user-info">
                    <span class="table-user-name">${escapeHtml(u.name || 'Pengguna')}</span>
                    <span class="table-user-email">${escapeHtml(u.email)}</span>
                  </div>
                </div>
              </td>
              <td style="padding:.75rem .875rem">
                <span style="display:inline-block;font-size:.6875rem;background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.25);padding:.15rem .5rem;border-radius:var(--r-pill);font-weight:600">
                  ${escapeHtml(u.role_label || 'Anggota')}
                </span>
              </td>
              <td style="padding:.75rem .875rem">
                ${u.is_admin ? '<span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.6875rem;background:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.25);padding:.15rem .45rem;border-radius:6px;font-weight:700"><i class="fa-solid fa-crown" style="font-size:.65rem"></i> Administrator</span>' : '<span style="font-size:.75rem;color:#7a9c95">Pengguna Standar</span>'}
              </td>
              <td style="padding:.75rem .875rem;color:var(--ink-3);font-size:.75rem">
                ${escapeHtml(u.created_by_email || 'System')}
              </td>
              <td style="padding:.75rem .875rem;text-align:right">
                ${isCurrentUser ? '<span style="font-size:.75rem;color:var(--emerald-h);font-weight:600;background:var(--emerald-l);padding:.15rem .45rem;border-radius:var(--r-pill)">Akun Anda</span>' : `
                  <button class="btn btn-ghost btn-sm" style="color:var(--red);padding:.25rem .5rem;font-size:.75rem" onclick="deleteWhitelistUser(${u.id}, '${escapeHtml(u.email)}')">
                    <i class="fa-solid fa-trash-can"></i> Hapus
                  </button>
                `}
              </td>
            </tr>
          `;
        }).join('')}
      </tbody>
    </table>
  `;
}

function filterWhitelistTable(query) {
  const q = query.toLowerCase().trim();
  const filtered = whitelistCache.filter(u =>
    u.email.toLowerCase().includes(q) || (u.name && u.name.toLowerCase().includes(q))
  );
  renderWhitelistTable(filtered);
}

async function addWhitelistUserFromPage() {
  const emailInput = document.getElementById('page-wl-email');
  const nameInput = document.getElementById('page-wl-name');
  const roleSelect = document.getElementById('page-wl-role');
  const email = emailInput.value.trim();
  const name = nameInput.value.trim();
  const roleVal = roleSelect.value;
  const is_admin = roleVal === 'admin' ? 1 : 0;
  const role_label = roleVal === 'admin' ? 'Administrator' : 'Pengguna Standar';

  if (!email) {
    toast('Masukkan alamat email Google!', 'error');
    emailInput.focus();
    return;
  }

  try {
    const res = await fetch('/api/whitelist', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({ email, name, role_label, is_admin })
    });
    const data = await res.json();
    if (!res.ok || data.error) {
      toast(data.error || 'Gagal menambahkan akun.', 'error');
      return;
    }
    emailInput.value = '';
    nameInput.value = '';
    roleSelect.value = 'user';
    loadWhitelistData();
    toast('✓ Akun berhasil diotorisasi ke whitelist!', 'success');
  } catch (err) {
    toast('Terjadi kesalahan: ' + err.message, 'error');
  }
}

async function deleteWhitelistUser(id, email) {
  if (!confirm(`Hapus ${email} dari whitelist? Akun ini tidak akan dapat login lagi.`)) return;
  try {
    const res = await fetch(`/api/whitelist/${id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-Token': CSRF }
    });
    const data = await res.json();
    if (!res.ok || data.error) {
      alert(data.error || 'Gagal menghapus akun.');
      return;
    }
    loadWhitelistData();
    toast('Akun dihapus dari whitelist', 'success');
  } catch (err) {
    alert('Terjadi kesalahan: ' + err.message);
  }
}

// ── Icon Picker ──
const FA_BRAND_ICONS = <?= json_encode($FA_BRAND_ICONS) ?>;
const FA_SOLID_ICONS = <?= json_encode($FA_SOLID_ICONS) ?>;

function renderIcons(filter) {
  const grid = document.getElementById('icon-grid');
  const current = document.getElementById('f-icon').value;
  const f = (filter || '').toLowerCase();
  const brands = FA_BRAND_ICONS.filter(i => !f || i.includes(f)).map(i => iconBtn(i, 'fa-brands', current));
  const solids = FA_SOLID_ICONS.filter(i => !f || i.includes(f)).map(i => iconBtn(i, 'fa-solid', current));
  grid.innerHTML = (brands.length ? brands.join('') : '') + (solids.length ? solids.join('') : '');
}

function iconBtn(icon, prefix, current) {
  const sel = current === icon;
  return `<button type="button" onclick="selectIcon('${icon}','${prefix}')"
    style="width:30px;height:30px;border-radius:6px;border:1px solid ${sel ? 'var(--teal-700)' : 'var(--border)'};
           background:${sel ? 'var(--emerald-l)' : '#fff'};color:${sel ? 'var(--emerald-h)' : 'var(--ink-3)'};
           cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.875rem;transition:all .15s">
    <i class="${prefix} ${icon}"></i>
  </button>`;
}

function filterIcons(val) { renderIcons(val); }
function selectIcon(icon, prefix) {
  document.getElementById('f-icon').value = icon;
  document.getElementById('f-icon-prefix').value = prefix || 'fa-brands';
  renderIcons(document.getElementById('icon-search').value);
}

// ── Mobile Drawer ──
function toggleSidebarMobile() {
  const sb = document.getElementById('app-sidebar');
  const bd = document.getElementById('sidebar-backdrop');
  const isOpen = sb.classList.toggle('mobile-open');
  if (bd) bd.classList.toggle('mobile-open', isOpen);
  document.body.style.overflow = isOpen ? 'hidden' : '';
  document.body.style.overflowX = 'hidden';
}

// ── API Fetch & Toast ──
async function apiFetch(url, method, body) {
  const r = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
    body: JSON.stringify(body),
  });
  return r.json();
}

let lastToastMsg = '';
let lastToastTime = 0;

function toast(msg, type = 'success') {
  const cleanMsg = String(msg).replace(/^[✓✔ℹ✕✖✗]\s*/, '').trim();
  const now = Date.now();
  if (cleanMsg === lastToastMsg && now - lastToastTime < 700) {
    return; // Discard duplicate toast within 700ms window
  }
  lastToastMsg = cleanMsg;
  lastToastTime = now;

  const icons = { success: '✓', error: '✕', info: 'ℹ' };
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span class="toast-icon">${icons[type] || 'ℹ'}</span><span>${cleanMsg}</span>`;
  document.getElementById('toast-container').appendChild(el);
  setTimeout(() => { el.style.animation = 'toastOut .22s ease forwards'; setTimeout(() => el.remove(), 220); }, 2600);
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]);
}

// ── Keyboard Shortcuts (Ctrl+K, /, Esc) ──
document.addEventListener('keydown', e => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    const isMobile = window.innerWidth <= 768;
    const target = isMobile ? document.getElementById('mobile-search') : document.getElementById('global-search');
    target?.focus();
  }
  if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
    e.preventDefault();
    const isMobile = window.innerWidth <= 768;
    const target = isMobile ? document.getElementById('mobile-search') : document.getElementById('global-search');
    target?.focus();
  }
  if (e.key === 'Escape') {
    if (document.activeElement.id === 'global-search' || document.activeElement.id === 'mobile-search') {
      clearSearch();
      document.activeElement.blur();
    }
    const sb = document.getElementById('app-sidebar');
    if (sb && sb.classList.contains('mobile-open')) {
      toggleSidebarMobile();
    }
    document.querySelectorAll('.modal-backdrop').forEach(b => b.classList.add('hidden'));
  }
});

document.querySelectorAll('.modal-backdrop').forEach(b => {
  b.addEventListener('click', e => { if (e.target === b) b.classList.add('hidden'); });
});

// ── Kelola Akses (Dual Perspective IAM Hub) ──
let accessUsersList = [];
let currentMatrixData = [];
let originalMatrixState = {}; // profile_id -> { enabled, can_edit }
let selectedAccessEmail = '';

let accessTokensList = [];
let currentAccessTokenId = null;
let currentAccessTokenData = [];
let originalTokenMatrixState = {}; // email -> { enabled, can_edit }

let activeAccessSubTab = 'user';
let auditSharesCache = [];
let selectedAuditShareIds = new Set();
let auditPermissionFilter = 'all'; // 'all' | 'readonly' | 'canedit'

async function initAccessManager() {
  loadAccessMetrics();
  if (activeAccessSubTab === 'user' || activeAccessSubTab === 'matrix') {
    await loadAccessUsersDropdown();
  } else if (activeAccessSubTab === 'token') {
    await loadAccessTokensDropdown();
  } else if (activeAccessSubTab === 'audit') {
    loadAuditSharesTable();
  }
}

async function loadAccessMetrics() {
  try {
    const res = await fetch('/api/access/metrics');
    const data = await res.json();
    if (data.error) return;
    const badgeCount = document.getElementById('access-badge-count');
    const auditCount = document.getElementById('badge-audit-count');

    if (badgeCount) badgeCount.textContent = data.total_shares;
    if (auditCount) auditCount.textContent = data.total_shares;
  } catch (e) {
    console.error('Metrics error', e);
  }
}

async function loadAccessUsersDropdown() {
  const sel = document.getElementById('access-user-select');
  if (!sel) return;
  try {
    const res = await fetch('/api/whitelist');
    const users = await res.json();
    if (users.error) return;
    accessUsersList = users;

    sel.innerHTML = '<option value="" disabled selected>-- Pilih Pengguna / Kolaborator --</option>' +
      users.map(u => `
        <option value="${escapeHtml(u.email)}" ${u.email.toLowerCase() === selectedAccessEmail.toLowerCase() ? 'selected' : ''}>
          ${escapeHtml(u.name || 'User')} (${escapeHtml(u.email)}) — ${escapeHtml(u.role_label || 'Member')}
        </option>
      `).join('');

    if (!selectedAccessEmail && users.length > 0) {
      const candidate = users.find(u => u.email.toLowerCase() !== <?= json_encode(strtolower($user['email'])) ?>) || users[0];
      if (candidate) {
        sel.value = candidate.email;
        onAccessUserSelected(candidate.email);
      }
    } else if (selectedAccessEmail) {
      sel.value = selectedAccessEmail;
      onAccessUserSelected(selectedAccessEmail);
    }
  } catch (e) {
    sel.innerHTML = `<option value="">Gagal memuat pengguna: ${escapeHtml(e.message)}</option>`;
  }
}

async function onAccessUserSelected(email) {
  selectedAccessEmail = email;
  const user = accessUsersList.find(u => u.email.toLowerCase() === email.toLowerCase());

  const summaryBox = document.getElementById('access-user-summary-box');
  const matrixContainer = document.getElementById('access-matrix-container');
  if (summaryBox) summaryBox.style.display = 'flex';
  if (matrixContainer) matrixContainer.style.display = 'flex';

  if (user) {
    const initial = (user.name || user.email).substring(0, 1).toUpperCase();
    const avatarEl = document.getElementById('matrix-user-avatar');
    const nameEl = document.getElementById('matrix-user-name');
    const emailEl = document.getElementById('matrix-user-email');
    const roleBadge = document.getElementById('matrix-user-role-badge');
    if (avatarEl) avatarEl.textContent = initial;
    if (nameEl) nameEl.textContent = user.name || 'Pengguna';
    if (emailEl) emailEl.textContent = user.email;
    if (roleBadge) {
      roleBadge.textContent = user.role_label || 'Member';
      roleBadge.className = user.is_admin ? 'badge badge-amber' : 'badge badge-teal';
    }
  }

  await loadMatrixForUser(email);
}

async function loadMatrixForUser(email) {
  const listEl = document.getElementById('matrix-tokens-list');
  if (!listEl) return;
  listEl.innerHTML = '<div style="padding:2.5rem;text-align:center;color:var(--ink-4)"><i class="fa-solid fa-spinner fa-spin" style="margin-right:.5rem"></i>Memuat daftar token & status akses…</div>';

  try {
    const res = await fetch(`/api/access/matrix?email=${encodeURIComponent(email)}`);
    const matrix = await res.json();
    if (matrix.error) {
      listEl.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">${escapeHtml(matrix.error)}</div>`;
      return;
    }
    currentMatrixData = matrix;
    originalMatrixState = {};
    matrix.forEach(m => {
      originalMatrixState[m.profile_id] = {
        enabled: !!m.is_shared,
        can_edit: !!m.can_edit
      };
    });

    updateMatrixSummaryBadge();
    renderMatrixList(matrix);
    updateMatrixDirtyState();
  } catch (e) {
    listEl.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">Gagal memuat matrix: ${escapeHtml(e.message)}</div>`;
  }
}

function updateMatrixSummaryBadge() {
  const grantedCount = currentMatrixData.filter(m => m.is_shared || m.is_owner).length;
  const totalCount = currentMatrixData.length;
  const badge = document.getElementById('matrix-access-summary-badge');
  if (badge) {
    badge.textContent = `Memiliki akses ke ${grantedCount} dari ${totalCount} token`;
  }
}

function resolveFaClass(icon, issuer = '', name = '') {
  icon = (icon || '').trim();
  if (!icon || icon === 'shield' || icon === 'fa-shield-halved') {
    const hay = ((issuer || '') + ' ' + (name || '')).toLowerCase();
    if (hay.includes('google') || hay.includes('gmail')) return 'fa-brands fa-google';
    if (hay.includes('github')) return 'fa-brands fa-github';
    if (hay.includes('microsoft') || hay.includes('azure') || hay.includes('office') || hay.includes('outlook')) return 'fa-brands fa-microsoft';
    if (hay.includes('amazon') || hay.includes('aws')) return 'fa-brands fa-aws';
    if (hay.includes('apple') || hay.includes('icloud')) return 'fa-brands fa-apple';
    if (hay.includes('discord')) return 'fa-brands fa-discord';
    if (hay.includes('telegram')) return 'fa-brands fa-telegram';
    if (hay.includes('slack')) return 'fa-brands fa-slack';
    if (hay.includes('facebook')) return 'fa-brands fa-facebook';
    if (hay.includes('twitter') || hay.includes(' x ')) return 'fa-brands fa-twitter';
    if (hay.includes('gitlab')) return 'fa-brands fa-gitlab';
    if (hay.includes('bitbucket')) return 'fa-brands fa-bitbucket';
    if (hay.includes('docker')) return 'fa-brands fa-docker';
    if (hay.includes('cloudflare')) return 'fa-brands fa-cloudflare';
    if (hay.includes('digitalocean') || hay.includes('digital-ocean')) return 'fa-brands fa-digital-ocean';
    if (hay.includes('stripe')) return 'fa-brands fa-stripe';
    if (hay.includes('spotify')) return 'fa-brands fa-spotify';
    if (hay.includes('database') || hay.includes('postgres') || hay.includes('mysql')) return 'fa-solid fa-database';
    if (hay.includes('vpn') || hay.includes('cisco')) return 'fa-solid fa-shield-halved';
    return 'fa-solid fa-shield-halved';
  }

  if (/^(fa-(brands|solid|regular)|fab|fas|far)\s+/.test(icon)) {
    return icon;
  }

  const raw = icon.replace(/^fa-/, '');
  const brandList = [
    'github','google','microsoft','apple','amazon','facebook',
    'twitter','instagram','linkedin','slack','discord','telegram',
    'whatsapp','dropbox','spotify','twitch','youtube','reddit',
    'gitlab','bitbucket','docker','aws','cloudflare','digital-ocean',
    'stripe','paypal','shopify','wordpress','jenkins','jira',
    'confluence','trello','npm','node','react','vuejs','angular',
    'laravel','php','python','java','swift','android','windows',
    'linux','ubuntu','firefox','chrome','safari','steam','playstation','xbox'
  ];

  if (brandList.includes(raw) || brandList.includes(icon)) {
    return 'fa-brands fa-' + raw;
  }

  return 'fa-solid fa-' + raw;
}

function renderMatrixList(items) {
  const listEl = document.getElementById('matrix-tokens-list');
  if (!listEl) return;
  const searchVal = (document.getElementById('matrix-search')?.value || '').toLowerCase().trim();

  const filtered = items.filter(m => {
    if (!searchVal) return true;
    return m.name.toLowerCase().includes(searchVal) || (m.issuer && m.issuer.toLowerCase().includes(searchVal));
  });

  if (filtered.length === 0) {
    listEl.innerHTML = '<div style="padding:2.5rem;text-align:center;color:var(--ink-4);font-size:.875rem"><i class="fa-solid fa-magnifying-glass" style="font-size:1.5rem;margin-bottom:.5rem;display:block"></i>Tidak ada token yang sesuai filter pencarian.</div>';
    return;
  }

  listEl.innerHTML = filtered.map(m => {
    const pid = m.profile_id;
    const isOwner = !!m.is_owner;
    const isShared = !!m.is_shared;
    const canEdit = !!m.can_edit;
    const isChecked = isOwner || isShared;

    return `
      <div class="matrix-token-item ${isChecked ? 'granted' : ''} ${isOwner ? 'is-owner-item' : ''}" id="matrix-item-${pid}">
        <div class="matrix-token-left">
          <label class="matrix-check-label">
            <input type="checkbox" id="mat-chk-${pid}" style="width:18px;height:18px;cursor:${isOwner ? 'not-allowed' : 'pointer'}"
              ${isChecked ? 'checked' : ''} ${isOwner ? 'disabled title="Pemilik token selalu memiliki akses"' : ''}
              onchange="onMatrixCheckChanged(${pid}, this.checked)">
          </label>

          <div class="token-icon-squircle" style="background:${escapeHtml(m.color || '#0b332d')};width:36px;height:36px;font-size:1rem">
            <i class="${escapeHtml(resolveFaClass(m.icon, m.issuer, m.name))}"></i>
          </div>

          <div style="min-width:0;display:flex;flex-direction:column;gap:.1rem">
            <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap">
              <span style="font-weight:700;color:var(--ink);font-size:.875rem" class="truncate">${escapeHtml(m.name)}</span>
              ${m.issuer ? `<span class="token-issuer-tag" style="font-size:.65rem">${escapeHtml(m.issuer)}</span>` : ''}
              ${isOwner ? '<span class="badge badge-amber" style="font-size:.65rem;padding:.1rem .4rem"><i class="fa-solid fa-crown"></i> Pemilik Token</span>' : (isShared ? '<span class="badge badge-green" style="font-size:.65rem;padding:.1rem .4rem">Akses Aktif</span>' : '')}
            </div>
            <span style="font-size:.72rem;color:var(--ink-4)">
              ${isOwner ? 'Token dibuat oleh pengguna ini (Akses Penuh)' : `Milik: ${escapeHtml(m.owner_name || m.owner_email)}`}
            </span>
          </div>
        </div>

        <div class="matrix-token-right">
          ${isOwner ? `
            <span style="font-size:.75rem;font-weight:600;color:var(--ink-3)">Full Access</span>
          ` : `
            <div class="perm-pill-toggle" id="perm-group-${pid}" style="${!isChecked ? 'opacity:0.4;pointer-events:none' : ''}">
              <button type="button" class="perm-pill-btn ${!canEdit ? 'active' : ''}" id="perm-btn-${pid}-0"
                onclick="setMatrixPermission(${pid}, false)">
                <i class="fa-solid fa-eye" style="margin-right:.25rem"></i>Hanya Lihat
              </button>
              <button type="button" class="perm-pill-btn ${canEdit ? 'active' : ''}" id="perm-btn-${pid}-1"
                onclick="setMatrixPermission(${pid}, true)">
                <i class="fa-solid fa-pen-to-square" style="margin-right:.25rem"></i>Boleh Edit
              </button>
            </div>
          `}
        </div>
      </div>
    `;
  }).join('');
}

function onMatrixCheckChanged(profileId, isChecked) {
  const item = currentMatrixData.find(m => m.profile_id === profileId);
  if (!item || item.is_owner) return;
  item.is_shared = isChecked;

  const card = document.getElementById(`matrix-item-${profileId}`);
  if (card) card.classList.toggle('granted', isChecked);

  const permGroup = document.getElementById(`perm-group-${profileId}`);
  if (permGroup) {
    permGroup.style.opacity = isChecked ? '1' : '0.4';
    permGroup.style.pointerEvents = isChecked ? 'auto' : 'none';
  }

  updateMatrixSummaryBadge();
  updateMatrixDirtyState();
}

function setMatrixPermission(profileId, canEdit) {
  const item = currentMatrixData.find(m => m.profile_id === profileId);
  if (!item || item.is_owner) return;
  item.can_edit = canEdit ? 1 : 0;

  const btn0 = document.getElementById(`perm-btn-${profileId}-0`);
  const btn1 = document.getElementById(`perm-btn-${profileId}-1`);
  if (btn0) btn0.classList.toggle('active', !canEdit);
  if (btn1) btn1.classList.toggle('active', !!canEdit);

  updateMatrixDirtyState();
}

function matrixSelectAll(enable) {
  currentMatrixData.forEach(m => {
    if (m.is_owner) return;
    m.is_shared = enable;
  });
  renderMatrixList(currentMatrixData);
  updateMatrixSummaryBadge();
  updateMatrixDirtyState();
}

function matrixSetAllPermissions(canEditVal) {
  currentMatrixData.forEach(m => {
    if (m.is_owner || !m.is_shared) return;
    m.can_edit = canEditVal ? 1 : 0;
  });
  renderMatrixList(currentMatrixData);
  updateMatrixDirtyState();
}

function filterMatrixTokens(val) {
  renderMatrixList(currentMatrixData);
}

function updateMatrixDirtyState() {
  let dirtyCount = 0;
  currentMatrixData.forEach(m => {
    if (m.is_owner) return;
    const orig = originalMatrixState[m.profile_id] || { enabled: false, can_edit: false };
    const curEnabled = !!m.is_shared;
    const curEdit = !!m.can_edit;
    if (curEnabled !== orig.enabled || (curEnabled && curEdit !== orig.can_edit)) {
      dirtyCount++;
    }
  });

  const statusText = document.getElementById('matrix-status-text');
  if (statusText) {
    if (dirtyCount > 0) {
      statusText.innerHTML = `<span style="color:#a7f3d0;font-weight:700">Ada ${dirtyCount} perubahan belum disimpan.</span> Klik tombol Simpan untuk menerapkan.`;
    } else {
      statusText.textContent = 'Semua perubahan hak akses pengguna sudah tersimpan.';
    }
  }
}

async function saveAccessMatrix() {
  if (!selectedAccessEmail) {
    toast('Pilih pengguna terlebih dahulu', 'error');
    return;
  }

  const saveBtn = document.getElementById('btn-save-matrix');
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan…';
  }

  const payload = {
    email: selectedAccessEmail,
    shares: currentMatrixData.filter(m => !m.is_owner).map(m => ({
      profile_id: m.profile_id,
      enabled: !!m.is_shared,
      can_edit: !!m.can_edit
    }))
  };

  try {
    const res = await fetch('/api/access/sync', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!res.ok || data.error) {
      toast(data.error || 'Gagal menyimpan akses.', 'error');
      return;
    }

    toast(`✓ Akses untuk ${selectedAccessEmail} berhasil diperbarui!`, 'success');
    loadAccessMetrics();
    await loadMatrixForUser(selectedAccessEmail);
  } catch (e) {
    toast('Terjadi kesalahan: ' + e.message, 'error');
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Simpan Akses Pengguna';
    }
  }
}

function reloadCurrentMatrix() {
  if (selectedAccessEmail) {
    loadMatrixForUser(selectedAccessEmail);
  }
}

async function revokeAllAccessCurrentTarget() {
  if (!selectedAccessEmail) return;
  if (!confirm(`Cabut SEMUA hak akses token untuk ${selectedAccessEmail}? (Proses Offboarding)`)) return;

  try {
    const res = await fetch('/api/access/revoke-all', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({ email: selectedAccessEmail })
    });
    const data = await res.json();
    if (!res.ok || data.error) {
      toast(data.error || 'Gagal mencabut akses.', 'error');
      return;
    }
    toast(`✓ Seluruh akses token untuk ${selectedAccessEmail} berhasil dicabut (${data.revoked_count} token)`, 'success');
    loadAccessMetrics();
    await loadMatrixForUser(selectedAccessEmail);
  } catch (e) {
    toast('Terjadi kesalahan: ' + e.message, 'error');
  }
}

// ── Sub-Tabs & View Switching (Dual Perspective) ──
function switchAccessSubTab(tab) {
  if (tab === 'matrix') tab = 'user';

  activeAccessSubTab = tab;
  document.getElementById('subtab-btn-user')?.classList.toggle('active', tab === 'user');
  document.getElementById('subtab-btn-token')?.classList.toggle('active', tab === 'token');
  document.getElementById('subtab-btn-audit')?.classList.toggle('active', tab === 'audit');
  document.getElementById('subtab-btn-multishare')?.classList.toggle('active', tab === 'multishare');

  const userWrap = document.getElementById('access-subview-user');
  const tokenWrap = document.getElementById('access-subview-token');
  const auditWrap = document.getElementById('access-subview-audit');
  const multiShareWrap = document.getElementById('access-subview-multishare');

  if (userWrap) userWrap.style.display = tab === 'user' ? 'flex' : 'none';
  if (tokenWrap) tokenWrap.style.display = tab === 'token' ? 'flex' : 'none';
  if (auditWrap) auditWrap.style.display = tab === 'audit' ? 'flex' : 'none';
  if (multiShareWrap) multiShareWrap.style.display = tab === 'multishare' ? 'flex' : 'none';

  if (tab === 'user') {
    if (!selectedAccessEmail && accessUsersList.length === 0) {
      loadAccessUsersDropdown();
    } else if (selectedAccessEmail) {
      loadMatrixForUser(selectedAccessEmail);
    }
  } else if (tab === 'token') {
    if (!currentAccessTokenId && accessTokensList.length === 0) {
      loadAccessTokensDropdown();
    } else if (currentAccessTokenId) {
      loadMatrixForToken(currentAccessTokenId);
    }
  } else if (tab === 'audit') {
    loadAuditSharesTable();
  } else if (tab === 'multishare') {
    initMultiShareTab();
  }
}

// ── Token-Centric Matrix Functions ──
async function loadAccessTokensDropdown() {
  const sel = document.getElementById('access-token-select');
  if (!sel) return;
  try {
    const res = await fetch('/api/access/all-tokens');
    const tokens = await res.json();
    if (tokens.error) return;
    accessTokensList = tokens;

    sel.innerHTML = '<option value="" disabled selected>-- Pilih Token Vault --</option>' +
      tokens.map(t => `
        <option value="${t.id}" ${currentAccessTokenId === t.id ? 'selected' : ''}>
          ${escapeHtml(t.issuer ? '[' + t.issuer + '] ' : '')}${escapeHtml(t.name)} (Milik: ${escapeHtml(t.owner_name || t.owner_email)})
        </option>
      `).join('');

    if (!currentAccessTokenId && tokens.length > 0) {
      sel.value = tokens[0].id;
      onAccessTokenSelected(tokens[0].id);
    } else if (currentAccessTokenId) {
      sel.value = currentAccessTokenId;
      onAccessTokenSelected(currentAccessTokenId);
    }
  } catch (e) {
    sel.innerHTML = `<option value="">Gagal memuat token: ${escapeHtml(e.message)}</option>`;
  }
}

async function onAccessTokenSelected(tokenId) {
  tokenId = parseInt(tokenId);
  if (!tokenId) return;
  currentAccessTokenId = tokenId;
  const token = accessTokensList.find(t => t.id === tokenId);

  const summaryBox = document.getElementById('access-token-summary-box');
  const matrixContainer = document.getElementById('access-token-matrix-container');
  if (summaryBox) summaryBox.style.display = 'flex';
  if (matrixContainer) matrixContainer.style.display = 'flex';

  if (token) {
    const squircle = document.getElementById('token-summary-squircle');
    const iconEl = document.getElementById('token-summary-icon');
    const nameEl = document.getElementById('token-summary-name');
    const issuerBadge = document.getElementById('token-summary-issuer-badge');
    const ownerEl = document.getElementById('token-summary-owner');

    if (squircle) squircle.style.background = token.color || '#0b332d';
    if (iconEl) iconEl.className = resolveFaClass(token.icon, token.issuer, token.name);
    if (nameEl) nameEl.textContent = token.name;
    if (issuerBadge) {
      if (token.issuer) {
        issuerBadge.textContent = token.issuer;
        issuerBadge.style.display = 'inline-block';
      } else {
        issuerBadge.style.display = 'none';
      }
    }
    if (ownerEl) ownerEl.textContent = `Dibuat oleh: ${token.owner_name || token.owner_email}`;
  }

  await loadMatrixForToken(tokenId);
}

async function loadMatrixForToken(tokenId) {
  const listEl = document.getElementById('token-matrix-users-list');
  if (!listEl) return;
  listEl.innerHTML = '<div style="padding:2.5rem;text-align:center;color:var(--ink-4)"><i class="fa-solid fa-spinner fa-spin" style="margin-right:.5rem"></i>Memuat daftar personil & hak akses token…</div>';

  try {
    const res = await fetch(`/api/access/token-matrix?profile_id=${tokenId}`);
    const data = await res.json();
    if (data.error) {
      listEl.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">${escapeHtml(data.error)}</div>`;
      return;
    }
    currentAccessTokenData = data.users || [];
    originalTokenMatrixState = {};
    currentAccessTokenData.forEach(u => {
      originalTokenMatrixState[u.email.toLowerCase()] = {
        enabled: !!u.has_access,
        can_edit: !!u.can_edit
      };
    });

    updateTokenMatrixSummaryBadge();
    renderTokenMatrixList(currentAccessTokenData);
    updateTokenMatrixDirtyState();
  } catch (e) {
    listEl.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">Gagal memuat akses token: ${escapeHtml(e.message)}</div>`;
  }
}

function updateTokenMatrixSummaryBadge() {
  const activeMembers = currentAccessTokenData.filter(u => u.has_access).length;
  const totalMembers = currentAccessTokenData.length;
  const badge = document.getElementById('token-access-summary-badge');
  if (badge) {
    badge.textContent = `Dibagikan ke ${activeMembers} dari ${totalMembers} anggota`;
  }
}

function renderTokenMatrixList(users) {
  const listEl = document.getElementById('token-matrix-users-list');
  if (!listEl) return;
  const searchVal = (document.getElementById('token-matrix-search')?.value || '').toLowerCase().trim();

  const filtered = users.filter(u => {
    if (!searchVal) return true;
    return u.name.toLowerCase().includes(searchVal) || u.email.toLowerCase().includes(searchVal);
  });

  if (filtered.length === 0) {
    listEl.innerHTML = '<div style="padding:2.5rem;text-align:center;color:var(--ink-4);font-size:.875rem"><i class="fa-solid fa-user-xmark" style="font-size:1.5rem;margin-bottom:.5rem;display:block"></i>Tidak ada anggota yang cocok dengan filter pencarian.</div>';
    return;
  }

  listEl.innerHTML = filtered.map(u => {
    const emailLower = u.email.toLowerCase();
    const isOwner = !!u.is_owner;
    const isChecked = !!u.has_access;
    const canEdit = !!u.can_edit;
    const initial = (u.name || u.email).substring(0, 1).toUpperCase();

    return `
      <div class="matrix-user-item ${isChecked ? 'granted' : ''} ${isOwner ? 'is-owner-item' : ''}" id="token-mat-item-${escapeHtml(emailLower)}">
        <div class="matrix-user-left">
          <label class="matrix-check-label">
            <input type="checkbox" id="token-mat-chk-${escapeHtml(emailLower)}" style="width:18px;height:18px;cursor:${isOwner ? 'not-allowed' : 'pointer'}"
              ${isChecked ? 'checked' : ''} ${isOwner ? 'disabled title="Pemilik token selalu memiliki akses"' : ''}
              onchange="onTokenMatrixUserCheckChanged('${escapeHtml(emailLower)}', this.checked)">
          </label>

          <div class="table-user-avatar" style="width:36px;height:36px;font-size:.95rem;flex-shrink:0">
            ${initial}
          </div>

          <div style="min-width:0;display:flex;flex-direction:column;gap:.1rem">
            <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap">
              <span style="font-weight:700;color:var(--ink);font-size:.875rem" class="truncate">${escapeHtml(u.name)}</span>
              <span class="badge ${u.role === 'admin' ? 'badge-amber' : 'badge-teal'}" style="font-size:.65rem;padding:.1rem .4rem">${escapeHtml(u.role === 'admin' ? 'Admin' : 'Member')}</span>
              ${isOwner ? '<span class="badge badge-amber" style="font-size:.65rem;padding:.1rem .4rem"><i class="fa-solid fa-crown"></i> Pemilik Token</span>' : (isChecked ? '<span class="badge badge-green" style="font-size:.65rem;padding:.1rem .4rem">Akses Aktif</span>' : '')}
            </div>
            <span style="font-size:.72rem;color:var(--ink-4)">${escapeHtml(u.email)}</span>
          </div>
        </div>

        <div class="matrix-user-right">
          ${isOwner ? `
            <span style="font-size:.75rem;font-weight:600;color:var(--ink-3)">Full Control</span>
          ` : `
            <div class="perm-pill-toggle" id="token-perm-group-${escapeHtml(emailLower)}" style="${!isChecked ? 'opacity:0.4;pointer-events:none' : ''}">
              <button type="button" class="perm-pill-btn ${!canEdit ? 'active' : ''}" id="token-perm-btn-${escapeHtml(emailLower)}-0"
                onclick="setTokenMatrixUserPermission('${escapeHtml(emailLower)}', false)">
                <i class="fa-solid fa-eye" style="margin-right:.25rem"></i>Hanya Lihat
              </button>
              <button type="button" class="perm-pill-btn ${canEdit ? 'active' : ''}" id="token-perm-btn-${escapeHtml(emailLower)}-1"
                onclick="setTokenMatrixUserPermission('${escapeHtml(emailLower)}', true)">
                <i class="fa-solid fa-pen-to-square" style="margin-right:.25rem"></i>Boleh Edit
              </button>
            </div>
          `}
        </div>
      </div>
    `;
  }).join('');
}

function onTokenMatrixUserCheckChanged(emailLower, isChecked) {
  const item = currentAccessTokenData.find(u => u.email.toLowerCase() === emailLower);
  if (!item || item.is_owner) return;
  item.has_access = isChecked;

  const row = document.getElementById(`token-mat-item-${emailLower}`);
  if (row) row.classList.toggle('granted', isChecked);

  const permGroup = document.getElementById(`token-perm-group-${emailLower}`);
  if (permGroup) {
    permGroup.style.opacity = isChecked ? '1' : '0.4';
    permGroup.style.pointerEvents = isChecked ? 'auto' : 'none';
  }

  updateTokenMatrixSummaryBadge();
  updateTokenMatrixDirtyState();
}

function setTokenMatrixUserPermission(emailLower, canEdit) {
  const item = currentAccessTokenData.find(u => u.email.toLowerCase() === emailLower);
  if (!item || item.is_owner) return;
  item.can_edit = canEdit;

  const btn0 = document.getElementById(`token-perm-btn-${emailLower}-0`);
  const btn1 = document.getElementById(`token-perm-btn-${emailLower}-1`);
  if (btn0) btn0.classList.toggle('active', !canEdit);
  if (btn1) btn1.classList.toggle('active', !!canEdit);

  updateTokenMatrixDirtyState();
}

function tokenMatrixSelectAll(enable) {
  currentAccessTokenData.forEach(u => {
    if (u.is_owner) return;
    u.has_access = enable;
  });
  renderTokenMatrixList(currentAccessTokenData);
  updateTokenMatrixSummaryBadge();
  updateTokenMatrixDirtyState();
}

function tokenMatrixSetAllPermissions(canEditVal) {
  currentAccessTokenData.forEach(u => {
    if (u.is_owner || !u.has_access) return;
    u.can_edit = (canEditVal === 1);
  });
  renderTokenMatrixList(currentAccessTokenData);
  updateTokenMatrixDirtyState();
}

function filterTokenMatrixUsers(val) {
  renderTokenMatrixList(currentAccessTokenData);
}

function updateTokenMatrixDirtyState() {
  let dirtyCount = 0;
  currentAccessTokenData.forEach(u => {
    if (u.is_owner) return;
    const emailKey = u.email.toLowerCase();
    const orig = originalTokenMatrixState[emailKey] || { enabled: false, can_edit: false };
    const curEnabled = !!u.has_access;
    const curEdit = !!u.can_edit;
    if (curEnabled !== orig.enabled || (curEnabled && curEdit !== orig.can_edit)) {
      dirtyCount++;
    }
  });

  const statusText = document.getElementById('token-matrix-status-text');
  if (statusText) {
    if (dirtyCount > 0) {
      statusText.innerHTML = `<span style="color:#a7f3d0;font-weight:700">Ada ${dirtyCount} perubahan belum disimpan.</span> Klik tombol Simpan untuk menerapkan.`;
    } else {
      statusText.textContent = 'Semua perubahan hak akses untuk token ini sudah tersimpan.';
    }
  }
}

function reloadCurrentTokenMatrix() {
  if (currentAccessTokenId) {
    loadMatrixForToken(currentAccessTokenId);
  }
}

async function saveTokenMatrix() {
  if (!currentAccessTokenId) return;

  const saveBtn = document.getElementById('btn-save-token-matrix');
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan…';
  }

  const payload = {
    profile_id: currentAccessTokenId,
    shares: currentAccessTokenData
      .filter(u => !u.is_owner)
      .map(u => ({
        email: u.email,
        enabled: !!u.has_access,
        can_edit: !!u.can_edit
      }))
  };

  try {
    const res = await fetch('/api/access/sync-token', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!res.ok || data.error) {
      toast(data.error || 'Gagal menyimpan akses.', 'error');
      return;
    }

    toast(`✓ Delegasi token berhasil disimpan (+${data.added} baru, ~${data.updated} diubah, -${data.removed} dicabut)`, 'success');
    loadAccessMetrics();
    await loadMatrixForToken(currentAccessTokenId);
  } catch (e) {
    toast('Terjadi kesalahan: ' + e.message, 'error');
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Simpan Delegasi Token';
    }
  }
}

function setAuditPermissionFilter(mode) {
  auditPermissionFilter = mode;
  document.getElementById('audit-filter-all')?.classList.toggle('active', mode === 'all');
  document.getElementById('audit-filter-readonly')?.classList.toggle('active', mode === 'readonly');
  document.getElementById('audit-filter-canedit')?.classList.toggle('active', mode === 'canedit');
  renderAuditSharesTable(auditSharesCache);
}

function toggleMultiShareDrawer() {
  const ms = document.getElementById('access-subview-multishare');
  if (!ms) return;
  const isHidden = ms.style.display === 'none' || !ms.style.display;
  ms.style.display = isHidden ? 'flex' : 'none';
  if (isHidden) {
    initMultiShareTab();
    ms.scrollIntoView({ behavior: 'smooth' });
  }
}

async function loadAuditSharesTable() {
  const wrap = document.getElementById('audit-table-wrap');
  if (!wrap) return;
  wrap.innerHTML = '<div style="padding:2.5rem;text-align:center;color:var(--ink-4)"><i class="fa-solid fa-spinner fa-spin" style="margin-right:.5rem"></i>Memuat riwayat sharing…</div>';

  try {
    const res = await fetch('/api/access/shares');
    const shares = await res.json();
    if (shares.error) {
      wrap.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">${escapeHtml(shares.error)}</div>`;
      return;
    }
    auditSharesCache = shares;
    selectedAuditShareIds.clear();
    updateAuditBulkBar();
    const totalEl = document.getElementById('audit-table-total');
    const badgeEl = document.getElementById('badge-audit-count');
    if (totalEl) totalEl.textContent = shares.length;
    if (badgeEl) badgeEl.textContent = shares.length;
    renderAuditSharesTable(shares);
  } catch (e) {
    wrap.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">Gagal memuat: ${escapeHtml(e.message)}</div>`;
  }
}

function renderAuditSharesTable(shares) {
  const wrap = document.getElementById('audit-table-wrap');
  if (!wrap) return;
  const searchVal = (document.getElementById('audit-search-input')?.value || '').toLowerCase().trim();

  const filtered = shares.filter(s => {
    if (auditPermissionFilter === 'readonly' && s.can_edit) return false;
    if (auditPermissionFilter === 'canedit' && !s.can_edit) return false;
    if (!searchVal) return true;
    return s.profile_name.toLowerCase().includes(searchVal) ||
           (s.issuer && s.issuer.toLowerCase().includes(searchVal)) ||
           s.shared_with_email.toLowerCase().includes(searchVal) ||
           (s.target_name && s.target_name.toLowerCase().includes(searchVal));
  });

  if (filtered.length === 0) {
    wrap.innerHTML = '<div style="padding:3rem;text-align:center;color:var(--ink-4);font-size:.875rem"><i class="fa-solid fa-share-nodes" style="font-size:1.75rem;margin-bottom:.5rem;display:block"></i>Tidak ada relasi sharing aktif ditemukan.</div>';
    return;
  }

  wrap.innerHTML = `
    <table style="width:100%;border-collapse:collapse;font-size:.8125rem;text-align:left">
      <thead>
        <tr style="background:#07080d;border-bottom:1px solid rgba(255,255,255,0.08);color:#64748b">
          <th style="padding:.75rem .875rem;width:40px">
            <input type="checkbox" onchange="toggleAuditSelectAll(this.checked)" style="width:16px;height:16px;cursor:pointer">
          </th>
          <th style="padding:.75rem .875rem;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Token 2FA</th>
          <th style="padding:.75rem .875rem;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Dibagikan Ke (Kolaborator)</th>
          <th style="padding:.75rem .875rem;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Hak Akses</th>
          <th style="padding:.75rem .875rem;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Pemilik Token</th>
          <th style="padding:.75rem .875rem;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Tanggal Dibuat</th>
          <th style="padding:.75rem .875rem;text-align:right;font-weight:700;font-size:.6875rem;text-transform:uppercase;letter-spacing:.06em">Aksi</th>
        </tr>
      </thead>
      <tbody>
        ${filtered.map(s => {
          const initial = (s.target_name || s.shared_with_email).substring(0, 1).toUpperCase();
          const isSelected = selectedAuditShareIds.has(s.share_id);
          const dateStr = s.created_at ? s.created_at.substring(0, 16) : '-';

          return `
            <tr style="border-bottom:1px solid rgba(255,255,255,0.04);background:${isSelected ? 'rgba(16,185,129,0.12)' : 'transparent'}">
              <td style="padding:.75rem .875rem">
                <input type="checkbox" ${isSelected ? 'checked' : ''} onchange="onAuditRowCheckChanged(${s.share_id}, this.checked)" style="width:16px;height:16px;cursor:pointer">
              </td>
              <td style="padding:.75rem .875rem">
                <div style="display:flex;align-items:center;gap:.625rem">
                  <div class="token-icon-squircle" style="background:${escapeHtml(s.color || '#0b332d')};width:30px;height:30px;font-size:.8125rem">
                    <i class="${escapeHtml(resolveFaClass(s.icon, s.issuer, s.profile_name))}"></i>
                  </div>
                  <div>
                    <span style="font-weight:700;color:#ffffff;display:block">${escapeHtml(s.profile_name)}</span>
                    ${s.issuer ? `<span style="font-size:.6875rem;color:#7a9c95">${escapeHtml(s.issuer)}</span>` : ''}
                  </div>
                </div>
              </td>
              <td style="padding:.75rem .875rem">
                <div class="table-user-cell">
                  <div class="table-user-avatar" style="width:28px;height:28px;font-size:.75rem">${initial}</div>
                  <div class="table-user-info">
                    <span class="table-user-name">${escapeHtml(s.target_name || 'Kolaborator')}</span>
                    <span class="table-user-email">${escapeHtml(s.shared_with_email)}</span>
                  </div>
                </div>
              </td>
              <td style="padding:.75rem .875rem">
                ${s.can_edit ? 
                  '<span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.6875rem;background:var(--emerald-l);color:var(--emerald-h);padding:.15rem .5rem;border-radius:var(--r-pill);font-weight:700"><i class="fa-solid fa-pen-to-square"></i> Boleh Edit</span>' :
                  '<span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.6875rem;background:#f1f5f9;color:var(--ink-3);padding:.15rem .5rem;border-radius:var(--r-pill);font-weight:600"><i class="fa-solid fa-eye"></i> Hanya Lihat</span>'
                }
              </td>
              <td style="padding:.75rem .875rem;color:var(--ink-3);font-size:.75rem">
                ${escapeHtml(s.owner_name || s.owner_email)}
              </td>
              <td style="padding:.75rem .875rem;color:var(--ink-4);font-size:.72rem">
                ${escapeHtml(dateStr)}
              </td>
              <td style="padding:.75rem .875rem;text-align:right">
                <button class="btn btn-ghost btn-sm" style="color:var(--red);padding:.25rem .5rem;font-size:.75rem"
                  onclick="revokeSingleShare(${s.share_id}, '${escapeHtml(s.profile_name)}', '${escapeHtml(s.shared_with_email)}')">
                  <i class="fa-solid fa-trash-can"></i> Cabut
                </button>
              </td>
            </tr>
          `;
        }).join('')}
      </tbody>
    </table>
  `;
}

function filterAuditShares(val) {
  renderAuditSharesTable(auditSharesCache);
}

function onAuditRowCheckChanged(shareId, checked) {
  if (checked) selectedAuditShareIds.add(shareId);
  else selectedAuditShareIds.delete(shareId);
  updateAuditBulkBar();
}

function toggleAuditSelectAll(checked) {
  auditSharesCache.forEach(s => {
    if (checked) selectedAuditShareIds.add(s.share_id);
    else selectedAuditShareIds.delete(s.share_id);
  });
  renderAuditSharesTable(auditSharesCache);
  updateAuditBulkBar();
}

function updateAuditBulkBar() {
  const btn = document.getElementById('btn-bulk-revoke-shares');
  const countEl = document.getElementById('audit-selected-count');
  const count = selectedAuditShareIds.size;
  if (btn) btn.style.display = count > 0 ? 'inline-flex' : 'none';
  if (countEl) countEl.textContent = count;
}

async function revokeSingleShare(shareId, profileName, email) {
  if (!confirm(`Cabut akses token "${profileName}" dari ${email}?`)) return;
  try {
    const res = await fetch('/api/access/bulk-revoke', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({ share_ids: [shareId] })
    });
    const data = await res.json();
    if (!res.ok || data.error) {
      toast(data.error || 'Gagal mencabut akses.', 'error');
      return;
    }
    toast('Akses token berhasil dicabut', 'success');
    loadAccessMetrics();
    loadAuditSharesTable();
  } catch (e) {
    toast('Terjadi kesalahan: ' + e.message, 'error');
  }
}

async function bulkRevokeSelectedShares() {
  const count = selectedAuditShareIds.size;
  if (count === 0) return;
  if (!confirm(`Cabut ${count} relasi sharing token terpilih sekaligus?`)) return;

  try {
    const res = await fetch('/api/access/bulk-revoke', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({ share_ids: Array.from(selectedAuditShareIds) })
    });
    const data = await res.json();
    if (!res.ok || data.error) {
      toast(data.error || 'Gagal mencabut akses massal.', 'error');
      return;
    }
    toast(`✓ Berhasil mencabut ${data.revoked_count} hak akses token massal!`, 'success');
    selectedAuditShareIds.clear();
    loadAccessMetrics();
    loadAuditSharesTable();
  } catch (e) {
    toast('Terjadi kesalahan: ' + e.message, 'error');
  }
}

// ── Multi-Share Massal (Banyak Token ke Banyak Akun) ──
let multiShareTokensCache = [];
let multiShareUsersCache = [];
let selectedMultiTokenIds = new Set();
let selectedMultiUserEmails = new Set();
let multiShareAction = 'grant'; // 'grant' | 'revoke'
let multiShareCanEdit = 0; // 0: Read-Only, 1: Can Edit

async function initMultiShareTab() {
  await Promise.all([
    loadMultiShareTokens(),
    loadMultiShareUsers()
  ]);
  updateMultiShareImpact();
}

async function loadMultiShareTokens() {
  const listEl = document.getElementById('multishare-tokens-list');
  if (!listEl) return;
  try {
    const res = await fetch('/api/access/all-tokens');
    const tokens = await res.json();
    if (tokens.error) {
      listEl.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">${escapeHtml(tokens.error)}</div>`;
      return;
    }
    multiShareTokensCache = tokens;
    renderMultiShareTokensList(tokens);
  } catch (e) {
    listEl.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">Gagal memuat token: ${escapeHtml(e.message)}</div>`;
  }
}

function renderMultiShareTokensList(tokens) {
  const listEl = document.getElementById('multishare-tokens-list');
  if (!listEl) return;
  const searchVal = (document.getElementById('multishare-token-search')?.value || '').toLowerCase().trim();

  const filtered = tokens.filter(t => {
    if (!searchVal) return true;
    return (t.name && t.name.toLowerCase().includes(searchVal)) ||
           (t.issuer && t.issuer.toLowerCase().includes(searchVal)) ||
           (t.owner_email && t.owner_email.toLowerCase().includes(searchVal));
  });

  if (filtered.length === 0) {
    listEl.innerHTML = '<div style="padding:2.5rem;text-align:center;color:var(--ink-4);font-size:.84rem"><i class="fa-solid fa-magnifying-glass" style="margin-bottom:.4rem;display:block"></i>Tidak ada token yang sesuai filter.</div>';
    return;
  }

  listEl.innerHTML = filtered.map(t => {
    const isSelected = selectedMultiTokenIds.has(t.id);
    return `
      <div class="multishare-item ${isSelected ? 'selected' : ''}" id="multishare-token-item-${t.id}" onclick="toggleMultiShareToken(${t.id})">
        <div class="multishare-item-left">
          <input type="checkbox" ${isSelected ? 'checked' : ''} onclick="event.stopPropagation(); toggleMultiShareToken(${t.id})" style="width:17px;height:17px;cursor:pointer;accent-color:var(--emerald-h)">
          <div class="token-icon-squircle" style="background:${escapeHtml(t.color || '#0b332d')};width:32px;height:32px;font-size:.875rem">
            <i class="${escapeHtml(resolveFaClass(t.icon, t.issuer, t.name))}"></i>
          </div>
          <div style="min-width:0;display:flex;flex-direction:column;gap:.1rem">
            <div style="display:flex;align-items:center;gap:.35rem">
              <span style="font-weight:700;color:var(--ink);font-size:.84rem" class="truncate">${escapeHtml(t.name)}</span>
              ${t.issuer ? `<span class="token-issuer-tag" style="font-size:.65rem">${escapeHtml(t.issuer)}</span>` : ''}
            </div>
            <span style="font-size:.7rem;color:var(--ink-4)">Owner: ${escapeHtml(t.owner_name || t.owner_email)}</span>
          </div>
        </div>
        <div style="font-size:.7rem;color:var(--ink-4);flex-shrink:0">
          ${escapeHtml(t.algorithm || 'SHA1')} &bull; ${t.period || 30}s
        </div>
      </div>
    `;
  }).join('');

  updateMultiShareTokenBadge();
}

async function loadMultiShareUsers() {
  const listEl = document.getElementById('multishare-users-list');
  if (!listEl) return;
  try {
    const res = await fetch('/api/whitelist');
    const users = await res.json();
    if (users.error) {
      listEl.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">${escapeHtml(users.error)}</div>`;
      return;
    }
    multiShareUsersCache = users;
    renderMultiShareUsersList(users);
  } catch (e) {
    listEl.innerHTML = `<div style="padding:2rem;color:var(--red);text-align:center">Gagal memuat akun: ${escapeHtml(e.message)}</div>`;
  }
}

function renderMultiShareUsersList(users) {
  const listEl = document.getElementById('multishare-users-list');
  if (!listEl) return;
  const searchVal = (document.getElementById('multishare-user-search')?.value || '').toLowerCase().trim();

  const filtered = users.filter(u => {
    if (!searchVal) return true;
    return (u.name && u.name.toLowerCase().includes(searchVal)) ||
           (u.email && u.email.toLowerCase().includes(searchVal)) ||
           (u.role_label && u.role_label.toLowerCase().includes(searchVal));
  });

  if (filtered.length === 0) {
    listEl.innerHTML = '<div style="padding:2.5rem;text-align:center;color:var(--ink-4);font-size:.84rem"><i class="fa-solid fa-magnifying-glass" style="margin-bottom:.4rem;display:block"></i>Tidak ada akun yang sesuai filter.</div>';
    return;
  }

  listEl.innerHTML = filtered.map(u => {
    const emailLower = u.email.toLowerCase();
    const isSelected = selectedMultiUserEmails.has(emailLower);
    const initial = (u.name || u.email).substring(0, 1).toUpperCase();

    return `
      <div class="multishare-item ${isSelected ? 'selected' : ''}" id="multishare-user-item-${escapeHtml(emailLower)}" onclick="toggleMultiShareUser('${escapeHtml(emailLower)}')">
        <div class="multishare-item-left">
          <input type="checkbox" ${isSelected ? 'checked' : ''} onclick="event.stopPropagation(); toggleMultiShareUser('${escapeHtml(emailLower)}')" style="width:17px;height:17px;cursor:pointer;accent-color:var(--emerald-h)">
          <div class="table-user-avatar" style="width:32px;height:32px;font-size:.8125rem">${initial}</div>
          <div style="min-width:0;display:flex;flex-direction:column;gap:.1rem">
            <div style="display:flex;align-items:center;gap:.35rem">
              <span style="font-weight:700;color:var(--ink);font-size:.84rem" class="truncate">${escapeHtml(u.name || 'User')}</span>
              <span class="badge ${u.is_admin ? 'badge-amber' : 'badge-teal'}" style="font-size:.65rem;padding:.1rem .35rem">${escapeHtml(u.role_label || 'Member')}</span>
            </div>
            <span style="font-size:.7rem;color:var(--ink-4)" class="truncate">${escapeHtml(u.email)}</span>
          </div>
        </div>
      </div>
    `;
  }).join('');

  updateMultiShareUserBadge();
}

function toggleMultiShareToken(id) {
  id = parseInt(id, 10);
  if (selectedMultiTokenIds.has(id)) {
    selectedMultiTokenIds.delete(id);
  } else {
    selectedMultiTokenIds.add(id);
  }
  const itemEl = document.getElementById(`multishare-token-item-${id}`);
  if (itemEl) {
    const isSel = selectedMultiTokenIds.has(id);
    itemEl.classList.toggle('selected', isSel);
    const chk = itemEl.querySelector('input[type="checkbox"]');
    if (chk) chk.checked = isSel;
  }
  updateMultiShareTokenBadge();
  updateMultiShareImpact();
}

function toggleMultiShareUser(email) {
  email = email.toLowerCase();
  if (selectedMultiUserEmails.has(email)) {
    selectedMultiUserEmails.delete(email);
  } else {
    selectedMultiUserEmails.add(email);
  }
  const itemEl = document.getElementById(`multishare-user-item-${email}`);
  if (itemEl) {
    const isSel = selectedMultiUserEmails.has(email);
    itemEl.classList.toggle('selected', isSel);
    const chk = itemEl.querySelector('input[type="checkbox"]');
    if (chk) chk.checked = isSel;
  }
  updateMultiShareUserBadge();
  updateMultiShareImpact();
}

function multiShareSelectAllTokens(check) {
  if (check) {
    multiShareTokensCache.forEach(t => selectedMultiTokenIds.add(t.id));
  } else {
    selectedMultiTokenIds.clear();
  }
  renderMultiShareTokensList(multiShareTokensCache);
  updateMultiShareImpact();
}

function multiShareSelectAllUsers(check) {
  if (check) {
    multiShareUsersCache.forEach(u => selectedMultiUserEmails.add(u.email.toLowerCase()));
  } else {
    selectedMultiUserEmails.clear();
  }
  renderMultiShareUsersList(multiShareUsersCache);
  updateMultiShareImpact();
}

function filterMultiShareTokens(val) {
  renderMultiShareTokensList(multiShareTokensCache);
}

function filterMultiShareUsers(val) {
  renderMultiShareUsersList(multiShareUsersCache);
}

function updateMultiShareTokenBadge() {
  const badge = document.getElementById('multishare-token-count-badge');
  if (badge) badge.textContent = `${selectedMultiTokenIds.size} Terpilih`;
}

function updateMultiShareUserBadge() {
  const badge = document.getElementById('multishare-user-count-badge');
  if (badge) badge.textContent = `${selectedMultiUserEmails.size} Terpilih`;
}

function onMultiShareActionChange(action) {
  multiShareAction = action;
  const labelGrant = document.getElementById('label-action-grant');
  const labelRevoke = document.getElementById('label-action-revoke');
  const permSelector = document.getElementById('multishare-permission-selector');
  const execBtn = document.getElementById('btn-exec-multishare');

  if (labelGrant) {
    labelGrant.classList.toggle('active', action === 'grant');
    labelGrant.classList.toggle('grant', action === 'grant');
  }
  if (labelRevoke) {
    labelRevoke.classList.toggle('active', action === 'revoke');
    labelRevoke.classList.toggle('revoke', action === 'revoke');
  }

  if (permSelector) {
    permSelector.style.display = action === 'grant' ? 'flex' : 'none';
  }

  if (execBtn) {
    if (action === 'revoke') {
      execBtn.style.background = 'var(--red)';
      execBtn.style.borderColor = 'var(--red)';
      execBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i> <span>Cabut Akses Massal</span>';
    } else {
      execBtn.style.background = 'var(--teal-800)';
      execBtn.style.borderColor = 'var(--teal-800)';
      execBtn.innerHTML = '<i class="fa-solid fa-bolt"></i> <span>Terapkan Sharing Massal</span>';
    }
  }

  updateMultiShareImpact();
}

function setMultiSharePerm(canEdit) {
  multiShareCanEdit = canEdit ? 1 : 0;
  document.getElementById('btn-perm-readonly')?.classList.toggle('active', !canEdit);
  document.getElementById('btn-perm-canedit')?.classList.toggle('active', !!canEdit);
  updateMultiShareImpact();
}

function updateMultiShareImpact() {
  const tCount = selectedMultiTokenIds.size;
  const uCount = selectedMultiUserEmails.size;
  const totalRelations = tCount * uCount;
  const impactEl = document.getElementById('multishare-impact-text');
  const execBtn = document.getElementById('btn-exec-multishare');

  if (!impactEl || !execBtn) return;

  if (tCount === 0 || uCount === 0) {
    impactEl.innerHTML = `
      <i class="fa-solid fa-circle-info" style="color:var(--teal-600)"></i>
      <span>Pilih minimal 1 token dan 1 akun penerima di atas untuk melanjutkan (${tCount} token, ${uCount} akun).</span>
    `;
    execBtn.disabled = true;
    return;
  }

  execBtn.disabled = false;

  if (multiShareAction === 'grant') {
    const permLabel = multiShareCanEdit ? 'Boleh Edit (Full Edit)' : 'Hanya Lihat (Read-Only)';
    impactEl.innerHTML = `
      <i class="fa-solid fa-circle-check" style="color:var(--emerald-h)"></i>
      <span>Akan membagikan <strong>${tCount} token</strong> ke <strong>${uCount} akun</strong> (total perkiraan <strong>${totalRelations} relasi</strong>). Izin: <span class="badge ${multiShareCanEdit ? 'badge-amber' : 'badge-teal'}" style="font-size:.7rem">${permLabel}</span></span>
    `;
  } else {
    impactEl.innerHTML = `
      <i class="fa-solid fa-triangle-exclamation" style="color:var(--red)"></i>
      <span>Akan mencabut hak akses <strong>${tCount} token</strong> dari <strong>${uCount} akun</strong> terpilih.</span>
    `;
  }
}

function resetMultiShareSelection() {
  selectedMultiTokenIds.clear();
  selectedMultiUserEmails.clear();
  const searchT = document.getElementById('multishare-token-search');
  const searchU = document.getElementById('multishare-user-search');
  if (searchT) searchT.value = '';
  if (searchU) searchU.value = '';
  renderMultiShareTokensList(multiShareTokensCache);
  renderMultiShareUsersList(multiShareUsersCache);
  updateMultiShareImpact();
}

async function executeBulkMultiShare() {
  const profileIds = Array.from(selectedMultiTokenIds);
  const emails = Array.from(selectedMultiUserEmails);

  if (profileIds.length === 0 || emails.length === 0) {
    toast('Pilih minimal satu token dan satu akun penerima.', 'error');
    return;
  }

  const actionText = multiShareAction === 'grant' 
    ? `Bagikan ${profileIds.length} token ke ${emails.length} akun terpilih (${multiShareCanEdit ? 'Boleh Edit' : 'Hanya Lihat'})?`
    : `Cabut akses ${profileIds.length} token dari ${emails.length} akun terpilih?`;

  if (!confirm(actionText)) return;

  const btn = document.getElementById('btn-exec-multishare');
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses…';

  try {
    const res = await fetch('/api/access/bulk-multi-share', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({
        profile_ids: profileIds,
        emails: emails,
        action: multiShareAction,
        can_edit: !!multiShareCanEdit
      })
    });

    const data = await res.json();
    if (!res.ok || data.error) {
      toast(data.error || 'Gagal memproses sharing massal.', 'error');
      btn.disabled = false;
      btn.innerHTML = originalHtml;
      return;
    }

    if (multiShareAction === 'grant') {
      let msg = `✓ Berhasil membagikan ${data.tokens_count} token ke ${data.users_count} akun!`;
      if (data.added || data.updated) {
        msg += ` (${data.added} baru, ${data.updated} diperbarui)`;
      }
      if (data.skipped_self) {
        msg += ` (${data.skipped_self} dilewati karena pemilik)`;
      }
      toast(msg, 'success');
    } else {
      toast(`✓ Berhasil mencabut ${data.removed} relasi akses token massal!`, 'success');
    }

    // Refresh metrics & reset selection
    loadAccessMetrics();
    resetMultiShareSelection();
  } catch (e) {
    toast('Terjadi kesalahan: ' + e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }
}

// ── Token Card Bulk Selection Mode on Tokens Grid & Table ──
let isTokensBulkMode = false;
let selectedTokensGridIds = new Set();

function toggleTokensBulkMode() {
  isTokensBulkMode = !isTokensBulkMode;
  const cards = document.querySelectorAll('.token-card');
  const rows = document.querySelectorAll('.token-row');
  const toggleBtn = document.getElementById('btn-toggle-bulk-tokens');
  const floatingBar = document.getElementById('tokens-grid-bulk-bar');

  cards.forEach(c => {
    c.classList.toggle('bulk-mode', isTokensBulkMode);
    if (!isTokensBulkMode) {
      c.classList.remove('bulk-selected');
      const chk = c.querySelector('.token-card-bulk-check');
      if (chk) chk.checked = false;
    }
  });

  rows.forEach(r => {
    if (!isTokensBulkMode) {
      r.style.background = '';
      const chk = r.querySelector('.token-table-bulk-check');
      if (chk) chk.checked = false;
    }
  });

  if (!isTokensBulkMode) {
    selectedTokensGridIds.clear();
    if (floatingBar) floatingBar.style.display = 'none';
    if (toggleBtn) {
      toggleBtn.classList.remove('btn-primary');
      toggleBtn.classList.add('btn-secondary');
      toggleBtn.innerHTML = '<i class="fa-solid fa-list-check"></i> <span class="bulk-lbl-desktop">Pilih Banyak Token</span><span class="bulk-lbl-mobile">Pilih</span>';
    }
    const thAll = document.getElementById('token-table-check-all');
    if (thAll) thAll.checked = false;
  } else {
    if (toggleBtn) {
      toggleBtn.classList.remove('btn-secondary');
      toggleBtn.classList.add('btn-primary');
      toggleBtn.innerHTML = '<i class="fa-solid fa-xmark"></i> <span class="bulk-lbl-desktop">Tutup Mode Pilihan</span><span class="bulk-lbl-mobile">Tutup</span>';
    }
    updateTokensGridBulkBar();
  }
}

function onTokenGridCheckChange(profileId, isChecked) {
  profileId = parseInt(profileId, 10);
  if (isChecked) {
    selectedTokensGridIds.add(profileId);
  } else {
    selectedTokensGridIds.delete(profileId);
  }
  const card = document.getElementById(`card-${profileId}`);
  if (card) card.classList.toggle('bulk-selected', isChecked);
  const tableChk = document.getElementById(`token-table-check-${profileId}`);
  if (tableChk) tableChk.checked = isChecked;
  const row = document.getElementById(`row-${profileId}`);
  if (row) row.style.background = isChecked ? 'rgba(16, 185, 129, 0.15)' : '';
  updateTokensGridBulkBar();
}

function onTokenTableCheckChange(profileId, isChecked) {
  profileId = parseInt(profileId, 10);
  const cardChk = document.getElementById(`token-check-${profileId}`);
  if (cardChk) cardChk.checked = isChecked;
  onTokenGridCheckChange(profileId, isChecked);
}

function bulkSelectAllTokensTable(check) {
  bulkSelectAllTokensGrid(check);
  document.querySelectorAll('.token-table-bulk-check').forEach(chk => {
    chk.checked = check;
  });
}

function bulkSelectAllTokensGrid(check) {
  const cards = document.querySelectorAll('.token-card');
  cards.forEach(c => {
    const pid = parseInt(c.dataset.id, 10);
    const chk = c.querySelector('.token-card-bulk-check');
    const tblChk = document.getElementById(`token-table-check-${pid}`);
    const row = document.getElementById(`row-${pid}`);
    if (check) {
      selectedTokensGridIds.add(pid);
      c.classList.add('bulk-selected');
      if (chk) chk.checked = true;
      if (tblChk) tblChk.checked = true;
      if (row) row.style.background = 'rgba(16, 185, 129, 0.15)';
    } else {
      selectedTokensGridIds.delete(pid);
      c.classList.remove('bulk-selected');
      if (chk) chk.checked = false;
      if (tblChk) tblChk.checked = false;
      if (row) row.style.background = '';
    }
  });
  const thAll = document.getElementById('token-table-check-all');
  if (thAll) thAll.checked = check;
  updateTokensGridBulkBar();
}

function updateTokensGridBulkBar() {
  const floatingBar = document.getElementById('tokens-grid-bulk-bar');
  const countEl = document.getElementById('tokens-grid-selected-count');
  const count = selectedTokensGridIds.size;
  if (!floatingBar) return;

  if (isTokensBulkMode && count > 0) {
    floatingBar.style.display = 'flex';
    if (countEl) countEl.textContent = count;
  } else {
    floatingBar.style.display = 'none';
  }
}

function proceedTokensGridToMultiShare() {
  if (selectedTokensGridIds.size === 0) {
    toast('Pilih minimal satu token.', 'error');
    return;
  }
  // Copy selected tokens into multi-share
  selectedMultiTokenIds = new Set(selectedTokensGridIds);

  // Exit bulk mode on grid
  toggleTokensBulkMode();

  // Switch view to access and subtab to multishare
  switchView('access');
  switchAccessSubTab('multishare');

  toast(`✓ ${selectedMultiTokenIds.size} token telah dipilih. Silakan pilih akun tujuan di kolom kanan.`, 'success');
}

// ── Drag & Drop Reordering & Persistent Custom Order ──
const CURRENT_USER_ID = <?= json_encode((int)($user['id'] ?? 0)) ?>;
const TOKEN_ORDER_KEY = 'totpvault_token_order_' + CURRENT_USER_ID;

let draggedTokenCard = null;
let draggedTokenRow = null;
let isDragOngoing = false;
let dragEndTimeout = null;

function initTokenDragAndDrop() {
  const grid = document.getElementById('profiles-grid');
  if (grid) {
    const cards = grid.querySelectorAll('.token-card');
    cards.forEach(card => setupCardDragEvents(card));
  }

  const tbody = document.querySelector('#profiles-table tbody');
  if (tbody) {
    const rows = tbody.querySelectorAll('.token-row');
    rows.forEach(row => setupRowDragEvents(row));
  }
}

function setupCardDragEvents(card) {
  const handle = card.querySelector('.token-drag-handle');
  if (!handle) return;

  function onCardDragStart(e) {
    if (e.type === 'mousedown' && e.button !== 0) return;
    if (e.type === 'pointerdown' && e.button !== 0) return;

    e.preventDefault();
    e.stopPropagation();
    if (window.getSelection) {
      window.getSelection().removeAllRanges();
    }

    draggedTokenCard = card;
    isDragOngoing = true;
    clearTimeout(dragEndTimeout);
    card.classList.add('is-dragging');
    document.body.classList.add('vault-dragging');

    function onCardMove(moveEvent) {
      if (!draggedTokenCard) return;
      const clientX = (moveEvent.touches && moveEvent.touches[0]) ? moveEvent.touches[0].clientX : moveEvent.clientX;
      const clientY = (moveEvent.touches && moveEvent.touches[0]) ? moveEvent.touches[0].clientY : moveEvent.clientY;
      if (typeof clientX !== 'number' || typeof clientY !== 'number') return;

      const grid = document.getElementById('profiles-grid');
      if (!grid) return;

      const cards = Array.from(grid.querySelectorAll('.token-card'));
      for (const targetCard of cards) {
        if (targetCard === draggedTokenCard) continue;
        const rect = targetCard.getBoundingClientRect();
        if (clientX >= rect.left && clientX <= rect.right && clientY >= rect.top && clientY <= rect.bottom) {
          const isAfter = (clientX - rect.left) / rect.width > 0.5 || (clientY - rect.top) / rect.height > 0.5;
          if (isAfter) {
            grid.insertBefore(draggedTokenCard, targetCard.nextSibling);
          } else {
            grid.insertBefore(draggedTokenCard, targetCard);
          }
          break;
        }
      }
    }

    function onCardEnd() {
      window.removeEventListener('pointermove', onCardMove);
      window.removeEventListener('pointerup', onCardEnd);
      window.removeEventListener('pointercancel', onCardEnd);
      window.removeEventListener('mousemove', onCardMove);
      window.removeEventListener('mouseup', onCardEnd);
      window.removeEventListener('touchmove', onCardMove);
      window.removeEventListener('touchend', onCardEnd);

      document.body.classList.remove('vault-dragging');

      if (draggedTokenCard) {
        draggedTokenCard.classList.remove('is-dragging');
        draggedTokenCard = null;
        saveOrderFromGrid();
        toast('✓ Urutan token disimpan', 'success');
      }
      clearTimeout(dragEndTimeout);
      dragEndTimeout = setTimeout(() => {
        isDragOngoing = false;
      }, 120);
    }

    window.addEventListener('pointermove', onCardMove, { passive: false });
    window.addEventListener('pointerup', onCardEnd, { passive: false });
    window.addEventListener('pointercancel', onCardEnd, { passive: false });
    window.addEventListener('mousemove', onCardMove);
    window.addEventListener('mouseup', onCardEnd);
    window.addEventListener('touchmove', onCardMove, { passive: false });
    window.addEventListener('touchend', onCardEnd);
  }

  handle.addEventListener('pointerdown', onCardDragStart);
  handle.addEventListener('mousedown', onCardDragStart);
  handle.addEventListener('touchstart', onCardDragStart, { passive: false });
}

function setupRowDragEvents(row) {
  const rowHandle = row.querySelector('.token-row-drag-handle');
  if (!rowHandle) return;

  function onRowDragStart(e) {
    if (e.type === 'mousedown' && e.button !== 0) return;
    if (e.type === 'pointerdown' && e.button !== 0) return;

    e.preventDefault();
    e.stopPropagation();
    if (window.getSelection) {
      window.getSelection().removeAllRanges();
    }

    draggedTokenRow = row;
    isDragOngoing = true;
    clearTimeout(dragEndTimeout);
    row.classList.add('is-dragging');
    document.body.classList.add('vault-dragging');

    function onRowMove(moveEvent) {
      if (!draggedTokenRow) return;
      const clientY = (moveEvent.touches && moveEvent.touches[0]) ? moveEvent.touches[0].clientY : moveEvent.clientY;
      if (typeof clientY !== 'number') return;

      const tbody = document.querySelector('#profiles-table tbody');
      if (!tbody) return;

      const rows = Array.from(tbody.querySelectorAll('.token-row'));
      for (const targetRow of rows) {
        if (targetRow === draggedTokenRow) continue;
        const rect = targetRow.getBoundingClientRect();
        if (clientY >= rect.top && clientY <= rect.bottom) {
          const isAfter = clientY > (rect.top + rect.height / 2);
          if (isAfter) {
            tbody.insertBefore(draggedTokenRow, targetRow.nextSibling);
          } else {
            tbody.insertBefore(draggedTokenRow, targetRow);
          }
          break;
        }
      }
    }

    function onRowEnd() {
      window.removeEventListener('pointermove', onRowMove);
      window.removeEventListener('pointerup', onRowEnd);
      window.removeEventListener('pointercancel', onRowEnd);
      window.removeEventListener('mousemove', onRowMove);
      window.removeEventListener('mouseup', onRowEnd);
      window.removeEventListener('touchmove', onRowMove);
      window.removeEventListener('touchend', onRowEnd);

      document.body.classList.remove('vault-dragging');

      if (draggedTokenRow) {
        draggedTokenRow.classList.remove('is-dragging');
        draggedTokenRow = null;
        saveOrderFromTable();
        toast('✓ Urutan token disimpan', 'success');
      }
      clearTimeout(dragEndTimeout);
      dragEndTimeout = setTimeout(() => {
        isDragOngoing = false;
      }, 120);
    }

    window.addEventListener('pointermove', onRowMove, { passive: false });
    window.addEventListener('pointerup', onRowEnd, { passive: false });
    window.addEventListener('pointercancel', onRowEnd, { passive: false });
    window.addEventListener('mousemove', onRowMove);
    window.addEventListener('mouseup', onRowEnd);
    window.addEventListener('touchmove', onRowMove, { passive: false });
    window.addEventListener('touchend', onRowEnd);
  }

  rowHandle.addEventListener('pointerdown', onRowDragStart);
  rowHandle.addEventListener('mousedown', onRowDragStart);
  rowHandle.addEventListener('touchstart', onRowDragStart, { passive: false });
}

function saveOrderFromGrid() {
  const grid = document.getElementById('profiles-grid');
  if (!grid) return;
  const cards = grid.querySelectorAll('.token-card');
  const order = Array.from(cards).map(c => parseInt(c.dataset.id)).filter(id => !isNaN(id));
  if (order.length > 0) {
    localStorage.setItem(TOKEN_ORDER_KEY, JSON.stringify(order));
    syncTableRowsToGridOrder();
    const resetBtn = document.getElementById('btn-reset-order');
    if (resetBtn) resetBtn.style.display = 'inline-flex';
  }
}

function saveOrderFromTable() {
  const tbody = document.querySelector('#profiles-table tbody');
  if (!tbody) return;
  const rows = tbody.querySelectorAll('.token-row');
  const order = Array.from(rows).map(r => parseInt(r.dataset.id)).filter(id => !isNaN(id));
  if (order.length > 0) {
    localStorage.setItem(TOKEN_ORDER_KEY, JSON.stringify(order));
    syncGridCardsToTableOrder();
    const resetBtn = document.getElementById('btn-reset-order');
    if (resetBtn) resetBtn.style.display = 'inline-flex';
  }
}

function saveCurrentTokenOrder() {
  saveOrderFromGrid();
}

function applySavedTokenOrder() {
  const saved = localStorage.getItem(TOKEN_ORDER_KEY);
  if (!saved) return;
  try {
    const order = JSON.parse(saved);
    if (!Array.isArray(order) || order.length === 0) return;

    const grid = document.getElementById('profiles-grid');
    if (grid) {
      const cardMap = {};
      grid.querySelectorAll('.token-card').forEach(c => {
        cardMap[c.dataset.id] = c;
      });
      order.forEach(id => {
        if (cardMap[id]) {
          grid.appendChild(cardMap[id]);
          delete cardMap[id];
        }
      });
      // Append any newly added tokens that were not yet in saved order
      Object.values(cardMap).forEach(c => grid.appendChild(c));
    }

    const tbody = document.querySelector('#profiles-table tbody');
    if (tbody) {
      const rowMap = {};
      tbody.querySelectorAll('.token-row').forEach(r => {
        rowMap[r.dataset.id] = r;
      });
      order.forEach(id => {
        if (rowMap[id]) {
          tbody.appendChild(rowMap[id]);
          delete rowMap[id];
        }
      });
      Object.values(rowMap).forEach(r => tbody.appendChild(r));
    }

    const resetBtn = document.getElementById('btn-reset-order');
    if (resetBtn) resetBtn.style.display = 'inline-flex';
  } catch (err) {
    console.error('Gagal menerapkan urutan token:', err);
  }
}

function resetTokenOrder() {
  localStorage.removeItem(TOKEN_ORDER_KEY);

  // Restore server default order: Mine (is_owner=1) first, then alphabetical by name
  const grid = document.getElementById('profiles-grid');
  if (grid) {
    const cards = Array.from(grid.querySelectorAll('.token-card'));
    cards.sort((a, b) => {
      const ownerA = a.dataset.owner === '1' ? 1 : 0;
      const ownerB = b.dataset.owner === '1' ? 1 : 0;
      if (ownerA !== ownerB) return ownerB - ownerA;
      return (a.dataset.name || '').localeCompare(b.dataset.name || '');
    });
    cards.forEach(c => grid.appendChild(c));
  }

  const tbody = document.querySelector('#profiles-table tbody');
  if (tbody) {
    const rows = Array.from(tbody.querySelectorAll('.token-row'));
    rows.sort((a, b) => {
      const ownerA = a.dataset.owner === '1' ? 1 : 0;
      const ownerB = b.dataset.owner === '1' ? 1 : 0;
      if (ownerA !== ownerB) return ownerB - ownerA;
      return (a.dataset.name || '').localeCompare(b.dataset.name || '');
    });
    rows.forEach(r => tbody.appendChild(r));
  }

  const resetBtn = document.getElementById('btn-reset-order');
  if (resetBtn) resetBtn.style.display = 'none';

  toast('✓ Urutan token dikembalikan ke default', 'success');
}

function syncTableRowsToGridOrder() {
  const grid = document.getElementById('profiles-grid');
  const tbody = document.querySelector('#profiles-table tbody');
  if (!grid || !tbody) return;
  const cards = grid.querySelectorAll('.token-card');
  const rowMap = {};
  tbody.querySelectorAll('.token-row').forEach(r => { rowMap[r.dataset.id] = r; });
  cards.forEach(c => {
    const id = c.dataset.id;
    if (rowMap[id]) tbody.appendChild(rowMap[id]);
  });
}

function syncGridCardsToTableOrder() {
  const grid = document.getElementById('profiles-grid');
  const tbody = document.querySelector('#profiles-table tbody');
  if (!grid || !tbody) return;
  const rows = tbody.querySelectorAll('.token-row');
  const cardMap = {};
  grid.querySelectorAll('.token-card').forEach(c => { cardMap[c.dataset.id] = c; });
  rows.forEach(r => {
    const id = r.dataset.id;
    if (cardMap[id]) grid.appendChild(cardMap[id]);
  });
}

// ── Initialize ──
initColorGrid('#0b332d');
initAllCards();
applySavedTokenOrder();
initTokenDragAndDrop();
const urlViewMode = new URLSearchParams(window.location.search).get('view');
const savedViewMode = (window.innerWidth <= 768)
  ? 'grid'
  : ((urlViewMode === 'table' || urlViewMode === 'grid') ? urlViewMode : (localStorage.getItem('totp_view_mode') || 'grid'));
setTokensViewMode(savedViewMode);
window.addEventListener('resize', () => {
  if (window.innerWidth <= 768) {
    setTokensViewMode('grid');
  }
});
if (IS_ADMIN) {
  loadWhitelistData();
  loadAccessMetrics();
}
if (activeView === 'access' && IS_ADMIN) {
  switchView('access');
} else if (activeView === 'whitelist' && IS_ADMIN) {
  switchView('whitelist');
}
</script>

</body>
</html>
