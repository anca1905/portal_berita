<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Pedoman Pemberitaan Media Siber – ' . APP_NAME;
$pageDescription = 'Pedoman Pemberitaan Media Siber resmi yang ditetapkan oleh Dewan Pers Republik Indonesia untuk ' . APP_NAME . '.';
include __DIR__ . '/includes/header.php';
?>

<div style="background:var(--bg-secondary);padding:24px 0;border-bottom:1px solid var(--border-light)">
  <div class="container">
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="width:4px;height:32px;background:var(--accent-red);border-radius:2px;flex-shrink:0"></div>
      <div>
        <h1 style="font-size:24px;font-weight:800;color:var(--text-primary);margin-bottom:2px">Pedoman Pemberitaan Media Siber</h1>
        <div style="font-size:13px;color:var(--text-muted)">Standar Etik & Kepatuhan Jurnalistik Media Online (Dewan Pers)</div>
      </div>
    </div>
  </div>
</div>

<main class="section-padding">
  <div class="container" style="max-width:960px;">
    <div style="background:var(--bg-primary);border-radius:var(--radius-lg);border:1px solid var(--border-light);padding:36px;box-shadow:var(--shadow-sm);font-size:15px;line-height:1.75;color:var(--text-secondary);">
      <p style="margin-bottom:20px;">
        Kemerdekaan berpendapat, kemerdekaan berekspresi, dan kemerdekaan pers adalah hak asasi manusia yang dilindungi Pancasila, Undang-Undang Dasar 1945, dan Deklarasi Universal Hak Asasi Manusia PBB. Media siber <strong><?= e(APP_NAME) ?></strong> tunduk dan patuh pada <strong>Pedoman Pemberitaan Media Siber</strong> yang disahkan Dewan Pers:
      </p>

      <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:24px 0 10px;">1. Ruang Lingkup</h3>
      <p style="margin-bottom:16px;">
        Media Siber adalah segala bentuk media yang menggunakan wahana internet dan melaksanakan kegiatan jurnalistik, serta memenuhi persyaratan Undang-Undang Pokok Pers dan Standar Perusahaan Pers.
      </p>

      <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:24px 0 10px;">2. Verifikasi dan Keberimbangan Berita</h3>
      <ul style="list-style:disc;margin-left:24px;margin-bottom:16px;">
        <li>Pada prinsipnya setiap berita harus melalui proses verifikasi fakta dan konfirmasi kepada pihak-pihak terkait.</li>
        <li>Berita yang berpotensi merugikan pihak lain memerlukan verifikasi pada berita yang sama untuk memenuhi prinsip akurasi dan keberimbangan (<em>cover both sides</em>).</li>
      </ul>

      <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:24px 0 10px;">3. Isi Buatan Pengguna (User Generated Content / UGC)</h3>
      <p style="margin-bottom:16px;">
        Pengguna yang memuat komentar atau opini bertanggung jawab atas isi yang disampaikan. Redaksi berhak menyunting atau menghapus komentar yang mengandung unsur SARA, ujaran kebencian, fitnah, pornografi, atau ajakan kekerasan.
      </p>

      <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:24px 0 10px;">4. Ralat, Koreksi, dan Hak Jawab</h3>
      <ul style="list-style:disc;margin-left:24px;margin-bottom:16px;">
        <li>Ralat, koreksi, dan hak jawab mengacu pada Undang-Undang Pers, Kode Etik Jurnalistik, dan Pedoman Hak Jawab.</li>
        <li>Ralat atau hak jawab ditautkan langsung pada berita yang bersangkutan agar pembaca memperoleh informasi utuh.</li>
        <li>Pada setiap berita yang dikoreksi, redaksi mencantumkan waktu pemutakhiran (<em>update</em>) serta catatan ralat.</li>
      </ul>

      <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:24px 0 10px;">5. Pencabutan Berita</h3>
      <p style="margin-bottom:16px;">
        Berita yang sudah dipublikasikan tidak dapat dicabut karena alasan penyensoran dari pihak luar, kecuali terkait masalah SARA, kesusilaan anak di bawah umur, atau atas pertimbangan khusus rekomendasi Dewan Pers.
      </p>

      <div style="margin-top:32px;padding:16px;background:var(--bg-secondary);border-radius:var(--radius-md);border-left:4px solid var(--accent-red);font-size:13px;color:var(--text-muted);">
        Salinan resmi Surat Keputusan Dewan Pers Nomor: 03/SK-DP/III/2012 tentang Pedoman Pemberitaan Media Siber.
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
