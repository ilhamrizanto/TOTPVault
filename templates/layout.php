<?php
// templates/layout.php — Apple-Grade Precision Design System for TOTPVault
$appName = 'TOTPVault';
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#09090b">
<title><?= htmlspecialchars($pageTitle ?? 'TOTPVault — Brankas 2FA Terpusat & Kriptografi Server-Side') ?></title>
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="apple-touch-icon" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root {
  /* ── Apple macOS Sequoia / iOS 18 Obsidian & Graphite Materials ── */
  --canvas:          #09090b; /* Base deep neutral obsidian */
  --canvas-sub:      #0d0d10; /* Recessed groove / table well */
  --surface-sidebar: rgba(14, 14, 18, 0.84); /* Vibrancy sidebar */

  --card:            #121216; /* Primary elevated surface */
  --card-sub:        #17171c; /* Secondary elevated surface */
  --card-hover:      #1c1c22; /* Interactive hover surface */
  --card-active:     #222229; /* Pressed / selected surface */

  /* ── Precision Hairline Borders & Specular Highlights ── */
  --border:          rgba(255, 255, 255, 0.075);
  --border-sub:      rgba(255, 255, 255, 0.045);
  --border-hover:    rgba(255, 255, 255, 0.14);
  --border-focus:    rgba(16, 185, 129, 0.55);
  --border-glow:     rgba(16, 185, 129, 0.35);
  --specular-top:    inset 0 1px 0 0 rgba(255, 255, 255, 0.07);
  --specular-card:   inset 0 1px 0 0 rgba(255, 255, 255, 0.055), 0 1px 2px rgba(0, 0, 0, 0.45);

  /* ── Optical Ink Hierarchy ── */
  --ink:             #f4f4f6; /* Primary crisp label */
  --ink-2:           #d4d4d8; /* Secondary label */
  --ink-3:           #8e8e98; /* Tertiary / subtitle label */
  --ink-4:           #5b5b66; /* Quaternary / metadata */
  --ink-5:           #2c2c34; /* Hairline structural divider */

  /* ── Functional Semantic Accents (Restrained, High-Craft) ── */
  --emerald:         #10b981;
  --emerald-h:       #059669;
  --emerald-light:   #34d399;
  --emerald-l:       rgba(16, 185, 129, 0.11);
  --emerald-glow:    0 1px 2px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.22);
  --emerald-border:  rgba(16, 185, 129, 0.28);

  /* Legacy compatibility aliases */
  --teal-900:        #111115;
  --teal-800:        #0c0c0f;
  --teal-700:        #141419;
  --teal-600:        #1b1b22;
  --teal-pill:       rgba(255, 255, 255, 0.05);
  --teal-pill-active:rgba(16, 185, 129, 0.14);
  --teal-text:       #8e8e98;
  --teal-text-hover: #f4f4f6;

  --cyan:            #06b6d4;
  --cyan-l:          rgba(6, 182, 212, 0.11);
  --indigo:          #6366f1;
  --indigo-l:        rgba(99, 102, 241, 0.11);

  --red:             #ef4444;
  --red-light:       #f87171;
  --red-l:           rgba(239, 68, 68, 0.11);
  --red-border:      rgba(239, 68, 68, 0.26);
  --amber:           #f59e0b;
  --amber-light:     #fbbf24;
  --amber-l:         rgba(245, 158, 11, 0.11);
  --amber-border:    rgba(245, 158, 11, 0.26);
  --green:           #10b981;

  /* ── Concentric Radii (Outer = Inner + Padding) ── */
  --r-pill:          999px;
  --r-xl:            20px;
  --r-card:          14px;
  --r-inner:         10px;
  --r-sm:            8px;
  --r-xs:            6px;

  /* ── Physical Elevation Shadows ── */
  --shadow-sm:       0 1px 2px rgba(0, 0, 0, 0.45);
  --shadow-md:       0 8px 24px -4px rgba(0, 0, 0, 0.55), 0 2px 6px rgba(0, 0, 0, 0.35);
  --shadow-lg:       0 24px 48px -12px rgba(0, 0, 0, 0.78), 0 4px 16px rgba(0, 0, 0, 0.45);
  --shadow-glow:     0 1px 3px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2);
  --shadow-glow-red: 0 1px 3px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.15);

  /* ── Apple Fluid Motion Curves ── */
  --ease-out:        cubic-bezier(0.23, 1, 0.32, 1);
  --ease-in-out:     cubic-bezier(0.77, 0, 0.175, 1);
  --ease-drawer:     cubic-bezier(0.32, 0.72, 0, 1);
  --ease-spring:     cubic-bezier(0.22, 1.12, 0.36, 1);
}

html {
  font-size: 16px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
  background: var(--canvas);
  color-scheme: dark;
  margin: 0;
  padding: 0;
  width: 100%;
  max-width: 100%;
  overflow-x: hidden !important;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Plus Jakarta Sans', sans-serif;
  background: var(--canvas);
  color: var(--ink);
  line-height: 1.5;
  letter-spacing: -0.011em;
  min-height: 100vh;
  min-height: 100dvh;
  width: 100%;
  max-width: 100%;
  overflow-x: hidden !important;
  margin: 0;
  padding: 0;
  -webkit-tap-highlight-color: transparent;
}

h1, h2, h3, h4, .display-font {
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Inter', sans-serif;
  letter-spacing: -0.025em;
  text-wrap: balance;
}

p {
  text-wrap: pretty;
}

a { color: inherit; text-decoration: none; }
button, input, select, textarea { font-family: inherit; }
code, pre, .mono {
  font-family: 'JetBrains Mono', 'SF Mono', ui-monospace, monospace;
  font-variant-numeric: tabular-nums;
}

/* ── Typography & Utility Classes ── */
.text-xs { font-size: .75rem; }
.text-sm { font-size: .875rem; }
.text-base { font-size: 1rem; }
.text-lg { font-size: 1.125rem; }
.text-xl { font-size: 1.25rem; }
.text-2xl { font-size: 1.5rem; }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }
.font-extrabold { font-weight: 800; }
.tabular-nums { font-variant-numeric: tabular-nums; }

.hidden { display: none !important; }
.truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Bespoke Buttons (Tactile & Specular) ── */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .45rem;
  height: 36px;
  padding: 0 .95rem;
  border-radius: var(--r-sm);
  font-size: .8125rem;
  font-weight: 600;
  letter-spacing: -0.01em;
  cursor: pointer;
  border: 1px solid transparent;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
  white-space: nowrap;
  transition:
    transform 150ms var(--ease-out),
    background-color 150ms ease,
    border-color 150ms ease,
    color 150ms ease,
    box-shadow 150ms ease,
    opacity 150ms ease;
}

.btn:active:not(:disabled) {
  transform: scale(0.97);
}

.btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--emerald);
  color: #04130e;
  font-weight: 650;
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.25);
}

@media (hover: hover) and (pointer: fine) {
  .btn-primary:hover:not(:disabled) {
    background: #18c98f;
  }
}

.btn-secondary, .btn-dark {
  background: #17171d;
  color: var(--ink);
  border-color: var(--border);
  box-shadow: var(--specular-card);
}

@media (hover: hover) and (pointer: fine) {
  .btn-secondary:hover:not(:disabled), .btn-dark:hover:not(:disabled) {
    background: #202028;
    border-color: var(--border-hover);
    color: #ffffff;
  }
}

.btn-danger {
  background: rgba(239, 68, 68, 0.12);
  color: #f87171;
  border-color: rgba(239, 68, 68, 0.28);
}

@media (hover: hover) and (pointer: fine) {
  .btn-danger:hover:not(:disabled) {
    background: #ef4444;
    color: #ffffff;
    border-color: #ef4444;
  }
}

.btn-ghost {
  background: transparent;
  color: var(--ink-3);
  border-color: transparent;
}

@media (hover: hover) and (pointer: fine) {
  .btn-ghost:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.055);
    color: var(--ink);
  }
}

.btn-sm {
  height: 30px;
  padding: 0 .7rem;
  font-size: .75rem;
  border-radius: 7px;
}

.btn-lg {
  height: 42px;
  padding: 0 1.25rem;
  font-size: .875rem;
  border-radius: 10px;
}

.btn-icon {
  width: 34px;
  height: 34px;
  padding: 0;
  border-radius: var(--r-sm);
}

/* ── Custom Inputs & Form Controls ── */
.field {
  display: flex;
  flex-direction: column;
  gap: .375rem;
}

.label {
  font-size: .75rem;
  font-weight: 600;
  color: var(--ink-2);
  letter-spacing: -0.005em;
}

.input, select.input, textarea.input {
  width: 100%;
  min-height: 38px;
  padding: .5rem .8rem;
  border: 1px solid var(--border);
  border-radius: var(--r-sm);
  font-size: .84375rem;
  color: var(--ink);
  background: #0d0d11;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.35);
  outline: none;
  transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
}

.input::placeholder {
  color: var(--ink-4);
}

.input:focus {
  background: #101015;
  border-color: var(--emerald);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.14);
}

select.input {
  appearance: none;
  -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238e8e98' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right .75rem center;
  padding-right: 2.1rem;
}

.hint {
  font-size: .72rem;
  color: var(--ink-3);
  line-height: 1.4;
}

/* ── Custom Apple Toggle Switch ── */
.apple-switch {
  position: relative;
  display: inline-flex;
  align-items: center;
  width: 38px;
  height: 22px;
  flex-shrink: 0;
  cursor: pointer;
}
.apple-switch input {
  opacity: 0;
  width: 0;
  height: 0;
  position: absolute;
}
.apple-switch-track {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  transition: background-color 180ms var(--ease-out), border-color 180ms var(--ease-out);
}
.apple-switch-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 16px;
  height: 16px;
  background: #ffffff;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.45);
  transition: transform 180ms var(--ease-out);
}
.apple-switch input:checked + .apple-switch-track {
  background: var(--emerald);
  border-color: var(--emerald);
}
.apple-switch input:checked + .apple-switch-track .apple-switch-thumb {
  transform: translateX(16px);
}
.apple-switch input:focus-visible + .apple-switch-track {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
}

/* ── Badges & Pills (Restrained) ── */
.badge {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .15rem .525rem;
  border-radius: var(--r-xs);
  font-size: .6875rem;
  font-weight: 600;
  letter-spacing: 0.01em;
  line-height: 1.35;
}
.badge-green  { background: var(--emerald-l); color: var(--emerald-light); border: 1px solid var(--emerald-border); }
.badge-teal   { background: var(--cyan-l);    color: #38bdf8;              border: 1px solid rgba(6, 182, 212, 0.24); }
.badge-amber  { background: var(--amber-l);   color: var(--amber-light);   border: 1px solid var(--amber-border); }
.badge-red    { background: var(--red-l);     color: var(--red-light);     border: 1px solid var(--red-border); }
.badge-neutral{ background: rgba(255, 255, 255, 0.05); color: var(--ink-2); border: 1px solid var(--border); }

/* ── Apple Sheet / Dialog Modals ── */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.68);
  backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  animation: fadeIn 160ms var(--ease-out);
}

.modal {
  background: #131318;
  border-radius: var(--r-xl);
  box-shadow: var(--shadow-lg), inset 0 1px 0 rgba(255, 255, 255, 0.08);
  width: 100%;
  max-width: 520px;
  border: 1px solid rgba(255, 255, 255, 0.09);
  animation: modalEnter 200ms var(--ease-out);
  overflow: hidden;
}

.modal-header {
  padding: 1.25rem 1.5rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  border-bottom: 1px solid var(--border-sub);
  background: rgba(255, 255, 255, 0.01);
}

.modal-body {
  padding: 1.25rem 1.5rem;
  background: transparent;
}

.modal-footer {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: .625rem;
  border-top: 1px solid var(--border-sub);
  background: rgba(0, 0, 0, 0.2);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}

@keyframes modalEnter {
  from { opacity: 0; transform: translateY(8px) scale(0.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(8px) scale(0.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ── Custom Confirmation Sheet (`appleConfirm`) ── */
#apple-confirm-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(14px) saturate(180%);
  -webkit-backdrop-filter: blur(14px) saturate(180%);
  z-index: 2500;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  animation: fadeIn 150ms var(--ease-out);
}
#apple-confirm-backdrop.open {
  display: flex;
}
.apple-confirm-sheet {
  width: 100%;
  max-width: 380px;
  background: rgba(22, 22, 28, 0.96);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 18px;
  box-shadow: var(--shadow-lg), inset 0 1px 0 rgba(255, 255, 255, 0.09);
  padding: 1.35rem 1.35rem 1.15rem;
  text-align: center;
  animation: modalEnter 180ms var(--ease-out);
}
.apple-confirm-icon {
  width: 42px;
  height: 42px;
  border-radius: 11px;
  margin: 0 auto .875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}
.apple-confirm-icon.danger {
  background: rgba(239, 68, 68, 0.12);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.25);
}
.apple-confirm-icon.info {
  background: rgba(16, 185, 129, 0.12);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
}
.apple-confirm-title {
  font-size: .96875rem;
  font-weight: 700;
  color: var(--ink);
  letter-spacing: -0.02em;
  margin-bottom: .375rem;
}
.apple-confirm-msg {
  font-size: .8125rem;
  color: var(--ink-3);
  line-height: 1.5;
  margin-bottom: 1.25rem;
}
.apple-confirm-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .5rem;
}

/* ── Custom Dark Scrollbar ── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.12); border-radius: 999px; }
::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.22); }

/* ── Sonner-Inspired HUD Toast Notifications ── */
#toast-container {
  position: fixed;
  bottom: 1.25rem;
  right: 1.25rem;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: .5rem;
  pointer-events: none;
}

.toast {
  display: inline-flex;
  align-items: center;
  gap: .625rem;
  padding: .625rem .95rem;
  background: rgba(22, 22, 28, 0.92);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 12px 32px -4px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.08);
  color: var(--ink);
  border-radius: 11px;
  font-size: .8125rem;
  font-weight: 500;
  letter-spacing: -0.01em;
  pointer-events: auto;
  animation: toastIn 200ms var(--ease-out);
}

.toast-icon {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: .6875rem;
  font-weight: 700;
  flex-shrink: 0;
}

.toast.success .toast-icon {
  background: rgba(16, 185, 129, 0.16);
  color: var(--emerald-light);
}
.toast.error .toast-icon {
  background: rgba(239, 68, 68, 0.16);
  color: var(--red-light);
}
.toast.info .toast-icon {
  background: rgba(255, 255, 255, 0.1);
  color: var(--ink-2);
}

@keyframes toastIn {
  from { opacity: 0; transform: translateY(10px) scale(0.96); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes toastOut {
  to   { opacity: 0; transform: translateY(6px) scale(0.96); }
}

@media (max-width: 768px) {
  #toast-container {
    left: 1rem;
    right: 1rem;
    bottom: calc(4.5rem + env(safe-area-inset-bottom, 0px));
    align-items: center;
  }
  .toast {
    max-width: 100%;
    justify-content: center;
  }
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
</head>
<body>
<div id="toast-container" aria-live="polite"></div>

<!-- Global Apple-Style Confirmation Dialog -->
<div id="apple-confirm-backdrop" role="dialog" aria-modal="true" aria-labelledby="apple-confirm-title">
  <div class="apple-confirm-sheet">
    <div id="apple-confirm-icon" class="apple-confirm-icon danger">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <div id="apple-confirm-title" class="apple-confirm-title">Konfirmasi Tindakan</div>
    <div id="apple-confirm-msg" class="apple-confirm-msg">Apakah Anda yakin ingin melanjutkan?</div>
    <div class="apple-confirm-actions">
      <button type="button" class="btn btn-secondary" id="apple-confirm-cancel">Batal</button>
      <button type="button" class="btn btn-danger" id="apple-confirm-ok">Lanjutkan</button>
    </div>
  </div>
</div>

<script>
window.appleConfirm = function({ title = 'Konfirmasi', message = 'Apakah Anda yakin?', confirmText = 'Lanjutkan', cancelText = 'Batal', danger = true } = {}) {
  return new Promise((resolve) => {
    const backdrop = document.getElementById('apple-confirm-backdrop');
    const titleEl  = document.getElementById('apple-confirm-title');
    const msgEl    = document.getElementById('apple-confirm-msg');
    const iconEl   = document.getElementById('apple-confirm-icon');
    const okBtn    = document.getElementById('apple-confirm-ok');
    const cancelBtn= document.getElementById('apple-confirm-cancel');

    if (!backdrop) {
      resolve(window.confirm(message));
      return;
    }

    titleEl.textContent = title;
    msgEl.textContent = message;
    okBtn.textContent = confirmText;
    cancelBtn.textContent = cancelText;

    if (danger) {
      iconEl.className = 'apple-confirm-icon danger';
      iconEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
      okBtn.className = 'btn btn-danger';
    } else {
      iconEl.className = 'apple-confirm-icon info';
      iconEl.innerHTML = '<i class="fa-solid fa-shield-halved"></i>';
      okBtn.className = 'btn btn-primary';
    }

    const cleanup = (result) => {
      backdrop.classList.remove('open');
      okBtn.removeEventListener('click', onOk);
      cancelBtn.removeEventListener('click', onCancel);
      backdrop.removeEventListener('click', onBackdrop);
      document.removeEventListener('keydown', onKey);
      resolve(result);
    };

    const onOk = () => cleanup(true);
    const onCancel = () => cleanup(false);
    const onBackdrop = (e) => { if (e.target === backdrop) cleanup(false); };
    const onKey = (e) => {
      if (e.key === 'Escape') cleanup(false);
      if (e.key === 'Enter') { e.preventDefault(); cleanup(true); }
    };

    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);
    backdrop.addEventListener('click', onBackdrop);
    document.addEventListener('keydown', onKey);

    backdrop.classList.add('open');
    okBtn.focus();
  });
};
</script>
