<?php
// templates/layout.php — Aeux-inspired Executive Dashboard Layout
$appName = 'TOTPVault';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'TOTPVault — Brankas 2FA Terpusat & Kriptografi Server-Side') ?></title>
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="apple-touch-icon" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root {
  /* ── Pitch-Black / Deep Obsidian Startup Palette ── */
  --canvas:        #050508; /* Pure deep dark space */
  --canvas-sub:    #090a0f;
  
  --card:          #0c0d12; /* Elevated card surface */
  --card-sub:      #12131a; /* Secondary elevated container */
  --card-hover:    #161722;
  
  --border:        rgba(255, 255, 255, 0.08); /* Razor sharp micro border */
  --border-sub:    rgba(255, 255, 255, 0.05);
  --border-hover:  rgba(255, 255, 255, 0.18);
  --border-glow:   rgba(16, 185, 129, 0.4);

  --ink:           #f8fafc; /* Primary crisp white text */
  --ink-2:         #cbd5e1; /* Secondary slate */
  --ink-3:         #94a3b8; /* Muted slate */
  --ink-4:         #64748b; /* Low contrast */
  --ink-5:         #334155; /* Subtle dividers */

  /* Cyber Accents */
  --emerald:       #10b981; /* High contrast cyber emerald */
  --emerald-h:     #059669;
  --emerald-l:     rgba(16, 185, 129, 0.12);
  --emerald-glow:  0 0 20px rgba(16, 185, 129, 0.3);
  --emerald-border:rgba(16, 185, 129, 0.35);

  --teal-900:      #061a16;
  --teal-800:      #08090d; /* Pitch dark sidebar */
  --teal-700:      #0e1017;
  --teal-600:      #13151f;
  --teal-pill:     rgba(255, 255, 255, 0.05);
  --teal-pill-active: rgba(16, 185, 129, 0.16);
  --teal-text:     #94a3b8;
  --teal-text-hover: #f8fafc;

  --cyan:          #06b6d4;
  --cyan-l:        rgba(6, 182, 212, 0.12);
  --indigo:        #6366f1;
  --indigo-l:      rgba(99, 102, 241, 0.12);

  --red:           #f43f5e;
  --red-l:         rgba(244, 63, 94, 0.14);
  --red-border:    rgba(244, 63, 94, 0.3);
  --amber:         #f59e0b;
  --amber-l:       rgba(245, 158, 11, 0.14);
  --amber-border:  rgba(245, 158, 11, 0.3);

  --r-pill:        999px;
  --r-card:        16px;
  --r-inner:       12px;
  --r-sm:          8px;

  --shadow-sm:     0 2px 6px rgba(0, 0, 0, 0.4);
  --shadow-md:     0 8px 24px -4px rgba(0, 0, 0, 0.6);
  --shadow-lg:     0 16px 40px -8px rgba(0, 0, 0, 0.85);
  --shadow-glow:   0 0 24px rgba(16, 185, 129, 0.28);
  --shadow-glow-red: 0 0 20px rgba(244, 63, 94, 0.35);
}

html {
  font-size: 16px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  background: var(--canvas);
  margin: 0;
  padding: 0;
  width: 100%;
  max-width: 100%;
  overflow-x: hidden !important;
}

body {
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
  background: var(--canvas);
  color: var(--ink);
  line-height: 1.5;
  letter-spacing: -0.015em;
  min-height: 100vh;
  width: 100%;
  max-width: 100%;
  overflow-x: hidden !important;
  margin: 0;
  padding: 0;
}

a { color: inherit; text-decoration: none; }
button, input, select, textarea { font-family: inherit; }
code, pre, .mono { font-family: 'JetBrains Mono', monospace; }

/* ── Typography & Utils ── */
.text-xs { font-size: .75rem; }
.text-sm { font-size: .875rem; }
.text-base { font-size: 1rem; }
.text-lg { font-size: 1.125rem; }
.text-xl { font-size: 1.25rem; }
.text-2xl { font-size: 1.5rem; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }
.font-extrabold { font-weight: 800; }

.hidden { display: none !important; }
.truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Buttons ── */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  padding: .5625rem 1.125rem;
  border-radius: var(--r-pill);
  font-size: .875rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all .18s cubic-bezier(0.16, 1, 0.3, 1);
  white-space: nowrap;
}

.btn:active { transform: scale(0.97); }

.btn-dark {
  background: #13141d;
  color: #f8fafc;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);
}
.btn-dark:hover {
  background: #1b1c28;
  border-color: rgba(255, 255, 255, 0.2);
  color: #ffffff;
  transform: translateY(-1px);
}

.btn-primary {
  background: var(--emerald);
  color: #050508;
  font-weight: 700;
  box-shadow: var(--shadow-glow);
}
.btn-primary:hover {
  background: #34d399;
  box-shadow: 0 0 26px rgba(16, 185, 129, 0.45);
  transform: translateY(-1px);
}

.btn-secondary {
  background: #0d0e14;
  color: var(--ink-2);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}
.btn-secondary:hover {
  background: #141620;
  color: #ffffff;
  border-color: var(--border-hover);
  transform: translateY(-1px);
}

.btn-ghost {
  background: transparent;
  color: var(--ink-3);
}
.btn-ghost:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #ffffff;
}

.btn-sm {
  padding: .375rem .75rem;
  font-size: .8125rem;
}
.btn-icon {
  width: 36px;
  height: 36px;
  padding: 0;
  border-radius: var(--r-pill);
}

/* ── Inputs & Fields ── */
.field { display: flex; flex-direction: column; gap: .375rem; }
.label { font-size: .8125rem; font-weight: 600; color: var(--ink-2); }
.input {
  width: 100%;
  padding: .5625rem .875rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  font-size: .875rem;
  color: var(--ink);
  background: #090a0f;
  outline: none;
  transition: all .15s ease;
}
.input:focus {
  border-color: var(--emerald);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}
.hint { font-size: .75rem; color: var(--ink-4); }

/* ── Badges ── */
.badge {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .2rem .625rem;
  border-radius: var(--r-pill);
  font-size: .75rem;
  font-weight: 600;
}
.badge-green { background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25); }
.badge-teal  { background: rgba(6, 182, 212, 0.12); color: #38bdf8; border: 1px solid rgba(6, 182, 212, 0.25); }
.badge-amber { background: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.25); }

/* ── Modal ── */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.82);
  backdrop-filter: blur(12px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  animation: fadeIn .18s ease;
}
.modal {
  background: #0c0d13;
  border-radius: 20px;
  box-shadow: 0 30px 80px rgba(0, 0, 0, 0.95);
  width: 100%;
  max-width: 540px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  animation: slideUp .22s cubic-bezier(0.16, 1, 0.3, 1);
  overflow: hidden;
}
.modal-header {
  padding: 1.5rem 1.75rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  border-bottom: 1px solid var(--border-sub);
  background: #08090e;
}
.modal-body { padding: 1.5rem 1.75rem; background: #0c0d13; }
.modal-footer {
  padding: 1.25rem 1.75rem;
  display: flex;
  justify-content: flex-end;
  gap: .75rem;
  border-top: 1px solid var(--border-sub);
  background: #08090e;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(12px) scale(0.98); } to { opacity: 1; transform: none; } }

/* ── Custom Dark Scrollbar ── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: #050508; }
::-webkit-scrollbar-thumb { background: #1c1e28; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #2f3445; }

/* ── Toast Notifications ── */
#toast-container {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: .75rem;
  pointer-events: none;
}
.toast {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .875rem 1.25rem;
  background: var(--teal-900);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #ffffff;
  border-radius: var(--r-pill);
  box-shadow: var(--shadow-lg);
  font-size: .875rem;
  font-weight: 500;
  pointer-events: auto;
  animation: toastIn .22s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast.success .toast-icon { color: var(--emerald); }
.toast.error   .toast-icon { color: #f87171; }
@keyframes toastIn { from { opacity: 0; transform: translateY(1rem) scale(0.96); } to { opacity: 1; transform: none; } }
@keyframes toastOut { to { opacity: 0; transform: translateY(1rem) scale(0.96); } }

@media (max-width: 768px) {
  #toast-container {
    left: 1rem;
    right: 1rem;
    bottom: 1.25rem;
    align-items: center;
  }
  .toast {
    max-width: 100%;
    width: 100%;
    justify-content: center;
    text-align: center;
  }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
</head>
<body>
<div id="toast-container"></div>
