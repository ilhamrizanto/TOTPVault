<?php
// templates/404.php — Apple-Grade Not Found View
$pageTitle = '404 Tidak Ditemukan — TOTPVault';
require __DIR__ . '/layout.php';
?>

<div style="min-height:100vh;min-height:100dvh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem 1.25rem;background:var(--canvas)">
  <div style="background:var(--card);border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow-lg),var(--specular-top);max-width:420px;width:100%;padding:2.5rem 2rem 2rem;text-align:center;animation:modalEnter 220ms var(--ease-out)">
    <div style="display:inline-flex;align-items:center;gap:.5rem;margin-bottom:1.5rem;background:rgba(255,255,255,0.03);border:1px solid var(--border);padding:.3rem .75rem;border-radius:999px">
      <img src="/logo.png" alt="TOTPVault" style="width:18px;height:18px;object-fit:contain">
      <span style="font-weight:600;font-size:.78125rem;color:var(--ink-2)">TOTPVault</span>
    </div>

    <div class="mono" style="font-size:3.25rem;font-weight:700;letter-spacing:-0.05em;color:var(--ink);line-height:1;margin-bottom:.75rem">
      404
    </div>

    <h1 style="font-size:1.25rem;font-weight:700;color:var(--ink);letter-spacing:-0.02em;margin-bottom:.5rem">
      Halaman Tidak Ditemukan
    </h1>

    <p style="color:var(--ink-3);font-size:.84375rem;line-height:1.55;margin-bottom:1.75rem">
      Alamat yang Anda tuju tidak tersedia atau telah dipindahkan dari ruang kerja TOTPVault.
    </p>

    <a href="<?= !empty($user) ? '/tokens' : '/' ?>" class="btn btn-primary btn-lg" style="width:100%">
      <i class="fa-solid fa-arrow-left" style="font-size:.75rem"></i>
      <span><?= !empty($user) ? 'Kembali ke Brankas' : 'Kembali ke Beranda' ?></span>
    </a>
  </div>
</div>
</body>
</html>
