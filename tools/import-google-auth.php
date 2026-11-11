<?php
// tools/import-google-auth.php — Google Authenticator migration QR importer
// Included by index.php after auth check — $user, csrf_token(), verify_csrf(),
// jsonResponse(), BASE_DIR and all src classes are already available.

// ── Protobuf decoder ────────────────────────────────────────────────────────
// Decodes the Google Authenticator migration payload.
// The proto schema (MigrationPayload) is fixed and publicly documented.

function proto_read_varint(string $data, int &$pos): int {
    $result = 0;
    $shift  = 0;
    do {
        if ($pos >= strlen($data)) throw new RuntimeException('Unexpected end of protobuf data');
        $byte    = ord($data[$pos++]);
        $result |= ($byte & 0x7F) << $shift;
        $shift  += 7;
    } while ($byte & 0x80);
    return $result;
}

function proto_read_bytes(string $data, int &$pos): string {
    $len = proto_read_varint($data, $pos);
    $out = substr($data, $pos, $len);
    $pos += $len;
    return $out;
}

/**
 * Decode a Google Authenticator otpauth-migration://offline?data=... payload.
 * Returns an array of accounts, each with: name, issuer, secret (Base32), algorithm, digits, type.
 */
function decode_ga_migration(string $base64Data): array {
    // The data param is URL-encoded base64
    $raw = base64_decode($base64Data);
    if ($raw === false) throw new RuntimeException('Invalid base64 data in migration URI.');

    $accounts = [];
    $pos      = 0;
    $len      = strlen($raw);

    while ($pos < $len) {
        $tag       = proto_read_varint($raw, $pos);
        $fieldNum  = $tag >> 3;
        $wireType  = $tag & 0x07;

        // Field 1 = repeated OtpParameters (length-delimited)
        if ($fieldNum === 1 && $wireType === 2) {
            $paramBytes = proto_read_bytes($raw, $pos);
            $accounts[] = decode_otp_parameters($paramBytes);
        } else {
            // Skip other fields (version, batch_size, batch_index, batch_id)
            switch ($wireType) {
                case 0: proto_read_varint($raw, $pos); break;        // varint
                case 2: proto_read_bytes($raw, $pos);  break;        // length-delimited
                case 5: $pos += 4; break;                            // 32-bit
                case 1: $pos += 8; break;                            // 64-bit
                default: throw new RuntimeException("Unknown wire type {$wireType}");
            }
        }
    }

    return $accounts;
}

function decode_otp_parameters(string $data): array {
    $pos    = 0;
    $len    = strlen($data);
    $result = [
        'secret'    => '',
        'name'      => '',
        'issuer'    => '',
        'algorithm' => 'SHA1',
        'digits'    => 6,
        'type'      => 'totp',
    ];

    // Algorithm map: 0=unspecified(SHA1), 1=SHA1, 2=SHA256, 3=SHA512, 4=MD5
    $algoMap   = [0 => 'SHA1', 1 => 'SHA1', 2 => 'SHA256', 3 => 'SHA512', 4 => 'SHA1'];
    // Digits map: 0=unspecified(6), 1=6, 2=8
    $digitsMap = [0 => 6, 1 => 6, 2 => 8];
    // Type map: 0=unspecified, 1=HOTP, 2=TOTP
    $typeMap   = [0 => 'totp', 1 => 'hotp', 2 => 'totp'];

    while ($pos < $len) {
        $tag      = proto_read_varint($data, $pos);
        $fieldNum = $tag >> 3;
        $wireType = $tag & 0x07;

        switch ($fieldNum) {
            case 1: // secret (bytes → Base32)
                $secretBytes       = proto_read_bytes($data, $pos);
                $result['secret']  = TOTP::base32Encode($secretBytes);
                break;
            case 2: // name / label
                $result['name']    = proto_read_bytes($data, $pos);
                break;
            case 3: // issuer
                $result['issuer']  = proto_read_bytes($data, $pos);
                break;
            case 4: // algorithm
                $v = proto_read_varint($data, $pos);
                $result['algorithm'] = $algoMap[$v] ?? 'SHA1';
                break;
            case 5: // digits
                $v = proto_read_varint($data, $pos);
                $result['digits'] = $digitsMap[$v] ?? 6;
                break;
            case 6: // type
                $v = proto_read_varint($data, $pos);
                $result['type'] = $typeMap[$v] ?? 'totp';
                break;
            case 7: // counter (HOTP only — skip)
                proto_read_varint($data, $pos);
                break;
            default:
                // Skip unknown fields
                switch ($wireType) {
                    case 0: proto_read_varint($data, $pos); break;
                    case 2: proto_read_bytes($data, $pos);  break;
                    case 5: $pos += 4; break;
                    case 1: $pos += 8; break;
                }
        }
    }

    // If the label contains "Issuer:Account", split it
    if (empty($result['issuer']) && str_contains($result['name'], ':')) {
        [$result['issuer'], $result['name']] = explode(':', $result['name'], 2);
    }
    $result['name']   = trim($result['name']);
    $result['issuer'] = trim($result['issuer']);

    return $result;
}

// ── API endpoint: decode migration data ────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    if (empty($input['data'])) {
        jsonResponse(['error' => 'Missing data parameter'], 400);
    }

    try {
        $accounts = decode_ga_migration($input['data']);
        if (empty($accounts)) {
            jsonResponse(['error' => 'No accounts found in the migration QR code.'], 400);
        }
        jsonResponse(['accounts' => $accounts]);
    } catch (RuntimeException $e) {
        jsonResponse(['error' => 'Failed to decode: ' . $e->getMessage()], 400);
    }
}

// ── Page ───────────────────────────────────────────────────────────────────
$pageTitle = 'Impor Google Authenticator — TOTPVault';
$csrfToken = csrf_token();
$avatar    = $user['avatar_url'] ? htmlspecialchars($user['avatar_url']) : null;
$initial   = strtoupper(substr($user['name'] ?? $user['email'], 0, 1));

require BASE_DIR . '/templates/layout.php';
?>

<style>
/* ── Apple-Design Import Workspace ── */
.app-shell {
  display: flex;
  width: 100%;
  min-height: 100vh;
  min-height: 100dvh;
  background: var(--canvas);
  position: relative;
}

/* ── Left Vibrancy Sidebar (244px) ── */
.sidebar {
  width: 244px;
  flex-shrink: 0;
  background: var(--surface-sidebar);
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
  border-right: 1px solid var(--border);
  height: 100vh;
  height: 100dvh;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 1.15rem .875rem;
  z-index: 150;
  overflow-y: auto;
}

.sidebar-top {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .2rem .35rem;
}

.brand-identity {
  display: flex;
  align-items: center;
  gap: .6rem;
}

.brand-icon-box {
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.brand-title {
  color: var(--ink);
  font-size: .96875rem;
  font-weight: 700;
  letter-spacing: -0.025em;
}

.brand-version-chip {
  font-family: 'JetBrains Mono', monospace;
  font-size: .65rem;
  font-weight: 600;
  padding: .1rem .42rem;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.04);
  color: var(--ink-3);
  border: 1px solid var(--border);
}

.sidebar-category {
  font-size: .65rem;
  font-weight: 600;
  color: var(--ink-4);
  letter-spacing: .06em;
  text-transform: uppercase;
  padding: .35rem .55rem .25rem;
}

.nav-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-pill, .sidebar-util-link {
  display: flex;
  align-items: center;
  gap: .6rem;
  height: 34px;
  padding: 0 .65rem;
  border-radius: 8px;
  font-size: .8125rem;
  font-weight: 500;
  color: var(--ink-3);
  background: transparent;
  border: 1px solid transparent;
  cursor: pointer;
  width: 100%;
  text-align: left;
  transition: background-color 140ms ease, color 140ms ease, border-color 140ms ease;
  text-decoration: none;
}

@media (hover: hover) and (pointer: fine) {
  .nav-pill:hover, .sidebar-util-link:hover {
    background: rgba(255, 255, 255, 0.045);
    color: var(--ink);
  }
}

.nav-pill.active, .sidebar-util-link.active {
  background: rgba(255, 255, 255, 0.07);
  color: var(--ink);
  border-color: var(--border);
  font-weight: 600;
}

.nav-pill-icon {
  width: 18px;
  text-align: center;
  font-size: .8125rem;
  color: var(--ink-3);
}

.nav-pill.active .nav-pill-icon, .sidebar-util-link.active i {
  color: var(--emerald-light);
}

.sidebar-bottom {
  display: flex;
  flex-direction: column;
  gap: .875rem;
  padding-top: .875rem;
  border-top: 1px solid var(--border-sub);
}

.util-links {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.user-card-pill {
  display: flex;
  align-items: center;
  gap: .625rem;
  padding: .45rem .55rem;
  background: rgba(255, 255, 255, 0.025);
  border-radius: 10px;
  border: 1px solid var(--border);
}

.user-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--emerald);
  color: #04130e;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: .75rem;
  flex-shrink: 0;
  overflow: hidden;
}

.user-meta { min-width: 0; flex: 1; }
.user-meta-name { font-size: .78125rem; font-weight: 600; color: var(--ink); line-height: 1.25; }
.user-meta-role { font-size: .6875rem; color: var(--ink-3); line-height: 1.25; }

.user-signout-btn {
  color: var(--ink-3);
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background-color 140ms ease, color 140ms ease;
}
@media (hover: hover) and (pointer: fine) {
  .user-signout-btn:hover {
    background: rgba(255, 255, 255, 0.06);
    color: var(--ink);
  }
}

/* ── Main Canvas ── */
.main-canvas {
  margin-left: 244px;
  width: calc(100% - 244px);
  max-width: 1120px;
  padding: 1.75rem 2.25rem 4rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  min-height: 100vh;
}

.canvas-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  padding-bottom: 1.15rem;
  border-bottom: 1px solid var(--border-sub);
}

.canvas-title {
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: -0.025em;
  color: var(--ink);
  line-height: 1.2;
}

.mobile-menu-btn {
  display: none;
  background: var(--card);
  border: 1px solid var(--border);
  color: var(--ink);
  cursor: pointer;
  border-radius: 8px;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
}

.sidebar-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(8px);
  z-index: 180;
}

.sidebar-close-btn {
  display: none;
  background: transparent;
  border: none;
  color: var(--ink-3);
  font-size: 1rem;
  cursor: pointer;
  padding: .25rem;
  border-radius: 6px;
}

@media (max-width: 1024px) {
  .sidebar {
    width: 270px;
    max-width: 84vw;
    transform: translateX(-100%);
    transition: transform 240ms var(--ease-drawer);
    box-shadow: var(--shadow-lg);
    z-index: 250;
  }
  .sidebar.mobile-open { transform: translateX(0); }
  .sidebar-backdrop.mobile-open { display: block; }
  .sidebar-close-btn { display: flex; }
  .mobile-menu-btn { display: flex; }
  .main-canvas {
    margin-left: 0 !important;
    width: 100% !important;
    padding: 1.25rem 1rem calc(5.5rem + env(safe-area-inset-bottom, 20px)) !important;
  }
}

.mobile-bottom-nav { display: none; }
@media (max-width: 768px) {
  .mobile-bottom-nav {
    display: flex !important;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: calc(56px + env(safe-area-inset-bottom, 0px));
    background: rgba(14, 14, 18, 0.88);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border-top: 1px solid var(--border);
    z-index: 100;
    align-items: center;
    justify-content: space-around;
    padding: 0 .25rem env(safe-area-inset-bottom, 0px);
  }
  .mobile-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    height: 56px;
    color: var(--ink-3);
    background: transparent;
    border: none;
    text-decoration: none;
    font-size: .6875rem;
    font-weight: 500;
    cursor: pointer;
    transition: color 150ms ease;
  }
  .mobile-nav-item.active {
    color: var(--emerald-light);
    font-weight: 600;
  }
  .mobile-nav-item i {
    font-size: 1rem;
  }
}

/* ── Two-Column Layout: Instructions + Dropzone ── */
.top-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
  align-items: stretch;
}
@media (max-width: 900px) {
  .top-grid { grid-template-columns: 1fr; }
}

.card-panel {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--r-card);
  padding: 1.35rem;
  box-shadow: var(--specular-card);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.step-row {
  display: flex;
  gap: .75rem;
  align-items: flex-start;
  margin-bottom: .95rem;
}
.step-row:last-child { margin-bottom: 0; }

.step-badge {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.05);
  color: var(--ink);
  border: 1px solid var(--border);
  font-family: 'JetBrains Mono', monospace;
  font-size: .7rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 1px;
}

.step-body { flex: 1; }
.step-title { font-weight: 600; font-size: .84375rem; color: var(--ink); }
.step-desc { font-size: .78125rem; color: var(--ink-3); margin-top: .15rem; line-height: 1.45; }

.warning-box {
  display: flex;
  gap: .625rem;
  padding: .75rem .875rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border);
  border-radius: 10px;
  font-size: .75rem;
  color: var(--ink-3);
  margin-top: 1.15rem;
  align-items: flex-start;
  line-height: 1.45;
}
.warning-box i {
  color: var(--emerald-light);
  margin-top: 2px;
  flex-shrink: 0;
}

.dropzone {
  border: 1.5px dashed rgba(255, 255, 255, 0.14);
  border-radius: var(--r-card);
  background: var(--card);
  padding: 2.25rem 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 150ms ease, background-color 150ms ease, transform 150ms var(--ease-out);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 240px;
  box-shadow: var(--specular-card);
}

@media (hover: hover) and (pointer: fine) {
  .dropzone:hover {
    border-color: var(--emerald);
    background: var(--card-hover);
  }
}

.dropzone.drag {
  border-color: var(--emerald);
  background: rgba(16, 185, 129, 0.06);
  transform: scale(0.99);
}

.dropzone-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  color: var(--ink);
  margin-bottom: .875rem;
}

.dropzone-title {
  font-weight: 600;
  font-size: .9375rem;
  color: var(--ink);
  margin-bottom: .25rem;
}

.dropzone-hint {
  font-size: .78125rem;
  color: var(--ink-3);
}

/* ── Decoded Accounts Review Table ── */
.review-wrap {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--r-card);
  padding: 1.35rem;
  box-shadow: var(--specular-card);
}

.review-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: .75rem;
}

.review-header h2 {
  font-size: .96875rem;
  font-weight: 650;
  color: var(--ink);
}

.account-table {
  width: 100%;
  border-collapse: collapse;
}

.account-table th {
  font-size: .6875rem;
  font-weight: 600;
  color: var(--ink-3);
  text-transform: uppercase;
  letter-spacing: .05em;
  padding: .625rem .75rem;
  border-bottom: 1px solid var(--border);
  text-align: left;
}

.account-table td {
  padding: .75rem;
  border-bottom: 1px solid var(--border-sub);
  font-size: .8125rem;
  vertical-align: middle;
  color: var(--ink);
}

.account-table tr:last-child td { border-bottom: none; }
.account-table tr.skipped td { opacity: .45; }
.account-table tr.imported td { background: rgba(16, 185, 129, 0.06); }

.account-name { font-weight: 600; color: var(--ink); }
.account-issuer { font-size: .72rem; color: var(--ink-3); margin-top: 2px; }

.badge-totp {
  background: var(--emerald-l);
  color: var(--emerald-light);
  border: 1px solid var(--emerald-border);
  font-family: 'JetBrains Mono', monospace;
  font-size: .6875rem;
  padding: .1rem .45rem;
  border-radius: 6px;
  font-weight: 600;
}

.badge-hotp {
  background: var(--amber-l);
  color: var(--amber-light);
  border: 1px solid var(--amber-border);
  font-family: 'JetBrains Mono', monospace;
  font-size: .6875rem;
  padding: .1rem .45rem;
  border-radius: 6px;
  font-weight: 600;
}

.import-footer {
  margin-top: 1.25rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-sub);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .75rem;
}

.import-summary {
  font-size: .8125rem;
  color: var(--ink-3);
  font-variant-numeric: tabular-nums;
}

#progress-bar-wrap { margin-top: 1rem; display: none; }
.progress-track {
  height: 6px;
  background: var(--canvas-sub);
  border: 1px solid var(--border);
  border-radius: 999px;
  overflow: hidden;
  margin-top: .35rem;
}
.progress-fill {
  height: 100%;
  background: var(--emerald);
  border-radius: 999px;
  transition: width 220ms var(--ease-out);
}
</style>

<div class="app-shell">

  <!-- Mobile Backdrop -->
  <div class="sidebar-backdrop" id="sidebar-backdrop" onclick="toggleSidebarMobile()"></div>

  <!-- Left Sidebar -->
  <aside class="sidebar" id="app-sidebar">
    <div class="sidebar-top">
      <div class="sidebar-brand">
        <div class="brand-identity">
          <div class="brand-icon-box">
            <img src="/logo.png" alt="TOTPVault Logo" style="width:24px;height:24px;object-fit:contain">
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

      <div>
        <div class="sidebar-category">Ruang Kerja</div>
        <div class="nav-list">
          <a href="/tokens" class="nav-pill">
            <div class="nav-pill-icon"><i class="fa-solid fa-key"></i></div>
            <span>Brankas Token</span>
          </a>
          <?php if (!empty($user['is_admin'])): ?>
          <a href="/access" class="nav-pill">
            <div class="nav-pill-icon"><i class="fa-solid fa-sliders"></i></div>
            <span>Kelola Akses</span>
          </a>
          <a href="/whitelist" class="nav-pill">
            <div class="nav-pill-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <span>Whitelist Akun</span>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="sidebar-bottom">
      <div>
        <div class="sidebar-category">Perkakas</div>
        <div class="util-links">
          <a href="/tools/import-google-auth" class="sidebar-util-link active">
            <i class="fa-solid fa-qrcode" style="width:18px;text-align:center"></i>
            <span>Impor Authenticator</span>
          </a>
          <a href="/api/export-profiles" class="sidebar-util-link">
            <i class="fa-solid fa-arrow-up-right-from-square" style="width:18px;text-align:center"></i>
            <span>Ekspor Profil</span>
          </a>
          <a href="https://github.com/token2/TOTPVault" target="_blank" rel="noopener" class="sidebar-util-link" title="Open source base engine by Token2">
            <i class="fa-brands fa-github" style="width:18px;text-align:center"></i>
            <span>Hulu: Token2 (GPL-3.0)</span>
          </a>
        </div>
      </div>

      <div class="user-card-pill">
        <div class="user-avatar" id="import-user-avatar">
          <?php if ($avatar): ?>
            <img src="<?= $avatar ?>" alt="" style="width:100%;height:100%;object-fit:cover" onerror="this.remove();document.getElementById('import-user-avatar').textContent='<?= $initial ?>';">
          <?php else: ?>
            <?= $initial ?>
          <?php endif; ?>
        </div>
        <div class="user-meta">
          <div class="user-meta-name truncate"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
          <div class="user-meta-role truncate"><?= htmlspecialchars($user['email']) ?></div>
        </div>
        <a href="/auth/logout" class="user-signout-btn" title="Keluar sesi">
          <i class="fa-solid fa-arrow-right-from-bracket" style="font-size:.8rem"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- Main Canvas -->
  <main class="main-canvas">

    <div class="canvas-header">
      <div style="display:flex;align-items:center;gap:.75rem">
        <button class="mobile-menu-btn" onclick="toggleSidebarMobile()" aria-label="Buka navigasi">
          <i class="fa-solid fa-bars"></i>
        </button>
        <div>
          <h1 class="canvas-title">Impor Google Authenticator</h1>
          <p style="color:var(--ink-3);font-size:.8125rem;margin-top:.2rem">
            Dekode payload migrasi QR dari Google Authenticator secara langsung ke brankas Anda.
          </p>
        </div>
      </div>
      <a href="/tokens" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-arrow-left" style="font-size:.7rem"></i>
        <span>Kembali ke Brankas</span>
      </a>
    </div>

    <div class="top-grid">
      <!-- Instructions Card -->
      <div class="card-panel">
        <div>
          <div style="font-weight:650;font-size:.875rem;color:var(--ink);margin-bottom:1rem">
            Langkah Migrasi
          </div>

          <div class="step-row">
            <div class="step-badge">1</div>
            <div class="step-body">
              <div class="step-title">Ekspor dari Aplikasi Ponsel</div>
              <div class="step-desc">Buka Google Authenticator → menu <strong>Transfer akun</strong> → pilih <strong>Ekspor akun</strong>.</div>
            </div>
          </div>

          <div class="step-row">
            <div class="step-badge">2</div>
            <div class="step-body">
              <div class="step-title">Simpan Gambar Kode QR</div>
              <div class="step-desc">Ambil tangkapan layar (screenshot) atau foto kode QR migrasi yang tampil di layar.</div>
            </div>
          </div>

          <div class="step-row">
            <div class="step-badge">3</div>
            <div class="step-body">
              <div class="step-title">Unggah atau Tempel Gambar</div>
              <div class="step-desc">Tarik gambar ke area di samping atau tekan <kbd class="mono" style="font-size:.68rem;background:var(--canvas);border:1px solid var(--border);border-radius:4px;padding:1px 5px">Ctrl+V</kbd>.</div>
            </div>
          </div>
        </div>

        <div class="warning-box">
          <i class="fa-solid fa-shield-halved"></i>
          <div>Pemindaian gambar QR dilakukan di browser Anda. Hapus tangkapan layar QR dari perangkat Anda setelah impor selesai.</div>
        </div>
      </div>

      <!-- Dropzone Card -->
      <div id="dropzone" class="dropzone"
        onclick="document.getElementById('file-input').click()"
        ondragover="event.preventDefault();this.classList.add('drag')"
        ondragleave="this.classList.remove('drag')"
        ondrop="handleDrop(event)">
        <div class="dropzone-icon"><i class="fa-solid fa-qrcode"></i></div>
        <div class="dropzone-title">Tarik &amp; Letakkan Gambar QR di Sini</div>
        <div class="dropzone-hint">Klik untuk memilih file &middot; atau tekan Ctrl+V</div>
      </div>
    </div>

    <input type="file" id="file-input" accept="image/*" style="display:none" onchange="handleFile(this.files[0])">
    <canvas id="decode-canvas" style="display:none;position:absolute;opacity:0;pointer-events:none"></canvas>

    <!-- Review Area -->
    <div id="review-wrap" class="review-wrap" style="display:none">
      <div class="review-header">
        <h2 id="review-title">Akun Ditemukan</h2>
        <div style="display:flex;gap:.45rem;align-items:center">
          <button class="btn btn-secondary btn-sm" onclick="toggleAll(true)">Pilih Semua</button>
          <button class="btn btn-ghost btn-sm" onclick="toggleAll(false)">Batalkan Pilihan</button>
        </div>
      </div>

      <div style="overflow-x:auto">
        <table class="account-table" id="account-table">
          <thead>
            <tr>
              <th style="width:36px"></th>
              <th>Nama Akun</th>
              <th>Tipe</th>
              <th>Algoritma</th>
              <th>Digit</th>
              <th style="width:110px">Status</th>
            </tr>
          </thead>
          <tbody id="account-tbody"></tbody>
        </table>
      </div>

      <div id="progress-bar-wrap">
        <div class="import-summary" id="progress-label">Mengimpor…</div>
        <div class="progress-track"><div class="progress-fill" id="progress-fill" style="width:0%"></div></div>
      </div>

      <div class="import-footer">
        <div class="import-summary" id="import-summary"></div>
        <button class="btn btn-primary" id="import-btn" onclick="importSelected()">
          <i class="fa-solid fa-download" style="font-size:.75rem"></i>
          <span>Impor Akun Terpilih</span>
        </button>
      </div>
    </div>

    <!-- Mobile bottom clearance spacer -->
    <div class="import-bottom-spacer" style="height: 48px; width: 100%; pointer-events: none;"></div>
  </main>

  <!-- Mobile Bottom Navigation -->
  <nav class="mobile-bottom-nav" id="mobile-bottom-nav">
    <a href="/tokens" class="mobile-nav-item" id="mob-nav-tokens">
      <i class="fa-solid fa-key"></i>
      <span>Brankas</span>
    </a>

    <?php if (!empty($user['is_admin'])): ?>
    <a href="/access" class="mobile-nav-item" id="mob-nav-access">
      <i class="fa-solid fa-sliders"></i>
      <span>Akses</span>
    </a>

    <a href="/whitelist" class="mobile-nav-item" id="mob-nav-whitelist">
      <i class="fa-solid fa-shield-halved"></i>
      <span>Whitelist</span>
    </a>
    <?php endif; ?>

    <a href="/tools/import-google-auth" class="mobile-nav-item active" id="mob-nav-import">
      <i class="fa-solid fa-qrcode"></i>
      <span>Impor</span>
    </a>

    <button class="mobile-nav-item" onclick="toggleSidebarMobile()" id="mob-nav-menu">
      <i class="fa-solid fa-bars"></i>
      <span>Menu</span>
    </button>
  </nav>
</div>

<script>
const CSRF = <?= json_encode($csrfToken) ?>;
let decodedAccounts = [];

function handleDrop(e) {
  e.preventDefault();
  document.getElementById('dropzone').classList.remove('drag');
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) handleFile(file);
}

document.addEventListener('paste', e => {
  const items = e.clipboardData?.items;
  if (!items) return;
  for (const item of items) {
    if (item.type.startsWith('image/')) { handleFile(item.getAsFile()); return; }
  }
});

function handleFile(file) {
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => decodeQR(e.target.result);
  reader.readAsDataURL(file);
}

function decodeQR(dataUrl) {
  const img = new Image();
  img.onload = () => {
    const canvas = document.getElementById('decode-canvas');
    const ctx    = canvas.getContext('2d');

    const scales = [1, 2, 1.5, 3, 0.75];
    for (const scale of scales) {
      const w = Math.round(img.width  * scale);
      const h = Math.round(img.height * scale);
      canvas.width  = w;
      canvas.height = h;
      ctx.imageSmoothingEnabled = false;
      ctx.drawImage(img, 0, 0, w, h);
      const imageData = ctx.getImageData(0, 0, w, h);
      const result    = jsQR(imageData.data, w, h, { inversionAttempts: 'attemptBoth' });
      if (result) { handleQRData(result.data); return; }
    }

    toast('Kode QR tidak terbaca. Pastikan seluruh area QR terlihat jelas.', 'error');
  };
  img.src = dataUrl;
}

function handleQRData(uri) {
  if (!uri.startsWith('otpauth-migration://')) {
    if (uri.startsWith('otpauth://')) {
      toast('Ini adalah QR akun tunggal. Gunakan tombol + Token Baru di Brankas.', 'error');
    } else {
      toast('Bukan kode QR ekspor Google Authenticator.', 'error');
    }
    return;
  }

  let dataParam;
  try {
    dataParam = new URL(uri).searchParams.get('data');
    if (!dataParam) throw new Error('Missing data parameter');
  } catch {
    toast('Gagal membaca URI migrasi.', 'error'); return;
  }

  fetch(window.location.pathname, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
    body: JSON.stringify({ data: dataParam }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.error) { toast(d.error, 'error'); return; }
    appendAccounts(d.accounts);
  })
  .catch(() => toast('Permintaan gagal. Silakan coba lagi.', 'error'));
}

function appendAccounts(accounts) {
  const startIdx = decodedAccounts.length;
  decodedAccounts = decodedAccounts.concat(accounts);

  const tbody = document.getElementById('account-tbody');
  accounts.forEach((a, i) => {
    const idx = startIdx + i;
    const isHotp = a.type === 'hotp';
    const tr = document.createElement('tr');
    tr.id = `row-${idx}`;
    tr.innerHTML = `
      <td><input type="checkbox" id="chk-${idx}" ${isHotp ? '' : 'checked'} style="width:15px;height:15px;cursor:pointer;accent-color:var(--emerald)" onchange="updateSummary()"></td>
      <td>
        <div class="account-name">${esc(a.name || '(tanpa nama)')}</div>
        ${a.issuer ? `<div class="account-issuer">${esc(a.issuer)}</div>` : ''}
      </td>
      <td><span class="badge-${a.type}">${a.type.toUpperCase()}</span>${isHotp ? ' <span style="font-size:.7rem;color:var(--amber-light)">HOTP tidak didukung</span>' : ''}</td>
      <td class="mono">${esc(a.algorithm)}</td>
      <td class="mono">${a.digits}</td>
      <td id="status-${idx}"><span style="color:var(--ink-4);font-size:.75rem">Menunggu</span></td>
    `;
    if (isHotp) {
      tr.querySelector('input').disabled = true;
      tr.classList.add('skipped');
    }
    tbody.appendChild(tr);
  });

  document.getElementById('review-wrap').style.display = '';
  updateSummary();
  toast(`${accounts.length} akun berhasil didekode dari QR.`, 'success');
}

function updateSummary() {
  const total    = decodedAccounts.length;
  const selected = decodedAccounts.filter((_, i) => {
    const chk = document.getElementById(`chk-${i}`);
    return chk && !chk.disabled && chk.checked;
  }).length;
  document.getElementById('review-title').textContent = `${total} Akun Ditemukan`;
  document.getElementById('import-summary').textContent = `${selected} dari ${total} dipilih`;
}

function toggleAll(state) {
  decodedAccounts.forEach((_, i) => {
    const chk = document.getElementById(`chk-${i}`);
    if (chk && !chk.disabled) chk.checked = state;
  });
  updateSummary();
}

async function importSelected() {
  const toImport = decodedAccounts
    .map((a, i) => ({ account: a, idx: i }))
    .filter(({ idx }) => {
      const chk = document.getElementById(`chk-${idx}`);
      return chk && !chk.disabled && chk.checked;
    });

  if (!toImport.length) { toast('Pilih setidaknya satu akun.', 'error'); return; }

  const btn = document.getElementById('import-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Mengimpor…</span>';

  const progressWrap = document.getElementById('progress-bar-wrap');
  const progressFill = document.getElementById('progress-fill');
  const progressLabel = document.getElementById('progress-label');
  progressWrap.style.display = '';

  let done = 0, failed = 0;

  const profilesToBatch = toImport.map(({ account: a }) => ({
    name:      a.issuer ? `${a.issuer} (${a.name})` : a.name,
    issuer:    a.issuer || '',
    secret:    a.secret,
    algorithm: a.algorithm,
    digits:    String(a.digits),
    period:    30,
    color:     '#10b981',
    icon:      'fa-shield-halved',
    hide_code: 0,
  }));

  toImport.forEach(({ idx }) => {
    const statusEl = document.getElementById(`status-${idx}`);
    if (statusEl) statusEl.innerHTML = '<span style="color:var(--ink-3);font-size:.75rem">Memproses…</span>';
  });

  try {
    const r = await fetch('/api/profiles/batch', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({ profiles: profilesToBatch }),
    });
    const d = await r.json();
    if (d.error) throw new Error(d.error);

    done = d.imported_count || toImport.length;
    toImport.forEach(({ idx }) => {
      const statusEl = document.getElementById(`status-${idx}`);
      if (statusEl) statusEl.innerHTML = '<span style="color:var(--emerald-light);font-size:.75rem;font-weight:600"><i class="fa-solid fa-check"></i> Diimpor</span>';
      document.getElementById(`row-${idx}`)?.classList.add('imported');
    });

    progressFill.style.width = '100%';
    progressLabel.textContent = `Selesai — ${done} token berhasil ditambahkan.`;
  } catch (batchErr) {
    for (const { account: a, idx } of toImport) {
      const statusEl = document.getElementById(`status-${idx}`);
      try {
        const r = await fetch('/api/profiles', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
          body: JSON.stringify({
            name:      a.issuer ? `${a.issuer} (${a.name})` : a.name,
            issuer:    a.issuer || '',
            secret:    a.secret,
            algorithm: a.algorithm,
            digits:    String(a.digits),
            period:    '30',
            color:     '#10b981',
            icon:      'fa-shield-halved',
            hide_code: 0,
          }),
        });
        const d = await r.json();
        if (d.error) throw new Error(d.error);
        statusEl.innerHTML = '<span style="color:var(--emerald-light);font-size:.75rem;font-weight:600"><i class="fa-solid fa-check"></i> Diimpor</span>';
        document.getElementById(`row-${idx}`)?.classList.add('imported');
        done++;
      } catch (err) {
        statusEl.innerHTML = `<span style="color:var(--red-light);font-size:.75rem" title="${esc(err.message)}"><i class="fa-solid fa-xmark"></i> Gagal</span>`;
        failed++;
      }
      progressFill.style.width = `${Math.round(((done + failed) / toImport.length) * 100)}%`;
      progressLabel.textContent = `Mengimpor ${done} dari ${toImport.length}…`;
    }
  }

  progressLabel.textContent = `Selesai — ${done} token diimpor${failed ? `, ${failed} gagal` : ''}.`;
  btn.disabled = false;
  btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Selesai</span>';

  if (done > 0) {
    toast(`Berhasil mengimpor ${done} token ke TOTPVault.`, 'success');
    setTimeout(() => { window.location.href = '/tokens'; }, 1400);
  }
}

function esc(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

let lastToastMsg = '';
let lastToastTime = 0;

function toast(msg, type = 'success') {
  const cleanMsg = String(msg).replace(/^[✓✔ℹ✕✖✗]\s*/, '').trim();
  const now = Date.now();
  if (cleanMsg === lastToastMsg && now - lastToastTime < 700) return;
  lastToastMsg = cleanMsg;
  lastToastTime = now;

  const icons = { success: '✓', error: '✕', info: 'ℹ' };
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span class="toast-icon">${icons[type] || 'ℹ'}</span><span>${cleanMsg}</span>`;
  document.getElementById('toast-container').appendChild(el);
  setTimeout(() => { el.style.animation = 'toastOut 180ms var(--ease-out) forwards'; setTimeout(() => el.remove(), 180); }, 3600);
}

function toggleSidebarMobile() {
  const sb = document.getElementById('app-sidebar');
  const bd = document.getElementById('sidebar-backdrop');
  const isOpen = sb.classList.toggle('mobile-open');
  if (bd) bd.classList.toggle('mobile-open', isOpen);
  document.body.style.overflow = isOpen ? 'hidden' : '';
}

updateSummary();
</script>
</body>
</html>
