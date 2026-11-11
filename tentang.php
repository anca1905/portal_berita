<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tentang Kami & Susunan Redaksi – ' . APP_NAME;
$pageDescription = 'Profil, visi misi, dan susunan dewan redaksi portal berita ' . APP_NAME . '.';
include __DIR__ . '/includes/header.php';
?>

<div style="background:var(--bg-secondary);padding:24px 0;border-bottom:1px solid var(--border-light)">
  <div class="container">
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="width:4px;height:32px;background:var(--primary-blue);border-radius:2px;flex-shrink:0"></div>
      <div>
        <h1 style="font-size:24px;font-weight:800;color:var(--text-primary);margin-bottom:2px">Tentang Kami</h1>
        <div style="font-size:13px;color:var(--text-muted)">Mengenal lebih dekat <?= e(APP_NAME) ?></div>
      </div>
    </div>
  </div>
</div>

<main class="section-padding">
  <div class="container" style="max-width:960px;">
    <div style="background:var(--bg-primary);border-radius:var(--radius-lg);border:1px solid var(--border-light);padding:36px;box-shadow:var(--shadow-sm);margin-bottom:32px;">
      <h2 style="font-size:22px;font-weight:800;color:var(--text-primary);margin-bottom:16px;">Visi & Misi</h2>
      <p style="font-size:15px;line-height:1.75;color:var(--text-secondary);margin-bottom:16px;">
        <strong><?= e(APP_NAME) ?></strong> (<em><?= e(APP_TAGLINE) ?></em>) adalah media siber independen yang berkomitmen menghadirkan informasi yang akurat, berimbang, mencerahkan, dan terpercaya bagi masyarakat luas di seluruh penjuru tanah air.
      </p>
      <p style="font-size:15px;line-height:1.75;color:var(--text-secondary);margin-bottom:24px;">
        Kami menjunjung tinggi Kode Etik Jurnalistik (KEJ) dan Undang-Undang Pokok Pers No. 40 Tahun 1999 serta berpedoman pada Pedoman Pemberitaan Media Siber Dewan Pers dalam setiap proses peliputan dan penerbitan berita.
      </p>

      <hr style="border:none;border-top:1px solid var(--border-light);margin:28px 0;">

      <h2 style="font-size:22px;font-weight:800;color:var(--text-primary);margin-bottom:20px;">Susunan Dewan Redaksi & Manajemen</h2>
      
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:20px;">
        <div style="background:var(--bg-secondary);padding:18px;border-radius:var(--radius-md);border-left:4px solid var(--primary-blue);">
          <div style="font-size:12px;font-weight:700;color:var(--primary-blue);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Pemimpin Umum / Penanggung Jawab</div>
          <div style="font-size:16px;font-weight:700;color:var(--text-primary);">Muh. Rahwan</div>
        </div>

        <div style="background:var(--bg-secondary);padding:18px;border-radius:var(--radius-md);border-left:4px solid var(--primary-blue);">
          <div style="font-size:12px;font-weight:700;color:var(--primary-blue);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Pemimpin Redaksi</div>
          <div style="font-size:16px;font-weight:700;color:var(--text-primary);">Administrator Redaksi</div>
        </div>

        <div style="background:var(--bg-secondary);padding:18px;border-radius:var(--radius-md);border-left:4px solid var(--primary-blue);">
          <div style="font-size:12px;font-weight:700;color:var(--primary-blue);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Redaktur Pelaksana</div>
          <div style="font-size:16px;font-weight:700;color:var(--text-primary);">Muh Arsyad Ramsi</div>
        </div>

        <div style="background:var(--bg-secondary);padding:18px;border-radius:var(--radius-md);border-left:4px solid var(--primary-blue);">
          <div style="font-size:12px;font-weight:700;color:var(--primary-blue);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Tim Liputan & Multimedia</div>
          <div style="font-size:16px;font-weight:700;color:var(--text-primary);">Aril & Tim Reporter Daerah</div>
        </div>
      </div>

      <hr style="border:none;border-top:1px solid var(--border-light);margin:28px 0;">

      <h2 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:14px;">Integritas Wartawan Kami</h2>
      <p style="font-size:14px;line-height:1.7;color:var(--text-secondary);">
        Setiap jurnalis <strong><?= e(APP_NAME) ?></strong> dibekali kartu identitas resmi (Kartu Pers) yang masih berlaku dan namanya tercantum dalam Boks Redaksi. Wartawan kami dilarang keras meminta atau menerima gratifikasi dalam bentuk apapun dari narasumber terkait pemberitaan.
      </p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
