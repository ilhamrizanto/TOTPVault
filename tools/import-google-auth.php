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
$pageTitle = 'Import Google Authenticator — TOTPVault';
$csrfToken = csrf_token();
$avatar    = $user['avatar_url'] ? htmlspecialchars($user['avatar_url']) : null;
$initial   = strtoupper(substr($user['name'] ?? $user['email'], 0, 1));

require BASE_DIR . '/templates/layout.php';
?>

<style>
/* ── Pitch-Black Obsidian Import Tool Layout ── */
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

/* ── Left Sidebar (240px Pitch-Black Dock) ── */
.sidebar {
  width: 240px;
  flex-shrink: 0;
  background: #08090d;
  border-right: 1px solid rgba(255, 255, 255, 0.08);
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
  padding: .15rem .35rem;
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
}
.brand-icon-box svg {
  width: 32px;
  height: 32px;
}

.brand-title {
  color: #ffffff;
  font-size: 1.15rem;
  font-weight: 800;
  letter-spacing: -0.025em;
}

.brand-version-chip {
  font-size: .625rem;
  font-weight: 700;
  padding: .15rem .45rem;
  border-radius: var(--r-pill);
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
  letter-spacing: .04em;
}

.sidebar-category {
  font-size: .625rem;
  font-weight: 700;
  color: #64748b;
  letter-spacing: .08em;
  text-transform: uppercase;
  padding: .4rem .5rem .2rem;
}

.nav-list {
  display: flex;
  flex-direction: column;
  gap: .25rem;
}

.nav-pill {
  display: flex;
  align-items: center;
  gap: .625rem;
  padding: .5rem .75rem;
  border-radius: 10px;
  font-size: .8125rem;
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
}
.nav-pill.active {
  background: #12141d;
  color: #10b981;
  border-color: rgba(16, 185, 129, 0.25);
  font-weight: 600;
}
.nav-pill-icon {
  width: 18px;
  text-align: center;
  font-size: .875rem;
}

.sidebar-bottom {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.util-links {
  display: flex;
  flex-direction: column;
  gap: .15rem;
}
.sidebar-util-link {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .75rem;
  color: #94a3b8;
  padding: .4rem .6rem;
  border-radius: 8px;
  border: 1px solid transparent;
  transition: all .15s ease;
  text-decoration: none;
}
.sidebar-util-link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
}
.sidebar-util-link.active {
  background: #12141d;
  color: #10b981;
  border-color: rgba(16, 185, 129, 0.25);
  font-weight: 600;
}

.user-card-pill {
  display: flex;
  align-items: center;
  gap: .625rem;
  padding: .5rem .625rem;
  background: #07080d;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.user-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #10b981;
  color: #050508;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: .8125rem;
  flex-shrink: 0;
  overflow: hidden;
}
.user-meta { min-width: 0; flex: 1; }
.user-meta-name { font-size: .8125rem; font-weight: 700; color: #ffffff; line-height: 1.2; }
.user-meta-role { font-size: .6875rem; color: #7a9c95; line-height: 1.2; }
.user-signout-btn {
  color: #64748b;
  padding: .25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
}
.user-signout-btn:hover { color: #ffffff; }

/* ── Main Canvas (Import Tool) ── */
.main-canvas {
  margin-left: 240px;
  width: calc(100% - 240px);
  max-width: calc(100% - 240px);
  box-sizing: border-box;
  background: var(--canvas);
  padding: 1.5rem 2.5rem 4rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  min-height: 100vh;
  overflow-x: hidden;
}

.canvas-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.canvas-title {
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #ffffff;
  line-height: 1.1;
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
  width: 38px;
  height: 38px;
}

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

@media(max-width: 1024px) {
  .app-shell { flex-direction: column; width: 100%; max-width: 100%; }
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
  .mobile-menu-btn { display: flex; }
  .main-canvas {
    margin-left: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    padding: 1rem 1rem calc(5.5rem + env(safe-area-inset-bottom, 20px)) !important;
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
  .mobile-nav-item.active {
    color: #10b981;
  }
  .mobile-nav-item i {
    font-size: 1.15rem;
  }
}

.top-grid {
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  gap: 1.25rem;
  align-items: stretch;
}
@media(max-width: 900px) {
  .top-grid { grid-template-columns: 1fr; }
}

.card-panel {
  background: #0c0d12;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.35rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.step-row {
  display: flex;
  gap: .75rem;
  align-items: flex-start;
  margin-bottom: 1rem;
}
.step-row:last-child { margin-bottom: 0; }
.step-badge {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
  font-size: .6875rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: .15rem;
}
.step-body { flex: 1; }
.step-title { font-weight: 700; font-size: .84375rem; color: #ffffff; }
.step-desc { font-size: .75rem; color: #7a9c95; margin-top: .15rem; line-height: 1.4; }

.warning-box {
  display: flex;
  gap: .625rem;
  padding: .75rem .875rem;
  background: rgba(245, 158, 11, 0.08);
  border: 1px solid rgba(245, 158, 11, 0.25);
  border-radius: 10px;
  font-size: .75rem;
  color: #fde68a;
  margin-top: 1rem;
  align-items: flex-start;
}
.warning-box i {
  color: #f59e0b;
  margin-top: .1rem;
  flex-shrink: 0;
}

.dropzone {
  border: 2px dashed rgba(255, 255, 255, 0.12);
  border-radius: 16px;
  background: #07080d;
  padding: 2rem 1.25rem;
  text-align: center;
  cursor: pointer;
  transition: all .18s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 220px;
  height: 100%;
}
.dropzone:hover, .dropzone.drag {
  border-color: #10b981;
  background: rgba(16, 185, 129, 0.05);
  transform: scale(1.005);
}
.dropzone-icon {
  font-size: 2.25rem;
  color: #10b981;
  margin-bottom: .75rem;
}
.dropzone-title {
  font-weight: 700;
  font-size: .9375rem;
  color: #ffffff;
  margin-bottom: .25rem;
}
.dropzone-hint {
  font-size: .75rem;
  color: #64748b;
}

/* Review Table */
.review-wrap {
  background: #0c0d12;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.35rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
  margin-top: .5rem;
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
  font-size: 1rem;
  font-weight: 700;
  color: #ffffff;
}

.account-table {
  width: 100%;
  border-collapse: collapse;
}
.account-table th {
  font-size: .6875rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .06em;
  padding: .625rem .75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  text-align: left;
}
.account-table td {
  padding: .75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  font-size: .8125rem;
  vertical-align: middle;
  color: #f8fafc;
}
.account-table tr:last-child td { border-bottom: none; }
.account-table tr.skipped td { opacity: .4; }
.account-table tr.imported td { background: rgba(16, 185, 129, 0.1); }

.account-name { font-weight: 600; color: #ffffff; }
.account-issuer { font-size: .6875rem; color: #7a9c95; margin-top: .1rem; }
.badge-totp {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.25);
  font-size: .6875rem;
  padding: .15rem .5rem;
  border-radius: var(--r-pill);
  font-weight: 700;
}
.badge-hotp {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.25);
  font-size: .6875rem;
  padding: .15rem .5rem;
  border-radius: var(--r-pill);
  font-weight: 700;
}

.import-footer {
  margin-top: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .75rem;
}
.import-summary { font-size: .8125rem; color: #7a9c95; }

#progress-bar-wrap { margin-top: 1rem; display: none; }
.progress-track {
  height: 6px;
  background: #07080d;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 3px;
  overflow: hidden;
  margin-top: .35rem;
}
.progress-fill {
  height: 100%;
  background: var(--emerald);
  border-radius: 3px;
  transition: width .3s ease;
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
        <div class="sidebar-category">WORKSPACE</div>
        <div class="nav-list">
          <a href="/tokens" class="nav-pill">
            <div class="nav-pill-icon"><i class="fa-solid fa-key"></i></div>
            <span>Tokens</span>
          </a>
          <a href="/whitelist" class="nav-pill">
            <div class="nav-pill-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <span>Whitelist Akun</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Utilities Menu (Directly above Profile) -->
    <div class="sidebar-bottom">
      <div>
        <div class="sidebar-category" style="padding-left:.35rem">UTILITIES</div>
        <div class="util-links">
          <a href="/tools/import-google-auth" class="sidebar-util-link active">
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

      <!-- User Account Section at Bottom -->
      <div>
        <div class="sidebar-category" style="padding-left:.35rem">USER ACCOUNT</div>
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
          <a href="/auth/logout" class="user-signout-btn" title="Sign out">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </a>
        </div>
      </div>
    </div>
  </aside>

  <!-- ── Main Canvas ── -->
  <main class="main-canvas">

    <!-- Header Bar -->
    <div class="canvas-header">
      <div style="display:flex;align-items:center;gap:.75rem">
        <button class="mobile-menu-btn" onclick="toggleSidebarMobile()" aria-label="Toggle navigation">
          <i class="fa-solid fa-bars"></i>
        </button>
        <div>
          <h1 class="canvas-title">Import from Google Authenticator</h1>
          <p style="color:var(--ink-3);font-size:.8125rem;margin-top:.25rem">
            Impor token akun dari QR code ekspor Google Authenticator secara otomatis.
          </p>
        </div>
      </div>
      <a href="/tokens" class="btn btn-secondary btn-sm" style="height:34px;padding:0 .875rem">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Kembali ke Tokens</span>
      </a>
    </div>

    <!-- Top Grid: Steps & Dropzone -->
    <div class="top-grid">
      <!-- Steps Column -->
      <div class="card-panel">
        <div style="font-weight:700;font-size:.875rem;color:var(--ink);margin-bottom:.875rem;display:flex;align-items:center;gap:.5rem">
          <i class="fa-solid fa-circle-info" style="color:var(--emerald)"></i>
          <span>Panduan Langkah Ekspor</span>
        </div>

        <div class="step-row">
          <div class="step-badge">1</div>
          <div class="step-body">
            <div class="step-title">Buka Google Authenticator di Ponsel</div>
            <div class="step-desc">Tekan menu tiga titik (⋮) → pilih <strong>Transfer akun</strong> → pilih <strong>Ekspor akun</strong>.</div>
          </div>
        </div>

        <div class="step-row">
          <div class="step-badge">2</div>
          <div class="step-body">
            <div class="step-title">Ambil Tangkapan Layar (Screenshot) QR Code</div>
            <div class="step-desc">Aplikasi Google Authenticator akan menampilkan QR code transfer. Screenshot kode QR tersebut.</div>
          </div>
        </div>

        <div class="step-row">
          <div class="step-badge">3</div>
          <div class="step-body">
            <div class="step-title">Tarik atau Tempel (Paste) di Samping</div>
            <div class="step-desc">Seret gambar tangkapan layar ke area dropzone di samping atau tekan <kbd style="font-size:.7rem;background:var(--canvas);border:1px solid var(--border);border-radius:4px;padding:.1rem .35rem">Ctrl+V</kbd>.</div>
          </div>
        </div>

        <div class="warning-box">
          <i class="fa-solid fa-shield-halved"></i>
          <div><strong>Keamanan Rahasia:</strong> Gambar QR ekspor berisi kunci rahasia mentah akun Anda. Gambar diproses secara lokal di browser Anda. Hapus file screenshot setelah proses impor selesai.</div>
        </div>
      </div>

      <!-- Dropzone Column -->
      <div id="dropzone" class="dropzone"
        onclick="document.getElementById('file-input').click()"
        ondragover="event.preventDefault();this.classList.add('drag')"
        ondragleave="this.classList.remove('drag')"
        ondrop="handleDrop(event)">
        <div class="dropzone-icon"><i class="fa-solid fa-qrcode"></i></div>
        <div class="dropzone-title">Tarik & Letakkan Screenshot QR di Sini</div>
        <div class="dropzone-hint">atau klik untuk memilih file &middot; Ctrl+V untuk Paste</div>
      </div>
    </div>

    <input type="file" id="file-input" accept="image/*" style="display:none" onchange="handleFile(this.files[0])">
    <canvas id="decode-canvas" style="display:none;position:absolute;opacity:0;pointer-events:none"></canvas>

    <!-- Review Area (Shown after decode) -->
    <div id="review-wrap" class="review-wrap" style="display:none">
      <div class="review-header">
        <h2 id="review-title">Akun Ditemukan</h2>
        <div style="display:flex;gap:.5rem;align-items:center">
          <button class="btn btn-secondary btn-sm" onclick="toggleAll(true)" style="padding:.25rem .625rem;font-size:.75rem">Pilih Semua</button>
          <button class="btn btn-secondary btn-sm" onclick="toggleAll(false)" style="padding:.25rem .625rem;font-size:.75rem">Batalkan Semua</button>
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
              <th style="width:100px">Status</th>
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
        <button class="btn btn-primary btn-sm" id="import-btn" onclick="importSelected()" style="height:36px;padding:0 1.25rem">
          <i class="fa-solid fa-file-import"></i>
          Impor Akun Terpilih
        </button>
      </div>
    </div>

    <!-- Mobile bottom clearance spacer -->
    <div class="import-bottom-spacer" style="height: 48px; width: 100%; pointer-events: none;"></div>
  </main>

  <!-- ── Mobile Fixed Bottom Navigation Bar ── -->
  <nav class="mobile-bottom-nav" id="mobile-bottom-nav">
    <a href="/tokens" class="mobile-nav-item" id="mob-nav-tokens">
      <i class="fa-solid fa-key"></i>
      <span>Tokens</span>
    </a>

    <?php if (!empty($user['is_admin'])): ?>
    <a href="/access" class="mobile-nav-item" id="mob-nav-access">
      <i class="fa-solid fa-users-gear"></i>
      <span>Akses</span>
    </a>

    <a href="/whitelist" class="mobile-nav-item" id="mob-nav-whitelist">
      <i class="fa-solid fa-shield-halved"></i>
      <span>Whitelist</span>
    </a>
    <?php endif; ?>

    <a href="/tools/import-google-auth" class="mobile-nav-item active" id="mob-nav-import">
      <i class="fa-solid fa-file-import"></i>
      <span>Import</span>
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

// ── Drop / paste / file handling ──────────────────────────────────────────

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

// ── QR decoding ───────────────────────────────────────────────────────────

function decodeQR(dataUrl) {
  const img = new Image();
  img.onload = () => {
    const canvas = document.getElementById('decode-canvas');
    const ctx    = canvas.getContext('2d');

    // Try multiple scales — GA export QRs are dense and jsQR works better
    // at certain resolutions. Try native size first, then upscaled.
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

    toast('No QR code found. Make sure the entire QR is visible and try a higher-resolution screenshot.', 'error');
  };
  img.src = dataUrl;
}

function handleQRData(uri) {
  if (!uri.startsWith('otpauth-migration://')) {
    if (uri.startsWith('otpauth://')) {
      toast('This looks like a single-account QR. Use the regular Add Token button on the dashboard instead.', 'error');
    } else {
      toast('Not a Google Authenticator export QR code.', 'error');
    }
    return;
  }

  let dataParam;
  try {
    dataParam = new URL(uri).searchParams.get('data');
    if (!dataParam) throw new Error('Missing data parameter');
  } catch {
    toast('Could not parse migration URI.', 'error'); return;
  }

  // Send to PHP for protobuf decoding
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
  .catch(() => toast('Request failed. Please try again.', 'error'));
}

// ── Review table ──────────────────────────────────────────────────────────

function appendAccounts(accounts) {
  // Append to existing list (support multi-QR exports)
  const startIdx = decodedAccounts.length;
  decodedAccounts = decodedAccounts.concat(accounts);

  const tbody = document.getElementById('account-tbody');
  accounts.forEach((a, i) => {
    const idx = startIdx + i;
    const isHotp = a.type === 'hotp';
    const tr = document.createElement('tr');
    tr.id = `row-${idx}`;
    tr.innerHTML = `
      <td><input type="checkbox" id="chk-${idx}" ${isHotp ? '' : 'checked'} style="width:15px;height:15px;cursor:pointer" onchange="updateSummary()"></td>
      <td>
        <div class="account-name">${esc(a.name || '(unnamed)')}</div>
        ${a.issuer ? `<div class="account-issuer">${esc(a.issuer)}</div>` : ''}
      </td>
      <td><span class="badge-${a.type}">${a.type.toUpperCase()}</span>${isHotp ? ' <span style="font-size:.7rem;color:var(--amber)">⚠ HOTP not supported</span>' : ''}</td>
      <td>${esc(a.algorithm)}</td>
      <td>${a.digits}</td>
      <td id="status-${idx}"><span style="color:var(--ink4);font-size:.75rem">Pending</span></td>
    `;
    // Disable HOTP rows
    if (isHotp) {
      tr.querySelector('input').disabled = true;
      tr.classList.add('skipped');
    }
    tbody.appendChild(tr);
  });

  document.getElementById('review-wrap').style.display = '';
  updateSummary();
  toast(`Found ${accounts.length} account${accounts.length !== 1 ? 's' : ''}. Review and click Import.`, 'success');
}

function updateSummary() {
  const total    = decodedAccounts.length;
  const selected = decodedAccounts.filter((_, i) => {
    const chk = document.getElementById(`chk-${i}`);
    return chk && !chk.disabled && chk.checked;
  }).length;
  document.getElementById('review-title').textContent = `${total} account${total !== 1 ? 's' : ''} found`;
  document.getElementById('import-summary').textContent = `${selected} of ${total} selected`;
}

function toggleAll(state) {
  decodedAccounts.forEach((_, i) => {
    const chk = document.getElementById(`chk-${i}`);
    if (chk && !chk.disabled) chk.checked = state;
  });
  updateSummary();
}

// ── Import ────────────────────────────────────────────────────────────────

async function importSelected() {
  const toImport = decodedAccounts
    .map((a, i) => ({ account: a, idx: i }))
    .filter(({ idx }) => {
      const chk = document.getElementById(`chk-${idx}`);
      return chk && !chk.disabled && chk.checked;
    });

  if (!toImport.length) { toast('No accounts selected.', 'error'); return; }

  const btn = document.getElementById('import-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing…';

  const progressWrap = document.getElementById('progress-bar-wrap');
  const progressFill = document.getElementById('progress-fill');
  const progressLabel = document.getElementById('progress-label');
  progressWrap.style.display = '';

  let done = 0, failed = 0;

  // Build batch payload
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
    if (statusEl) statusEl.innerHTML = '<span style="color:var(--ink4);font-size:.75rem">Mengimpor…</span>';
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
      if (statusEl) statusEl.innerHTML = '<span style="color:var(--green);font-size:.75rem"><i class="fa-solid fa-check"></i> Imported</span>';
      document.getElementById(`row-${idx}`)?.classList.add('imported');
    });

    progressFill.style.width = '100%';
    progressLabel.textContent = `Selesai — ${done} token berhasil diimpor ke vault.`;
  } catch (batchErr) {
    // Fallback to item-by-item if batch endpoint reports error
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
        statusEl.innerHTML = '<span style="color:var(--green);font-size:.75rem"><i class="fa-solid fa-check"></i> Imported</span>';
        document.getElementById(`row-${idx}`)?.classList.add('imported');
        done++;
      } catch (err) {
        statusEl.innerHTML = `<span style="color:var(--red);font-size:.75rem" title="${esc(err.message)}"><i class="fa-solid fa-xmark"></i> Gagal</span>`;
        failed++;
      }
      progressFill.style.width = `${Math.round(((done + failed) / toImport.length) * 100)}%`;
      progressLabel.textContent = `Diimpor ${done} dari ${toImport.length}…`;
    }
  }

  progressLabel.textContent = `Selesai — ${done} token diimpor${failed ? `, ${failed} gagal` : ''}.`;
  btn.disabled = false;
  btn.innerHTML = '<i class="fa-solid fa-check"></i> Impor Selesai';

  if (done > 0) {
    toast(`✓ Berhasil mengimpor ${done} token ke TOTPVault.`, 'success');
    setTimeout(() => { window.location.href = '/tokens'; }, 1600);
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────

function esc(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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
  setTimeout(() => { el.style.animation = 'toastOut .2s ease forwards'; setTimeout(() => el.remove(), 200); }, 4000);
}

function toggleSidebarMobile() {
  const sb = document.getElementById('app-sidebar');
  const bd = document.getElementById('sidebar-backdrop');
  const isOpen = sb.classList.toggle('mobile-open');
  if (bd) bd.classList.toggle('mobile-open', isOpen);
  document.body.style.overflow = isOpen ? 'hidden' : '';
  document.body.style.overflowX = 'hidden';
}

updateSummary();
</script>
</body>
</html>
