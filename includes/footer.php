<?php
// XyronNews – Public Footer
$footerCategories = getCategories();
$popularPosts = getPopularPosts(5);
?>

<!-- FOOTER -->
<footer class="footer" role="contentinfo">
  <div class="footer-top">
    <div class="container">
      <div class="footer-grid">
        <!-- Brand -->
        <div class="footer-brand">
          <div class="footer-logo" style="align-items: center; display: flex;">
            <?php if (!empty($__globalSettings['site_logo'])): ?>
              <img src="<?= getLogoUrl() ?>" alt="Logo" style="width:36px; height:36px; object-fit:contain; margin-right:12px; border-radius:8px;">
            <?php else: ?>
              <div class="footer-logo-icon">X</div>
            <?php endif; ?>
            <span class="footer-logo-name"><?= APP_NAME ?></span>
          </div>
          <p class="footer-desc"><?= APP_TAGLINE ?></p>
          <div class="footer-social">
            <a href="#" class="social-btn" aria-label="Facebook">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
            </a>
            <a href="#" class="social-btn" aria-label="Twitter/X">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="#" class="social-btn" aria-label="Instagram">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
            </a>
            <a href="#" class="social-btn" aria-label="YouTube">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.97C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="white"/></svg>
            </a>
          </div>
        </div>

        <!-- Kanal -->
        <div>
          <div class="footer-col-title">KATEGORI</div>
          <div class="footer-links">
            <?php foreach ($footerCategories as $cat): ?>
            <a href="<?= e(categoryUrl($cat['slug'])) ?>" class="footer-link"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Populer -->
        <div>
          <div class="footer-col-title">ARTIKEL POPULER</div>
          <div class="footer-links">
            <?php foreach ($popularPosts as $pp): ?>
            <a href="<?= e(postUrl($pp['slug'])) ?>" class="footer-link" style="display:block;margin-bottom:8px;white-space:normal;line-height:1.4;">
              <?= e(mb_strimwidth($pp['title'], 0, 65, '...')) ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Layanan -->
        <div>
          <div class="footer-col-title">LAYANAN & INFORMASI</div>
          <div class="footer-links">
            <a href="<?= APP_URL ?>/search.php" class="footer-link">Pencarian Berita</a>
            <a href="<?= APP_URL ?>/tv.php" class="footer-link">Live TV Streaming</a>
            <a href="<?= APP_URL ?>/tentang.php" class="footer-link">Tentang Kami</a>
            <a href="<?= APP_URL ?>/pasang-iklan.php" class="footer-link" style="color:var(--accent-red);font-weight:700;">Pasang Iklan & Media Kit</a>
            <a href="<?= APP_URL ?>/kontak.php" class="footer-link">Hubungi Redaksi</a>
            <a href="<?= APP_URL ?>/pedoman.php" class="footer-link">Pedoman Media Siber</a>
            <a href="<?= APP_URL ?>/privasi.php" class="footer-link">Kebijakan Privasi</a>
            <a href="<?= APP_URL ?>/admin/" class="footer-link">Portal Redaksi / Admin</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="footer-bottom">
      <span class="footer-copy">© <?= date('Y') ?> <?= APP_NAME ?>. Semua Hak Dilindungi.</span>
      <div class="footer-bottom-links">
        <a href="<?= APP_URL ?>/privasi.php" class="footer-bottom-link">Syarat & Ketentuan</a>
        <a href="<?= APP_URL ?>/privasi.php" class="footer-bottom-link">Kebijakan Privasi</a>
        <a href="<?= APP_URL ?>/pedoman.php" class="footer-bottom-link">Pedoman Media Siber</a>
      </div>
    </div>
  </div>
</footer>

<script>window.APP_URL = '<?= APP_URL ?>';</script>
<script src="<?= APP_URL ?>/app.js"></script>
<?php if (!empty($extraJs)) echo $extraJs; ?>
</body>
</html>
