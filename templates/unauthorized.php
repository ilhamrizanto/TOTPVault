<?php
// templates/unauthorized.php — Apple-Grade Access Restricted Sheet
$pageTitle = 'Akses Dibatasi — TOTPVault';
require __DIR__ . '/layout.php';

$email = htmlspecialchars($email ?? $_GET['email'] ?? '');
$message = htmlspecialchars($message ?? $_GET['message'] ?? '');
?>

<style>
.unauth-shell {
  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: var(--canvas);
  padding: 2rem 1.25rem;
  position: relative;
}

.unauth-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: var(--shadow-lg), var(--specular-top);
  width: 100%;
  max-width: 440px;
  padding: 2.25rem 2rem 1.875rem;
  text-align: center;
  position: relative;
  animation: modalEnter 220ms var(--ease-out);
}

.unauth-brand {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  margin-bottom: 1.5rem;
  padding: .3rem .75rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border);
  border-radius: var(--r-pill);
}

.unauth-icon-box {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: var(--red-l);
  border: 1px solid var(--red-border);
  color: var(--red-light);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  margin-bottom: 1.125rem;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.unauth-title {
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: -0.025em;
  color: var(--ink);
  line-height: 1.25;
  margin-bottom: .5rem;
}

.unauth-desc {
  font-size: .84375rem;
  color: var(--ink-3);
  line-height: 1.55;
  margin-bottom: 1.35rem;
}

.unauth-identity {
  background: var(--canvas-sub);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: .75rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .75rem;
  margin-bottom: 1rem;
  text-align: left;
}

.unauth-identity-label {
  font-size: .6875rem;
  font-weight: 600;
  color: var(--ink-4);
  margin-bottom: .15rem;
}

.unauth-identity-val {
  font-size: .84375rem;
  font-weight: 600;
  color: var(--ink);
  word-break: break-all;
}

.unauth-note {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border-sub);
  border-radius: 10px;
  padding: .75rem .875rem;
  text-align: left;
  font-size: .78125rem;
  color: var(--ink-3);
  line-height: 1.5;
  margin-bottom: 1.5rem;
  display: flex;
  gap: .625rem;
  align-items: flex-start;
}

.unauth-actions {
  display: flex;
  flex-direction: column;
  gap: .5rem;
}

.unauth-footer {
  margin-top: 1.5rem;
  font-size: .75rem;
  color: var(--ink-4);
}
</style>

<div class="unauth-shell">
  <div class="unauth-card">
    <div>
      <div class="unauth-brand">
        <img src="/logo.png" alt="TOTPVault" style="width:18px;height:18px;object-fit:contain">
        <span style="font-weight:600;font-size:.78125rem;color:var(--ink-2)">TOTPVault</span>
      </div>
    </div>

    <div class="unauth-icon-box">
      <i class="fa-solid fa-lock"></i>
    </div>

    <h1 class="unauth-title">Akses Belum Diotorisasi</h1>
    <p class="unauth-desc">
      Identitas yang Anda gunakan belum terdaftar pada daftar izin akses (whitelist) di brankas ini.
    </p>

    <?php if ($email): ?>
    <div class="unauth-identity">
      <div style="min-width:0">
        <div class="unauth-identity-label">Akun Terdeteksi</div>
        <div class="unauth-identity-val mono"><?= $email ?></div>
      </div>
      <span class="badge badge-red" style="flex-shrink:0">Ditolak</span>
    </div>
    <?php endif; ?>

    <div class="unauth-note">
      <i class="fa-solid fa-shield-halved" style="margin-top:2px;color:var(--ink-3);flex-shrink:0"></i>
      <div>
        Hubungi Administrator untuk menambahkan alamat email Anda ke menu <strong>Whitelist Akun</strong> sebelum masuk kembali.
      </div>
    </div>

    <div class="unauth-actions">
      <a href="/auth/login/google" class="btn btn-primary btn-lg" style="width:100%">
        <i class="fa-brands fa-google"></i>
        <span>Gunakan Akun Google Lain</span>
      </a>
      <a href="/" class="btn btn-secondary btn-lg" style="width:100%">
        <span>Kembali ke Beranda</span>
      </a>
    </div>
  </div>

  <div class="unauth-footer">
    TOTPVault &middot; Zero-Trust 2FA Workspace
  </div>
</div>
</body>
</html>
