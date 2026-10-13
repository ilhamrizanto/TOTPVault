<?php
$pageTitle = 'Akses Ditolak — TOTPVault';
require __DIR__ . '/layout.php';

$email = htmlspecialchars($email ?? $_GET['email'] ?? '');
$message = htmlspecialchars($message ?? $_GET['message'] ?? '');
?>

<style>
.unauth-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: var(--canvas);
  padding: 2rem 1.5rem;
  position: relative;
  overflow: hidden;
}

.unauth-glow {
  position: absolute;
  top: 30%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 500px;
  height: 350px;
  background: radial-gradient(ellipse at center, rgba(239, 68, 68, 0.08) 0%, rgba(5, 5, 8, 0) 70%);
  pointer-events: none;
}

.unauth-card {
  background: #0c0d12;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.9);
  width: 100%;
  max-width: 520px;
  padding: 2.75rem 2.25rem;
  text-align: center;
  position: relative;
  z-index: 2;
  animation: slideUp .22s cubic-bezier(0.16, 1, 0.3, 1);
}

.unauth-icon-badge {
  width: 68px;
  height: 68px;
  border-radius: 50%;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #ef4444;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.85rem;
  margin-bottom: 1.25rem;
  box-shadow: 0 0 30px rgba(239, 68, 68, 0.25);
}

.unauth-title {
  font-size: 1.625rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #ffffff;
  line-height: 1.2;
  margin-bottom: .5rem;
}

.unauth-desc {
  font-size: .875rem;
  color: #7a9c95;
  line-height: 1.55;
  margin-bottom: 1.5rem;
}

.unauth-email-box {
  background: #07080d;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: .875rem 1.125rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .75rem;
  margin-bottom: 1.5rem;
  text-align: left;
}

.email-label {
  font-size: .6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #64748b;
}

.email-val {
  font-size: .9375rem;
  font-weight: 700;
  color: #ffffff;
  word-break: break-all;
}

.unauth-hint-card {
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 12px;
  padding: .875rem 1rem;
  text-align: left;
  font-size: .8125rem;
  color: #fca5a5;
  line-height: 1.5;
  margin-bottom: 1.75rem;
  display: flex;
  gap: .625rem;
  align-items: flex-start;
}

.unauth-actions {
  display: flex;
  flex-direction: column;
  gap: .625rem;
}

.unauth-footer {
  margin-top: 2rem;
  text-align: center;
  font-size: .75rem;
  color: #475569;
  position: relative;
  z-index: 2;
}
</style>

<div class="unauth-shell">
  <div class="unauth-glow"></div>

  <div class="unauth-card">
    <!-- Brand -->
    <div style="display:inline-flex;align-items:center;gap:.6rem;margin-bottom:1.25rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);padding:.4rem .9rem;border-radius:9999px">
      <img src="/logo.png" alt="TOTPVault Logo" style="width:22px;height:22px;object-fit:contain">
      <span style="font-weight:700;font-size:.875rem;letter-spacing:-0.01em;color:#ffffff">TOTPVault</span>
    </div>

    <!-- Icon -->
    <div class="unauth-icon-badge">
      <i class="fa-solid fa-shield-xmark"></i>
    </div>

    <!-- Title & Desc -->
    <h1 class="unauth-title">Akses Ditolak: Belum Terdaftar</h1>
    <p class="unauth-desc">
      Akun Google yang Anda gunakan belum terdaftar dalam daftar otorisasi (whitelist) instance TOTPVault ini.
    </p>

    <!-- Email Box -->
    <?php if ($email): ?>
    <div class="unauth-email-box">
      <div>
        <div class="email-label">Akun Google Terdeteksi</div>
        <div class="email-val"><?= $email ?></div>
      </div>
      <span class="badge badge-red" style="font-size:.6875rem;white-space:nowrap">
        Belum Diotorisasi
      </span>
    </div>
    <?php endif; ?>

    <!-- Security Advisory Box -->
    <div class="unauth-hint-card">
      <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;flex-shrink:0;color:#ef4444"></i>
      <div>
        <strong>Akses Sistem Dibatasi:</strong> Hanya akun yang telah disetujui Administrator yang diizinkan mengakses brankas token. Hubungi admin tim Anda untuk mengotorisasi email ini.
      </div>
    </div>

    <!-- Actions -->
    <div class="unauth-actions">
      <a href="/auth/login/google" class="btn btn-primary" style="padding:.75rem 1.25rem;font-size:.9375rem;font-weight:700">
        <i class="fa-brands fa-google"></i>
        <span>Login dengan Akun Google Lain</span>
      </a>
      <a href="/" class="btn btn-secondary" style="padding:.65rem 1.25rem;font-size:.875rem">
        <i class="fa-solid fa-house"></i>
        <span>Kembali ke Beranda</span>
      </a>
    </div>
  </div>

  <div class="unauth-footer">
    TOTPVault &middot; Internal 2FA Identity Vault &middot; Keamanan Terisolasi
  </div>
</div>
</body>
</html>
