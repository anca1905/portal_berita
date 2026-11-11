<?php
// XyronNews – Halaman Layanan Iklan, Advertorial & Media Kit
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$successMsg = '';
$errorMsg   = '';

$priceAdvertorial = getAdSetting('ad_price_advertorial', '250.000');
$priceHeader      = getAdSetting('ad_price_header', '500.000');
$priceSidebar     = getAdSetting('ad_price_sidebar', '300.000');
$adPhone          = getAdWhatsAppNumber();
$adEmail          = getAdSetting('ad_email', 'iklan@xyronnews.com');

// Handle submission formulir penawaran
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_inquiry'])) {
    verifyCsrf();
    $name    = sanitize($_POST['name'] ?? '');
    $company = sanitize($_POST['company'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $package = sanitize($_POST['package'] ?? '');
    $notes   = sanitize($_POST['notes'] ?? '');

    if (!$name || !$phone || !$package) {
        $errorMsg = 'Mohon lengkapi formulir Nama, No. WhatsApp, dan Pilihan Paket.';
    } else {
        $successMsg = 'Terima kasih Bapak/Ibu ' . e($name) . '. Permintaan Anda telah kami terima. Tim Pemasaran kami akan segera menghubungi nomor WhatsApp Anda.';
    }
}

$pageTitle       = 'Tarif Iklan, Advertorial & Media Kit – ' . APP_NAME;
$pageDescription = 'Pasang iklan banner mandiri, siaran pers (advertorial), dan kemitraan publikasi instansi di portal berita ' . APP_NAME . '. Jangkau ribuan pembaca setia setiap hari.';
include __DIR__ . '/includes/header.php';
?>

<!-- HERO SECTION -->
<section class="ratecard-hero">
  <div class="container">
    <div class="ratecard-hero-inner">
      <div class="ratecard-hero-tag">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        RATE CARD & MEDIA KIT <?= date('Y') ?>
      </div>
      <h1 class="ratecard-hero-title">Tingkatkan Reputasi & Jangkauan Brand Anda Bersama <?= e(APP_NAME) ?></h1>
      <p class="ratecard-hero-desc">
        Solusi publikasi berita advertorial terindeks Google, sewa slot banner mandiri, dan kemitraan media partner untuk UMKM, instansi pemerintah, dan korporasi dengan tarif transparan tanpa perantara.
      </p>
    </div>
  </div>
</section>

<!-- MAIN CONTAINER -->
<main class="section-padding" style="padding-top:0">
  <div class="container">

    <!-- STATS HIGHLIGHTS -->
    <div class="ratecard-stats-grid">
      <div class="ratecard-stat-card">
        <div class="ratecard-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
          <div class="ratecard-stat-val">150.000+</div>
          <div class="ratecard-stat-lbl">Estimasi Pembaca Bulanan</div>
        </div>
      </div>

      <div class="ratecard-stat-card">
        <div class="ratecard-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
          <div class="ratecard-stat-val">&lt; 24 Jam</div>
          <div class="ratecard-stat-lbl">Proses Tayang Berita Kilat</div>
        </div>
      </div>

      <div class="ratecard-stat-card">
        <div class="ratecard-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        </div>
        <div>
          <div class="ratecard-stat-val">DoFollow SEO</div>
          <div class="ratecard-stat-lbl">Permanent Backlink & Otoritas</div>
        </div>
      </div>

      <div class="ratecard-stat-card">
        <div class="ratecard-stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <div>
          <div class="ratecard-stat-val">100% Mandiri</div>
          <div class="ratecard-stat-lbl">Direct Deal Tanpa Pihak Ketiga</div>
        </div>
      </div>
    </div>

    <?php if ($successMsg): ?>
    <div style="background:#e6fffa;border:1px solid #38b2ac;color:#234e52;padding:16px 20px;border-radius:var(--radius-md);margin-bottom:28px;font-size:14px;display:flex;align-items:center;gap:12px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <div><?= e($successMsg) ?></div>
    </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
    <div style="background:#fff5f5;border:1px solid #e53e3e;color:#742a2a;padding:16px 20px;border-radius:var(--radius-md);margin-bottom:28px;font-size:14px;display:flex;align-items:center;gap:12px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div><?= e($errorMsg) ?></div>
    </div>
    <?php endif; ?>

    <!-- SECTION TITLE -->
    <div class="section-header" style="margin-bottom:28px;">
      <h2 class="section-title">DAFTAR PAKET <span>TARIF IKLAN</span></h2>
      <span class="section-more">Pilih paket sesuai kebutuhan kampanye Anda</span>
    </div>

    <!-- PRICING GRID -->
    <div class="pricing-grid">

      <!-- Paket 1: Advertorial -->
      <div class="pricing-card featured">
        <div class="pricing-featured-badge">PALING DIMINATI</div>
        <div class="pricing-card-header">
          <div class="pricing-plan-name">Rilis Berita / Advertorial</div>
          <div class="pricing-plan-desc">Publikasi siaran pers, artikel promosi profil usaha, atau ulasan produk.</div>
          <div class="pricing-price-wrap">
            <span class="pricing-currency">Rp</span>
            <span class="pricing-amount"><?= e($priceAdvertorial) ?></span>
            <span class="pricing-period">/ artikel</span>
          </div>
        </div>

        <ul class="pricing-features-list">
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span><strong>Tayang Permanen</strong> (tidak pernah dihapus)</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Maksimal <strong>2 Link DoFollow</strong> (Bagus untuk SEO)</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Maksimal 3 Foto / Infografis HD</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Terindeks di Google Search & Google News</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Bisa draf sendiri atau dibantu editing</span>
          </li>
        </ul>

        <a href="<?= e(getAdWhatsAppLink('Advertorial / Siaran Pers (Rp ' . $priceAdvertorial . ')')) ?>" target="_blank" rel="noopener noreferrer" class="pricing-btn-wa">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
          Pesan via WhatsApp
        </a>
      </div>

      <!-- Paket 2: Banner Header 728x90 -->
      <div class="pricing-card">
        <div class="pricing-card-header">
          <div class="pricing-plan-name">Banner Header (728x90)</div>
          <div class="pricing-plan-desc">Posisi puncak teratas halaman, menjamin visibilitas pandangan pertama.</div>
          <div class="pricing-price-wrap">
            <span class="pricing-currency">Rp</span>
            <span class="pricing-amount"><?= e($priceHeader) ?></span>
            <span class="pricing-period">/ bulan</span>
          </div>
        </div>

        <ul class="pricing-features-list">
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Tampil di <strong>Beranda & Semua Halaman</strong></span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Ukuran 728x90 px (Responsif Mobile)</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Tautan langsung ke WhatsApp / Website / Shopee</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Bisa ganti materi banner 1x sebulan</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Slot Eksklusif (terbatas)</span>
          </li>
        </ul>

        <a href="<?= e(getAdWhatsAppLink('Banner Header 728x90 (Rp ' . $priceHeader . '/bln)')) ?>" target="_blank" rel="noopener noreferrer" class="pricing-btn-wa">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
          Sewa Slot Header
        </a>
      </div>

      <!-- Paket 3: Banner Sidebar 300x250 -->
      <div class="pricing-card">
        <div class="pricing-card-header">
          <div class="pricing-plan-name">Banner Sidebar (300x250)</div>
          <div class="pricing-plan-desc">Berdampingan dengan berita terpopuler & widget aktif, CTR tinggi.</div>
          <div class="pricing-price-wrap">
            <span class="pricing-currency">Rp</span>
            <span class="pricing-amount"><?= e($priceSidebar) ?></span>
            <span class="pricing-period">/ bulan</span>
          </div>
        </div>

        <ul class="pricing-features-list">
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Tampil di <strong>Sidebar Beranda & Seluruh Artikel</strong></span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Ukuran Medium Rectangle 300x250 px</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Format Format JPG, PNG, GIF Animasi, WebP</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Cocok untuk Promo Resto, Klinik, Bimbel, Properti</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Bantuan desain dasar jika belum punya materi</span>
          </li>
        </ul>

        <a href="<?= e(getAdWhatsAppLink('Banner Sidebar 300x250 (Rp ' . $priceSidebar . '/bln)')) ?>" target="_blank" rel="noopener noreferrer" class="pricing-btn-wa">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
          Sewa Slot Sidebar
        </a>
      </div>

      <!-- Paket 4: Kemitraan Instansi / Media Partner -->
      <div class="pricing-card">
        <div class="pricing-card-header">
          <div class="pricing-plan-name">Kerjasama Instansi & Media Partner</div>
          <div class="pricing-plan-desc">Paket publikasi kegiatan kedinasan, Humas Pemda/DPRD, dan event kampus/komunitas.</div>
          <div class="pricing-price-wrap">
            <span class="pricing-currency">Mulai</span>
            <span class="pricing-amount">MoU</span>
            <span class="pricing-period">/ kontrak</span>
          </div>
        </div>

        <ul class="pricing-features-list">
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Publikasi liputan kegiatan dinas / instansi</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Pencantuman Logo di Widget Mitra Resmi</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Dukungan Laporan Pertanggungjawaban (LPJ) & Kliping</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Fleksibel: Paket Kontrak Bulanan atau Tahunan</span>
          </li>
          <li class="pricing-feature-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Faktur & Kwitansi Resmi Perusahaan Pers</span>
          </li>
        </ul>

        <a href="<?= e(getAdWhatsAppLink('Kemitraan Instansi / Media Partner Event')) ?>" target="_blank" rel="noopener noreferrer" class="pricing-btn-secondary">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          Konsultasi MoU Kerjasama
        </a>
      </div>

    </div><!-- end .pricing-grid -->

    <!-- FORM PENGAJUAN & QUICK WHATSAPP INQUIRY -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:28px;margin-bottom:44px;align-items:flex-start;">

      <!-- Form Box -->
      <div class="ratecard-form-wrap">
        <h2 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:8px;">Formulir Pengajuan Iklan / Siaran Pers</h2>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px;line-height:1.4;">
          Isi detail di bawah ini atau klik langsung tombol WhatsApp untuk respon instan dari staf periklanan kami.
        </p>

        <form method="POST" id="adInquiryForm">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="submit_inquiry" value="1">

          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Nama Lengkap / PIC <span style="color:var(--accent-red)">*</span></label>
            <input type="text" name="name" id="inq_name" class="form-control" placeholder="Contoh: Budi Santoso" required style="width:100%;padding:10px 14px;border:1px solid var(--border-medium);border-radius:var(--radius-sm);background:var(--bg-secondary);color:var(--text-primary);font-size:14px;">
          </div>

          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Nama Bisnis / Brand / Instansi</label>
            <input type="text" name="company" id="inq_company" class="form-control" placeholder="Contoh: PT Sumber Rezeki / Diskominfo" style="width:100%;padding:10px 14px;border:1px solid var(--border-medium);border-radius:var(--radius-sm);background:var(--bg-secondary);color:var(--text-primary);font-size:14px;">
          </div>

          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Nomor WhatsApp Aktif <span style="color:var(--accent-red)">*</span></label>
            <input type="tel" name="phone" id="inq_phone" class="form-control" placeholder="08xxxxxxxxxx" required style="width:100%;padding:10px 14px;border:1px solid var(--border-medium);border-radius:var(--radius-sm);background:var(--bg-secondary);color:var(--text-primary);font-size:14px;">
          </div>

          <div style="margin-bottom:14px;">
            <label style="display:block;font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Pilihan Paket Iklan <span style="color:var(--accent-red)">*</span></label>
            <select name="package" id="inq_package" class="form-control" required style="width:100%;padding:10px 14px;border:1px solid var(--border-medium);border-radius:var(--radius-sm);background:var(--bg-secondary);color:var(--text-primary);font-size:14px;">
              <option value="Advertorial / Siaran Pers (Rp <?= $priceAdvertorial ?>)">Advertorial / Siaran Pers (Rp <?= $priceAdvertorial ?> / artikel)</option>
              <option value="Banner Header 728x90 (Rp <?= $priceHeader ?>)">Banner Header 728x90 (Rp <?= $priceHeader ?> / bulan)</option>
              <option value="Banner Sidebar 300x250 (Rp <?= $priceSidebar ?>)">Banner Sidebar 300x250 (Rp <?= $priceSidebar ?> / bulan)</option>
              <option value="Banner Bawah Artikel 468x100">Banner Bawah Artikel 468x100</option>
              <option value="Kemitraan Instansi / Media Partner Event">Kemitraan Instansi / Media Partner Event</option>
            </select>
          </div>

          <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Ringkasan Kebutuhan / Tautan Materi</label>
            <textarea name="notes" id="inq_notes" rows="3" class="form-control" placeholder="Jelaskan secara singkat rencana publikasi atau tanggal target tayang..." style="width:100%;padding:10px 14px;border:1px solid var(--border-medium);border-radius:var(--radius-sm);background:var(--bg-secondary);color:var(--text-primary);font-size:14px;"></textarea>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button type="button" onclick="sendInquiryViaWA()" class="pricing-btn-wa" style="flex:1;min-width:180px;cursor:pointer;border:none;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
              Kirim Cepat ke WhatsApp
            </button>
            <button type="submit" class="btn btn-primary" style="padding:11px 20px;border-radius:var(--radius-md);font-weight:700;font-size:13px;cursor:pointer;">
              Submit Form
            </button>
          </div>
        </form>
      </div>

      <!-- Info Kontak & Alur Bisnis -->
      <div style="display:flex;flex-direction:column;gap:18px;">
        
        <div style="background:var(--bg-primary);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow-sm);">
          <h3 style="font-size:16px;font-weight:800;color:var(--text-primary);margin-bottom:12px;display:flex;align-items:center;gap:8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary-blue)"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Alur Pemesanan 3 Langkah Mudah
          </h3>
          <ol style="padding-left:20px;font-size:13px;color:var(--text-secondary);display:flex;flex-direction:column;gap:10px;line-height:1.4;">
            <li><strong>Pilih Paket & Kirim Materi:</strong> Kirim teks rilis pers atau gambar banner dan tautan tujuan ke WhatsApp admin kami.</li>
            <li><strong>Review & Konfirmasi:</strong> Tim redaksi memeriksa materi agar sesuai standar pedoman siber dan mengonfirmasi jadwal tayang.</li>
            <li><strong>Tayang & Laporan:</strong> Berita atau banner langsung aktif tayang, dan link bukti tayang langsung dikirimkan ke Anda.</li>
          </ol>
        </div>

        <div style="background:var(--bg-primary);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow-sm);">
          <h3 style="font-size:16px;font-weight:800;color:var(--text-primary);margin-bottom:12px;">Kontak Langsung Divisi Iklan</h3>
          <div style="display:flex;flex-direction:column;gap:12px;font-size:13px;color:var(--text-secondary);">
            <div style="display:flex;gap:10px;align-items:center;">
              <div style="width:32px;height:32px;border-radius:6px;background:var(--primary-blue-light);display:flex;align-items:center;justify-content:center;color:var(--primary-blue);flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              </div>
              <div>
                <strong>Hotline WhatsApp Marketing:</strong><br>
                <a href="<?= e(getAdWhatsAppLink()) ?>" target="_blank" rel="noopener noreferrer" style="color:var(--primary-blue);font-weight:700;">+<?= e($adPhone) ?></a>
              </div>
            </div>

            <div style="display:flex;gap:10px;align-items:center;">
              <div style="width:32px;height:32px;border-radius:6px;background:var(--primary-blue-light);display:flex;align-items:center;justify-content:center;color:var(--primary-blue);flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              </div>
              <div>
                <strong>Email Khusus Iklan & Invoice:</strong><br>
                <a href="mailto:<?= e($adEmail) ?>" style="color:var(--primary-blue);font-weight:600;"><?= e($adEmail) ?></a>
              </div>
            </div>

            <div style="display:flex;gap:10px;align-items:center;">
              <div style="width:32px;height:32px;border-radius:6px;background:var(--primary-blue-light);display:flex;align-items:center;justify-content:center;color:var(--primary-blue);flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
              </div>
              <div>
                <strong>Metode Pembayaran Resmi:</strong><br>
                Transfer Bank (BCA, Mandiri, BRI, BNI), QRIS All Payment
              </div>
            </div>
          </div>
        </div>

        <div style="background:rgba(232, 0, 13, 0.04);border:1px dashed var(--accent-red);border-radius:var(--radius-lg);padding:18px;font-size:12px;color:var(--text-secondary);line-height:1.5;">
          <strong style="color:var(--accent-red);display:block;margin-bottom:4px;">Kebijakan Konten & Etika Pers:</strong>
          <?= e(APP_NAME) ?> berkomitmen menjaga kepercayaan pembaca. Kami <strong>TIDAK MENERIMA</strong> materi iklan yang berkaitan dengan perjudian online / kasino, obat-obatan ilegal/tanpa izin BPOM, penipuan investasi bodong, pornografi, atau konten yang melanggar hukum RI.
        </div>

      </div>

    </div>

    <!-- FAQ SECTION -->
    <div style="margin-top:20px;">
      <div class="section-header" style="margin-bottom:20px;">
        <h2 class="section-title">PERTANYAAN <span>SERING DIAJUKAN (FAQ)</span></h2>
      </div>

      <div class="ratecard-faq-grid">
        <div class="ratecard-faq-item">
          <div class="ratecard-faq-q">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--primary-blue)"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Berapa lama artikel rilis pers bertahan?
          </div>
          <div class="ratecard-faq-a">
            Artikel rilis pers / advertorial akan ditayangkan secara <strong>permanen</strong> di portal berita kami selama domain dan website aktif, tanpa biaya perpanjangan tahunan.
          </div>
        </div>

        <div class="ratecard-faq-item">
          <div class="ratecard-faq-q">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--primary-blue)"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Apakah artikel bisa menyertakan backlink ke web saya?
          </div>
          <div class="ratecard-faq-a">
            Ya, kami mengizinkan hingga <strong>2 link DoFollow</strong> aktif menuju halaman produk, website perusahaan, atau sosial media Anda yang aman dan relevan.
          </div>
        </div>

        <div class="ratecard-faq-item">
          <div class="ratecard-faq-q">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--primary-blue)"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Jika belum punya draf artikel, apakah bisa dibuatkan?
          </div>
          <div class="ratecard-faq-a">
            Bisa. Anda cukup mengirimkan poin-poin informasi produk/profil usaha Anda, dan tim jurnalis redaksi kami akan menyusun artikel bergaya jurnalistik profesional.
          </div>
        </div>

        <div class="ratecard-faq-item">
          <div class="ratecard-faq-q">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--primary-blue)"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Apakah tersedia invoice atau bukti tayang resmi?
          </div>
          <div class="ratecard-faq-a">
            Tentu. Untuk kebutuhan instansi, perusahaan, atau agensi, kami menyediakan invoice, kwitansi resmi, dan link laporan bukti tayang (LPJ media).
          </div>
        </div>
      </div>
    </div>

  </div>
</main>

<script>
function sendInquiryViaWA() {
  const name    = document.getElementById('inq_name').value.trim();
  const company = document.getElementById('inq_company').value.trim();
  const phone   = document.getElementById('inq_phone').value.trim();
  const pkg     = document.getElementById('inq_package').value;
  const notes   = document.getElementById('inq_notes').value.trim();

  if (!name) {
    alert('Mohon isi nama lengkap Anda.');
    document.getElementById('inq_name').focus();
    return;
  }

  let text = `Halo Tim Marketing <?= addslashes(APP_NAME) ?>,\n\n`;
  text += `Saya ingin konsultasi / order pemasangan iklan:\n`;
  text += `• Nama: ${name}\n`;
  if (company) text += `• Instansi/Brand: ${company}\n`;
  if (phone)   text += `• No. WA: ${phone}\n`;
  text += `• Paket yang Diminati: ${pkg}\n`;
  if (notes)   text += `• Catatan Materi: ${notes}\n`;
  text += `\nMohon info ketersediaan slot & nomor rekening pembayaran. Terima kasih.`;

  const waUrl = `https://wa.me/<?= $adPhone ?>?text=` + encodeURIComponent(text);
  window.open(waUrl, '_blank');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
