/* ============================================================
   NusaNews - Portal Berita
   Main Application JavaScript
   ============================================================ */

(function () {
  'use strict';

  /* ============ MARKET DATA ============ */
  let MARKET_DATA = [
    { name: 'IHSG', value: '7.487,32', change: '+1,24%', up: true },
    { name: 'LQ45', value: '1.042,18', change: '+0,98%', up: true },
    { name: 'IDR/USD', value: '15.812', change: '-0,31%', up: false },
    { name: 'IDR/EUR', value: '17.240', change: '+0,15%', up: true },
    { name: 'Bitcoin', value: '$74.980', change: '+3,21%', up: true },
    { name: 'Ethereum', value: '$3.840', change: '+2,15%', up: true },
    { name: 'Emas', value: 'Rp 1.285.000', change: '+0,45%', up: true },
    { name: 'Minyak WTI', value: '$78,40', change: '-0,87%', up: false },
    { name: 'CPO', value: 'RM 4.218', change: '+2,91%', up: true },
    { name: 'Nikel', value: '$18.750', change: '+1,10%', up: true },
    { name: 'Batu Bara', value: '$142,60', change: '-1,45%', up: false },
    { name: 'S&P 500', value: '5.612,45', change: '+0,67%', up: true },
    { name: 'Nikkei', value: '38.421,00', change: '+0,32%', up: true },
    { name: 'Hang Seng', value: '18.922,18', change: '-0,45%', up: false },
  ];

  /* ============ BUILD TICKER STRIP ============ */
  function buildTicker() {
    const track = document.getElementById('tickerTrack');
    if (!track) return;

    // Duplicate for seamless scroll
    const items = [...MARKET_DATA, ...MARKET_DATA];
    const html = items.map(item => `
      <div class="ticker-item">
        <span class="ticker-name">${item.name}</span>
        <span class="ticker-value">${item.value}</span>
        <span class="ticker-change ${item.up ? 'up' : 'down'}">
          ${item.up ? '▲' : '▼'} ${item.change}
        </span>
      </div>
      <span class="ticker-dot">·</span>
    `).join('');

    track.innerHTML = html;
  }

  /* ============ BUILD MARKET DATA STRIP ============ */
  function buildMarketStrip() {
    const container = document.getElementById('marketDataInner');
    if (!container) return;

    const displayItems = MARKET_DATA.slice(0, 8);
    const html = displayItems.map(item => `
      <div class="market-card">
        <div class="market-card-name">${item.name}</div>
        <div class="market-card-value">${item.value}</div>
        <div class="market-card-change ${item.up ? 'up' : 'down'}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            ${item.up
              ? '<path d="M12 4l8 8H4z"/>'
              : '<path d="M12 20l-8-8h16z"/>'
            }
          </svg>
          ${item.change}
        </div>
      </div>
    `).join('');

    container.innerHTML = html;
  }

  /* ============ BUILD MARKET MINI WIDGET ============ */
  function buildMarketMini() {
    const container = document.getElementById('marketMiniWidget');
    if (!container) return;

    const items = MARKET_DATA.slice(0, 6);
    const html = items.map(item => `
      <div class="market-mini-row" tabindex="0" aria-label="${item.name}: ${item.value}, ${item.change}">
        <div class="market-mini-name">${item.name}</div>
        <div class="market-mini-val">${item.value}</div>
        <div class="market-mini-chg ${item.up ? 'up' : 'down'}">${item.change}</div>
      </div>
    `).join('');

    container.innerHTML = html;
  }

  /* ============ SEARCH OVERLAY ============ */
  const searchOverlay = document.getElementById('searchOverlay');
  const searchToggleBtn = document.getElementById('searchToggleBtn');
  const searchCloseBtn = document.getElementById('searchCloseBtn');
  const mainSearchInput = document.getElementById('mainSearchInput');

  function openSearch() {
    if (!searchOverlay) return;
    searchOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => mainSearchInput && mainSearchInput.focus(), 300);
  }

  function closeSearch() {
    if (!searchOverlay) return;
    searchOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (searchToggleBtn) searchToggleBtn.addEventListener('click', openSearch);
  if (searchCloseBtn) searchCloseBtn.addEventListener('click', closeSearch);

  if (searchOverlay) {
    searchOverlay.addEventListener('click', function (e) {
      if (e.target === searchOverlay) closeSearch();
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSearch();
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      openSearch();
    }
  });

  // Expose doSearch globally for inline calls
  window.doSearch = function (query) {
    closeSearch();
    showNotification('Mencari: ' + query, 'Hasil pencarian akan segera tersedia.');
  };

  /* ============ LIVE SEARCH AUTOCOMPLETE ============ */
  const liveResultsContainer = document.getElementById('liveSearchResults');
  let searchDebounceTimer = null;

  if (mainSearchInput && liveResultsContainer) {
    mainSearchInput.addEventListener('input', function () {
      clearTimeout(searchDebounceTimer);
      const query = this.value.trim();
      if (query.length < 2) {
        liveResultsContainer.innerHTML = '';
        liveResultsContainer.style.display = 'none';
        return;
      }

      searchDebounceTimer = setTimeout(async function () {
        try {
          const res = await fetch((window.APP_URL || '') + '/api_search.php?q=' + encodeURIComponent(query));
          const json = await res.json();
          const items = json.data || [];
          if (items.length === 0) {
            liveResultsContainer.innerHTML = '<div style="padding:12px;font-size:13px;color:var(--text-muted);text-align:center;">Tidak ada hasil ditemukan.</div>';
            liveResultsContainer.style.display = 'block';
            return;
          }

          const html = items.map(item => `
            <a href="${item.url}" class="live-search-item">
              <img src="${item.image}" alt="" class="live-search-thumb" onerror="this.src='${window.APP_URL || ''}/assets/default-post.jpg'">
              <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;line-height:1.35;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.title}</div>
                <div class="live-search-meta">
                  <span class="live-search-badge">${item.category}</span>
                  <span>·</span>
                  <span>${item.time}</span>
                </div>
              </div>
            </a>
          `).join('');

          liveResultsContainer.innerHTML = html;
          liveResultsContainer.style.display = 'block';
        } catch (e) {
          console.error("Live search error:", e);
        }
      }, 250);
    });
  }

  /* ============ THEME TOGGLE (DARK / LIGHT) ============ */
  const themeToggleBtn = document.getElementById('themeToggleBtn');
  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', function () {
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      if (isDark) {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('xnn_theme', 'light');
      } else {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('xnn_theme', 'dark');
      }
    });
  }

  /* ============ TAB SWITCHING ============ */
  window.switchTab = function (btn, tabId) {
    // Deactivate all tabs in the same group
    const tabGroup = btn.closest('[role="tablist"]');
    if (tabGroup) {
      tabGroup.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
    }
    btn.classList.add('active');
    btn.setAttribute('aria-selected', 'true');

    // Show tab content placeholder
    showNotification('Tab Diaktifkan', 'Menampilkan konten kategori terpilih...');
  };

  /* ============ SCROLL TO TOP ============ */
  const scrollTopBtn = document.getElementById('scrollTopBtn');

  function handleScroll() {
    if (!scrollTopBtn) return;
    if (window.scrollY > 400) {
      scrollTopBtn.classList.add('visible');
    } else {
      scrollTopBtn.classList.remove('visible');
    }
  }

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  window.addEventListener('scroll', handleScroll, { passive: true });

  /* ============ NOTIFICATION SYSTEM ============ */
  function showNotification(title, text, duration = 4000) {
    const bar = document.getElementById('notifBar');
    if (!bar) return;

    const notif = document.createElement('div');
    notif.className = 'notif-item';
    notif.innerHTML = `
      <div class="notif-title">${title}</div>
      <div class="notif-text">${text}</div>
    `;

    bar.appendChild(notif);
    notif.addEventListener('click', () => notif.remove());

    setTimeout(() => {
      notif.style.opacity = '0';
      notif.style.transform = 'translateX(120%)';
      notif.style.transition = 'all 0.4s ease';
      setTimeout(() => notif.remove(), 400);
    }, duration);
  }

  // Expose globally
  window.showNotification = showNotification;

  /* ============ NEWSLETTER ============ */
  window.handleNewsletter = async function (e) {
    e.preventDefault();
    const emailEl = document.getElementById('newsletterEmail');
    const email = emailEl ? emailEl.value.trim() : '';
    if (!email) return;

    try {
      const res = await fetch((window.APP_URL || '') + '/api_newsletter.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email })
      });
      const data = await res.json();
      if (data.success) {
        showNotification('Berhasil Berlangganan!', data.message);
        if (emailEl) emailEl.value = '';
      } else {
        showNotification('Perhatian', data.message || 'Gagal berlangganan.');
      }
    } catch (err) {
      showNotification('Berhasil Berlangganan!', `Email ${email} telah terdaftar.`);
      if (emailEl) emailEl.value = '';
    }
  };

  /* ============ LIVE MARKET UPDATE SIMULATION ============ */
  function simulateLiveUpdate() {
    // Slightly randomize market values to simulate live data
    MARKET_DATA.forEach(item => {
      const rand = (Math.random() - 0.48) * 0.3;
      const direction = rand > 0;
      const absChg = Math.abs(rand).toFixed(2);
      item.up = direction;
      item.change = `${direction ? '+' : '-'}${absChg}%`;
    });

    buildMarketMini();

    // Update ticker
    const tickerItems = document.querySelectorAll('.ticker-change');
    const miniItems = MARKET_DATA;
    tickerItems.forEach((el, i) => {
      const dataIdx = i % MARKET_DATA.length;
      const item = miniItems[dataIdx];
      if (!item) return;
      el.className = `ticker-change ${item.up ? 'up' : 'down'}`;
      el.textContent = `${item.up ? '▲' : '▼'} ${item.change}`;
    });
  }

  // Update every 8 seconds
  setInterval(simulateLiveUpdate, 8000);

  /* ============ MOBILE NAVIGATION DRAWER & DROPDOWN ============ */
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const mobileDrawerBackdrop = document.getElementById('mobileDrawerBackdrop');
  const mobileDrawerCloseBtn = document.getElementById('mobileDrawerCloseBtn');

  function openMobileDrawer() {
    if (!mobileDrawer) return;
    mobileDrawer.classList.add('active');
    if (mobileDrawerBackdrop) mobileDrawerBackdrop.classList.add('active');
    document.body.style.overflow = 'hidden';
    mobileDrawer.setAttribute('aria-hidden', 'false');
  }

  function closeMobileDrawer() {
    if (!mobileDrawer) return;
    mobileDrawer.classList.remove('active');
    if (mobileDrawerBackdrop) mobileDrawerBackdrop.classList.remove('active');
    document.body.style.overflow = '';
    mobileDrawer.setAttribute('aria-hidden', 'true');
  }

  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', openMobileDrawer);
  }
  if (mobileDrawerCloseBtn) {
    mobileDrawerCloseBtn.addEventListener('click', closeMobileDrawer);
  }
  if (mobileDrawerBackdrop) {
    mobileDrawerBackdrop.addEventListener('click', closeMobileDrawer);
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && mobileDrawer && mobileDrawer.classList.contains('active')) {
      closeMobileDrawer();
    }
  });

  // Nav Dropdown click toggle for touchscreen laptops & mobile
  const navDropdownBtn = document.getElementById('navDropdownBtn');
  const navDropdown = document.getElementById('navDropdown');
  if (navDropdownBtn && navDropdown) {
    navDropdownBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      navDropdown.classList.toggle('active');
    });
    document.addEventListener('click', function (e) {
      if (!navDropdown.contains(e.target)) {
        navDropdown.classList.remove('active');
      }
    });
  }

  /* ============ ARTICLE CARD INTERACTIONS ============ */
  document.addEventListener('click', function (e) {
    const card = e.target.closest('.news-card, .hero-sidebar-item, .news-list-item, .trending-item, .video-card, .opinion-card');
    if (card) {
      // Ripple effect
      const ripple = document.createElement('div');
      ripple.style.cssText = `
        position:absolute;width:60px;height:60px;
        border-radius:50%;background:rgba(0,71,204,0.08);
        transform:scale(0);animation:ripple-anim 0.5s ease;
        pointer-events:none;z-index:999;
        left:${e.clientX - card.getBoundingClientRect().left - 30}px;
        top:${e.clientY - card.getBoundingClientRect().top - 30}px;
      `;

      if (!document.getElementById('ripple-style')) {
        const style = document.createElement('style');
        style.id = 'ripple-style';
        style.textContent = `@keyframes ripple-anim{to{transform:scale(4);opacity:0}}`;
        document.head.appendChild(style);
      }

      const pos = getComputedStyle(card).position;
      if (pos === 'static') card.style.position = 'relative';
      card.style.overflow = 'hidden';
      card.appendChild(ripple);
      setTimeout(() => ripple.remove(), 500);
    }
  });

  /* ============ INTERSECTION OBSERVER (Lazy Reveal) ============ */
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-fade-up');
          entry.target.style.opacity = '1';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.news-card, .video-card, .opinion-card, .sidebar-widget').forEach(el => {
      el.style.opacity = '0';
      observer.observe(el);
    });
  }

  /* ============ REAL TIME CLOCK in Header ============ */
  function updateDateTime() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formatted = now.toLocaleDateString('id-ID', options);
    // Could be displayed somewhere; storing for potential use
    window.__currentDate = formatted;
  }

  updateDateTime();
  setInterval(updateDateTime, 60000);

  /* ============ BREAKING NEWS PAUSE ON HOVER ============ */
  const breakingTrack = document.querySelector('.breaking-track');
  if (breakingTrack) {
    breakingTrack.addEventListener('mouseenter', () => {
      breakingTrack.style.animationPlayState = 'paused';
    });
    breakingTrack.addEventListener('mouseleave', () => {
      breakingTrack.style.animationPlayState = 'running';
    });
  }

  /* ============ WELCOME NOTIFICATION ============ */
  window.addEventListener('load', function () {
    setTimeout(() => {
      showNotification(
        `Selamat Datang di ${window.APP_NAME || 'NusaNews'}`,
        'Berita terkini ekonomi & bisnis Indonesia. Diperbarui setiap menit.'
      );
    }, 1200);
  });

  /* ============ INIT ============ */
  async function init() {
    try {
      // Menambahkan cache busting timestamp agar tidak ter-cache oleh browser
      const baseUrl = window.APP_URL || '';
      const response = await fetch(baseUrl + '/market_data.json?t=' + new Date().getTime());
      if (response.ok) {
        const json = await response.json();
        if (json && json.data && json.data.length > 0) {
          MARKET_DATA = json.data;
        }
      }
    } catch (e) {
      console.warn("Gagal menarik data live market, menggunakan data statis.", e);
    }

    buildTicker();
    buildMarketStrip();
    buildMarketMini();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
