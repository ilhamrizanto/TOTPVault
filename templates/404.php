<?php
$pageTitle = '404 Tidak Ditemukan — TOTPVault';
require __DIR__ . '/layout.php';
?>

<div style="min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem 1.5rem;background:var(--canvas);position:relative;overflow:hidden">
  <!-- Subtle ambient cyber glow -->
  <div style="position:absolute;top:20%;left:50%;transform:translate(-50%,-50%);width:500px;height:300px;background:radial-gradient(ellipse at center, rgba(16,185,129,0.08) 0%, rgba(5,5,8,0) 70%);pointer-events:none"></div>

  <div style="background:#0c0d12;border:1px solid rgba(255,255,255,0.08);border-radius:24px;box-shadow:0 20px 60px rgba(0,0,0,0.85);max-width:480px;width:100%;padding:3rem 2.25rem;text-align:center;position:relative;z-index:2">
    <div style="display:inline-flex;align-items:center;gap:.6rem;margin-bottom:1.5rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);padding:.4rem .9rem;border-radius:9999px">
      <img src="/logo.png" alt="TOTPVault Logo" style="width:22px;height:22px;object-fit:contain">
      <span style="font-weight:700;font-size:.875rem;letter-spacing:-0.01em;color:#ffffff">TOTPVault</span>
    </div>
    <div style="font-size:5rem;font-weight:900;letter-spacing:-.06em;color:var(--emerald);line-height:1;margin-bottom:1rem;font-family:'JetBrains Mono',monospace;text-shadow:0 0 30px rgba(16,185,129,0.4)">
      404
    </div>
    <h1 style="font-size:1.5rem;font-weight:800;color:#ffffff;letter-spacing:-.02em;margin-bottom:.5rem">
      Halaman Tidak Ditemukan
    </h1>
    <p style="color:#7a9c95;font-size:.875rem;line-height:1.6;margin-bottom:2rem">
      Tautan yang Anda tuju mungkin sudah dipindahkan atau tidak tersedia di server TOTPVault.
    </p>
    <div style="display:flex;gap:.75rem;justify-content:center">
      <a href="<?= !empty($user) ? '/tokens' : '/' ?>" class="btn btn-primary" style="padding:.65rem 1.4rem;font-weight:700">
        <i class="fa-solid fa-arrow-left"></i>
        <span><?= !empty($user) ? 'Kembali ke Tokens' : 'Kembali ke Beranda' ?></span>
      </a>
    </div>
  </div>
  <div style="margin-top:2rem;font-size:.75rem;color:#475569;z-index:2">
    TOTPVault &copy; 2026 &middot; Protected 2FA Environment
  </div>
</div>
</body>
</html>
