/* ══════════════════════════════════════════════
   ThurzShop Marketplace — main.js
   ══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Navbar: highlight active link on scroll ── */
  const sections  = document.querySelectorAll('section[id], div[id]');
  const navLinks  = document.querySelectorAll('.nav-links a');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        navLinks.forEach(link => {
          link.classList.toggle(
            'active',
            link.getAttribute('href') === `#${entry.target.id}`
          );
        });
      }
    });
  }, { threshold: 0.4 });

  sections.forEach(sec => observer.observe(sec));

  /* ── Navbar: sticky shadow on scroll ─────────── */
  const nav = document.querySelector('nav');
  window.addEventListener('scroll', () => {
    nav.style.boxShadow = window.scrollY > 10
      ? '0 2px 20px rgba(0,0,0,.08)'
      : 'none';
  });

  /* ── Search bar: focus effect ────────────────── */
  const searchInput = document.querySelector('.search-box input');
  const searchBox   = document.querySelector('.search-box');

  if (searchInput && searchBox) {
    searchInput.addEventListener('focus', () => {
      searchBox.style.borderColor = 'var(--blue)';
      searchBox.style.boxShadow   = '0 0 0 3px rgba(26,86,255,.12)';
    });
    searchInput.addEventListener('blur', () => {
      searchBox.style.borderColor = '';
      searchBox.style.boxShadow   = '';
    });

    /* ── Search: Enter key → go to marketplace ── */
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const query = searchInput.value.trim();
        if (query) {
          window.location.href = `pages/marketplace.php?q=${encodeURIComponent(query)}`;
        }
      }
    });
  }

  /* ── Game cards: click → marketplace filter ─── */
  document.querySelectorAll('.game-card').forEach(card => {
    card.addEventListener('click', () => {
      const game = card.querySelector('.game-name')?.textContent.trim();
      if (game) {
        window.location.href = `pages/marketplace.php?game=${encodeURIComponent(game)}`;
      }
    });
  });

  /* ── Deal featured card hover: subtle scale ─── */
  const featured = document.querySelector('.deal-featured');
  if (featured) {
    featured.addEventListener('mouseenter', () => {
      featured.style.transform = 'scale(1.01)';
      featured.style.transition = 'transform .3s ease';
    });
    featured.addEventListener('mouseleave', () => {
      featured.style.transform = 'scale(1)';
    });
  }

  /* ── Scroll-reveal for sections ──────────────── */
  const revealEls = document.querySelectorAll(
    '.game-card, .deal-card, .trust-card'
  );

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        entry.target.style.animationDelay = `${i * 0.05}s`;
        entry.target.classList.add('revealed');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  /* Add base reveal style via JS */
  const style = document.createElement('style');
  style.textContent = `
    .game-card, .deal-card, .trust-card {
      opacity: 0;
      transform: translateY(16px);
      transition: opacity .5s ease, transform .5s ease;
    }
    .revealed {
      opacity: 1 !important;
      transform: translateY(0) !important;
    }
  `;
  document.head.appendChild(style);

  revealEls.forEach(el => revealObserver.observe(el));

  /* ── Mobile menu toggle (hamburger) ──────────── */
  const menuToggle = document.getElementById('menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');

  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener('click', () => {
      const isOpen = mobileMenu.style.display === 'flex';
      mobileMenu.style.display = isOpen ? 'none' : 'flex';
      menuToggle.setAttribute('aria-expanded', String(!isOpen));
    });
  }

  /* ── Toast notification helper ───────────────── */
  window.showToast = (message, type = 'info') => {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    const toastStyle = document.createElement('style');
    toastStyle.textContent = `
      .toast {
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        padding: 12px 20px; border-radius: 10px;
        font-family: 'Manrope', sans-serif; font-size: 14px; font-weight: 600;
        box-shadow: 0 8px 32px rgba(0,0,0,.15);
        animation: toastIn .3s ease, toastOut .3s ease 2.7s forwards;
        max-width: 320px;
      }
      .toast-info    { background: var(--blue);  color: #fff; }
      .toast-success { background: var(--green); color: #fff; }
      .toast-error   { background: #ef4444;      color: #fff; }
      @keyframes toastIn  { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
      @keyframes toastOut { from { opacity:1; } to { opacity:0; transform:translateY(10px); } }
    `;
    if (!document.getElementById('toast-style')) {
      toastStyle.id = 'toast-style';
      document.head.appendChild(toastStyle);
    }

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  };

});

 // ── Set game filter via button click ──────────────
  function setGame(game) {
    document.getElementById('gameInput').value = game;
    document.getElementById('filterForm').submit();
  }

  // ── Mobile: toggle sidebar ─────────────────────────
  const toggleBtn = document.getElementById('filterToggle');
  const sidebar   = document.getElementById('filterSidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      toggleBtn.textContent = sidebar.classList.contains('open') ? '✕ Tutup Filter' : '⚙ Filter';
    });
  }

  // ── Card reveal animation ──────────────────────────
  const cards = document.querySelectorAll('.listing-card');
  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        revealObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.05 });

  cards.forEach((card, i) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = `opacity .4s ease ${i * 0.06}s, transform .4s ease ${i * 0.06}s, border-color .2s, box-shadow .2s`;
    revealObs.observe(card);
  });


  /* ── LOGIN PAGE ──────────────────────────────────────────────────── */
  const togglePw = document.getElementById('togglePw');
  if (togglePw) {
    const pwInput   = document.getElementById('password');
    const eyeOpen   = document.getElementById('eyeOpen');
    const eyeOff    = document.getElementById('eyeOff');

    togglePw.addEventListener('click', () => {
      const isHidden  = pwInput.type === 'password';
      pwInput.type    = isHidden ? 'text' : 'password';
      eyeOpen.style.display = isHidden ? 'none'  : 'block';
      eyeOff.style.display  = isHidden ? 'block' : 'none';
    });
  }

  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    const submitBtn  = document.getElementById('submitBtn');
    const btnText    = document.getElementById('btnText');
    const btnArrow   = document.getElementById('btnArrow');
    const btnSpinner = document.getElementById('btnSpinner');

    function setFieldError(groupId, errId, msg) {
      document.getElementById(groupId).classList.add('has-error');
      document.getElementById(errId).textContent = msg;
    }
    function clearFieldError(groupId) {
      document.getElementById(groupId).classList.remove('has-error');
    }

    document.getElementById('username')?.addEventListener('input', () => clearFieldError('group-username'));
    document.getElementById('password')?.addEventListener('input', () => clearFieldError('group-password'));

    loginForm.addEventListener('submit', (e) => {
      let valid = true;
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;

      clearFieldError('group-username');
      clearFieldError('group-password');

      if (!username) {
        setFieldError('group-username', 'err-username', 'Username tidak boleh kosong.');
        valid = false;
      }
      if (!password) {
        setFieldError('group-password', 'err-password', 'Password tidak boleh kosong.');
        valid = false;
      }

      if (!valid) { e.preventDefault(); return; }

      submitBtn.disabled       = true;
      btnText.textContent      = 'Masuk...';
      btnArrow.style.display   = 'none';
      btnSpinner.style.display = 'block';
    });
  }
