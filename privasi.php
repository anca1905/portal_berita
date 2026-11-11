<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Kebijakan Privasi & Disclaimer – ' . APP_NAME;
$pageDescription = 'Kebijakan privasi dan syarat penggunaan layanan portal berita ' . APP_NAME . '.';
include __DIR__ . '/includes/header.php';
?>

<div style="background:var(--bg-secondary);padding:24px 0;border-bottom:1px solid var(--border-light)">
  <div class="container">
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="width:4px;height:32px;background:var(--primary-blue);border-radius:2px;flex-shrink:0"></div>
      <div>
        <h1 style="font-size:24px;font-weight:800;color:var(--text-primary);margin-bottom:2px">Kebijakan Privasi & Ketentuan</h1>
        <div style="font-size:13px;color:var(--text-muted)">Perlindungan Data Pribadi Pengguna <?= e(APP_NAME) ?></div>
      </div>
    </div>
  </div>
</div>

<main class="section-padding">
  <div class="container" style="max-width:960px;">
    <div style="background:var(--bg-primary);border-radius:var(--radius-lg);border:1px solid var(--border-light);padding:36px;box-shadow:var(--shadow-sm);font-size:15px;line-height:1.75;color:var(--text-secondary);">
      <h2 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:14px;">1. Pengumpulan Informasi</h2>
      <p style="margin-bottom:16px;">
        Kami menghargai privasi setiap pembaca. Saat Anda mengunjungi <strong><?= e(APP_NAME) ?></strong>, kami dapat mengumpulkan informasi teknis seperti alamat IP, jenis peramban (browser), dan halaman yang diakses untuk keperluan analisis performa dan peningkatan pengalaman pembaca.
      </p>

      <h2 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:14px;">2. Penggunaan Data & Cookies</h2>
      <p style="margin-bottom:16px;">
        Situs ini menggunakan <em>cookies</em> untuk menyimpan preferensi pengguna (seperti mode tampilan dan riwayat baca) serta memfasilitasi penayangan iklan yang relevan. Anda berhak mematikan cookies melalui pengaturan browser Anda kapan saja.
      </p>

      <h2 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:14px;">3. Kolom Komentar & Partisipasi Publik</h2>
      <p style="margin-bottom:16px;">
        Nama atau informasi yang Anda cantumkan saat mengirim komentar pada artikel akan tampil secara publik. Harap tidak menyertakan informasi sensitif (seperti nomor telepon pribadi, nomor rekening, atau alamat rumah) pada kolom komentar.
      </p>

      <h2 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:14px;">4. Disclaimer / Penyangkalan</h2>
      <p style="margin-bottom:16px;">
        Seluruh materi berita, analisa, data pasar, dan informasi lain yang disajikan di <strong><?= e(APP_NAME) ?></strong> ditujukan untuk konsumsi informasi umum. Kami berusaha menyajikan informasi seakurat mungkin, namun redaksi tidak bertanggung jawab atas kerugian finansial atau materi akibat keputusan yang diambil semata-mata atas dasar rujukan berita.
      </p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
