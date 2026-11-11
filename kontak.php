<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$successMsg = '';
$errorMsg = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['send_contact'])) {
    if (!empty($_POST['contact_hp'])) {
        $successMsg = 'Pesan Anda telah berhasil dikirim.';
    } else {
        verifyCsrf();
        $senderName  = sanitize($_POST['name'] ?? '');
        $senderEmail = sanitize($_POST['email'] ?? '');
        $subject     = sanitize($_POST['subject'] ?? '');
        $message     = sanitize($_POST['message'] ?? '');

        if (!$senderName || !$senderEmail || !$message) {
            $errorMsg = 'Mohon lengkapi seluruh formulir.';
        } elseif (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Format alamat email tidak valid.';
        } else {
            // Sukses diterima redaksi
            $successMsg = 'Terima kasih, pesan Anda telah berhasil dikirim ke Redaksi ' . APP_NAME . '. Kami akan segera menindaklanjutinya.';
        }
    }
}

$pageTitle = 'Hubungi Kami & Layanan Iklan – ' . APP_NAME;
$pageDescription = 'Kontak redaksi, kerjasama iklan, dan alamat kantor ' . APP_NAME . '.';
include __DIR__ . '/includes/header.php';
?>

<div style="background:var(--bg-secondary);padding:24px 0;border-bottom:1px solid var(--border-light)">
  <div class="container">
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="width:4px;height:32px;background:var(--primary-blue);border-radius:2px;flex-shrink:0"></div>
      <div>
        <h1 style="font-size:24px;font-weight:800;color:var(--text-primary);margin-bottom:2px">Hubungi Redaksi</h1>
        <div style="font-size:13px;color:var(--text-muted)">Kontak, Siaran Pers & Kerjasama Iklan</div>
      </div>
    </div>
  </div>
</div>

<main class="section-padding">
  <div class="container" style="max-width:1040px;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:28px;">
      
      <!-- Info Kontak -->
      <div style="background:var(--bg-primary);border-radius:var(--radius-lg);border:1px solid var(--border-light);padding:32px;box-shadow:var(--shadow-sm);">
        <h2 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:18px;">Kantor Redaksi</h2>
        
        <div style="display:flex;flex-direction:column;gap:18px;font-size:14px;color:var(--text-secondary);">
          <div style="display:flex;gap:14px;align-items:flex-start;">
            <div style="width:36px;height:36px;border-radius:8px;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;color:var(--primary-blue);flex-shrink:0;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
            </div>
            <div>
              <strong style="color:var(--text-primary);">Gedung Redaksi <?= e(APP_NAME) ?></strong><br>
              Kendari, Sulawesi Tenggara, Indonesia
            </div>
          </div>

          <div style="display:flex;gap:14px;align-items:flex-start;">
            <div style="width:36px;height:36px;border-radius:8px;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;color:var(--primary-blue);flex-shrink:0;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </div>
            <div>
              <strong style="color:var(--text-primary);">Email Redaksi & Siaran Pers:</strong><br>
              <a href="mailto:redaksi@xyronnews.com" style="color:var(--primary-blue);font-weight:600;">redaksi@xyronnews.com</a>
            </div>
          </div>

          <div style="display:flex;gap:14px;align-items:flex-start;">
            <div style="width:36px;height:36px;border-radius:8px;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;color:var(--primary-blue);flex-shrink:0;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div>
              <strong style="color:var(--text-primary);">Kerjasama Bisnis & Iklan:</strong><br>
              <a href="<?= APP_URL ?>/pasang-iklan.php" style="color:var(--primary-blue);font-weight:700;">Lihat Daftar Tarif Iklan (Rate Card) →</a><br>
              <span style="font-size:12px;color:var(--text-muted);">Email: <?= e(getAdSetting('ad_email', 'iklan@xyronnews.com')) ?></span>
            </div>
          </div>

          <div style="display:flex;gap:14px;align-items:flex-start;">
            <div style="width:36px;height:36px;border-radius:8px;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;color:var(--primary-blue);flex-shrink:0;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
              <strong style="color:var(--text-primary);">Jam Operasional:</strong><br>
              Senin – Minggu: 08.00 – 22.00 WITA<br>
              (Pemberitaan 24 Jam Non-Stop)
            </div>
          </div>
        </div>
      </div>

      <!-- Form Kontak -->
      <div style="background:var(--bg-primary);border-radius:var(--radius-lg);border:1px solid var(--border-light);padding:32px;box-shadow:var(--shadow-sm);">
        <h2 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:18px;">Kirim Pesan ke Redaksi</h2>

        <?php if ($successMsg): ?>
        <div style="background:#D1FAE5;border:1px solid #6EE7B7;color:#065F46;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:14px;display:flex;align-items:center;gap:10px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <span><?= e($successMsg) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
        <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:14px;display:flex;align-items:center;gap:10px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <span><?= e($errorMsg) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <div style="display:none;" aria-hidden="true">
            <input type="text" name="contact_hp" tabindex="-1" autocomplete="off">
          </div>

          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Nama Lengkap *</label>
            <input type="text" name="name" required class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--border-light);border-radius:8px;font-size:14px;box-sizing:border-box;">
          </div>

          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Email Anda *</label>
            <input type="email" name="email" required class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--border-light);border-radius:8px;font-size:14px;box-sizing:border-box;">
          </div>

          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Subjek Pesan *</label>
            <input type="text" name="subject" required class="form-control" placeholder="Contoh: Ralat Berita / Pemasangan Iklan" style="width:100%;padding:10px 14px;border:1px solid var(--border-light);border-radius:8px;font-size:14px;box-sizing:border-box;">
          </div>

          <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">Pesan Anda *</label>
            <textarea name="message" required rows="4" class="form-control" style="width:100%;padding:10px 14px;border:1px solid var(--border-light);border-radius:8px;font-size:14px;box-sizing:border-box;resize:vertical;"></textarea>
          </div>

          <button type="submit" name="send_contact" style="background:var(--primary-blue);color:#fff;font-weight:700;font-size:14px;padding:12px 24px;border-radius:8px;border:none;cursor:pointer;width:100%;">
            Kirim Pesan Sekarang →
          </button>
        </form>
      </div>

    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
