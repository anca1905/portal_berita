<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$pageTitle       = 'Live TV – ' . (defined('APP_NAME') ? APP_NAME : 'Portal Berita');
$pageDescription = 'Tonton siaran langsung TV Merah Putih secara online.';

// Ambil semua channel yang aktif dari DB
$activeChannels = [];
$selectedChannel = null;
$channelSlug = trim($_GET['channel'] ?? $_GET['slug'] ?? '');

try {
    $db  = getDB();
    $activeChannels = $db->query("SELECT * FROM tv_channels WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    
    if (!empty($activeChannels)) {
        if ($channelSlug) {
            foreach ($activeChannels as $ch) {
                if ($ch['slug'] === $channelSlug) {
                    $selectedChannel = $ch;
                    break;
                }
            }
        }
        if (!$selectedChannel) {
            $selectedChannel = $activeChannels[0];
        }
    }
} catch (Exception $e) {
    // fallback
}

$youtubeId   = $selectedChannel['youtube_id'] ?? 'UCXl7geFMDSZkHWcKm28XSOA';
$channelName = $selectedChannel['name'] ?? 'TV MERAH PUTIH';
$channelDesc = $selectedChannel['description'] ?? 'Siaran Langsung 24 Jam';

require_once __DIR__ . '/includes/header.php';
?>

<style>
.live-tv-section {
  background: #0d0d0d;
  padding: 32px 0 48px;
  min-height: 60vh;
}

.live-tv-inner {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Header bar */
.live-tv-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.live-tv-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.live-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #E8000D;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 4px;
}

.live-badge::before {
  content: '';
  width: 6px;
  height: 6px;
  background: #fff;
  border-radius: 50%;
  animation: blink-live 1.2s ease-in-out infinite;
}

@keyframes blink-live {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

.live-tv-name {
  font-size: 15px;
  font-weight: 700;
  color: #fff;
  letter-spacing: 0.3px;
}

.live-tv-desc {
  font-size: 11px;
  color: rgba(255,255,255,0.45);
}

.live-yt-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: rgba(255,255,255,0.55);
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(255,255,255,0.1);
  padding: 6px 12px;
  border-radius: 20px;
  text-decoration: none;
  transition: all 0.2s;
}

.live-yt-btn:hover {
  background: rgba(255,255,255,0.13);
  color: #fff;
}

/* Player */
.live-player-wrap {
  border-radius: 14px;
  overflow: hidden;
  background: #000;
  box-shadow: 0 16px 48px rgba(0,0,0,0.5);
  position: relative;
}

.live-embed {
  position: relative;
  padding-bottom: 56.25%;
  height: 0;
}

.live-embed iframe {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  border: none;
}

/* Footer bar */
.live-tv-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 12px;
}

.live-viewer {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: rgba(255,255,255,0.4);
}

.live-viewer svg { width: 13px; height: 13px; }

.live-time {
  font-size: 12px;
  color: rgba(255,255,255,0.4);
}
</style>

<div class="live-tv-section">
  <div class="live-tv-inner">

    <div class="live-tv-bar">
      <div class="live-tv-brand">
        <span class="live-badge">LIVE</span>
        <div>
          <div class="live-tv-name"><?= htmlspecialchars($channelName) ?></div>
          <div class="live-tv-desc"><?= htmlspecialchars($channelDesc) ?></div>
        </div>
      </div>
      <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($youtubeId) ?>"
         target="_blank" class="live-yt-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
          <polyline points="15 3 21 3 21 9"/>
          <line x1="10" y1="14" x2="21" y2="3"/>
        </svg>
        Buka di YouTube
      </a>
    </div>

    <div class="live-player-wrap">
      <div class="live-embed">
        <iframe
          src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?autoplay=1&rel=0&controls=1&modestbranding=1"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
          loading="lazy">
        </iframe>
      </div>
    </div>

    <div class="live-tv-footer">
      <div class="live-viewer">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <span id="viewerCount">–</span>
      </div>
      <div class="live-time" id="liveTime"></div>
    </div>

    <?php if (count($activeChannels) > 1): ?>
    <!-- Channel Switcher -->
    <div style="margin-top:28px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.1);">
      <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:12px;letter-spacing:1px;text-transform:uppercase;">Pilih Saluran TV</div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:12px;">
        <?php foreach ($activeChannels as $ch): 
          $isActiveCh = ($selectedChannel && $selectedChannel['id'] === $ch['id']);
        ?>
        <a href="<?= APP_URL ?>/tv.php?channel=<?= e($ch['slug']) ?>" 
           style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:8px;background:<?= $isActiveCh ? 'rgba(232,0,13,0.2)' : 'rgba(255,255,255,0.05)' ?>;border:1px solid <?= $isActiveCh ? '#E8000D' : 'rgba(255,255,255,0.1)' ?>;text-decoration:none;transition:all .2s;">
          <div style="width:32px;height:32px;border-radius:6px;background:<?= e($ch['color'] ?: '#E8000D') ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;">
            <?= e($ch['logo_letter'] ?: 'TV') ?>
          </div>
          <div style="overflow:hidden;">
            <div style="font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($ch['name']) ?></div>
            <div style="font-size:11px;color:rgba(255,255,255,0.5);"><?= $isActiveCh ? '▶ Sedang Diputar' : 'Klik untuk tonton' ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
// Jam real-time
(function tick() {
  const now = new Date();
  const hh = String(now.getHours()).padStart(2,'0');
  const mm = String(now.getMinutes()).padStart(2,'0');
  const ss = String(now.getSeconds()).padStart(2,'0');
  const el = document.getElementById('liveTime');
  if (el) el.textContent = hh + ':' + mm + ':' + ss + ' WIB';
  setTimeout(tick, 1000);
})();

// Simulasi penonton
(function() {
  const el = document.getElementById('viewerCount');
  let n = <?= rand(8000, 45000) ?>;
  function upd() {
    n += Math.floor(Math.random() * 30) - 15;
    n = Math.max(1000, n);
    if (el) el.textContent = n.toLocaleString('id-ID') + ' penonton';
  }
  upd();
  setInterval(upd, 4000);
})();
</script>

<?php
if (file_exists(__DIR__ . '/includes/footer.php')) include __DIR__ . '/includes/footer.php';
?>
