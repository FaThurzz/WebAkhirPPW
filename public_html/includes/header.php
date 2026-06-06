<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$base_url = '/ProjectAkhir/public_html/'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($page_title) ? $page_title : 'ThurzShop — Marketplace'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Rowdies:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/style.css" />
  <style>
    /* ── NAVBAR ─────────────────────────────────── */
    nav {
      position: sticky; top: 0; z-index: 100;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
    }
    .nav-inner {
      max-width: var(--container); margin: 0 auto;
      display: flex; align-items: center; gap: var(--space-4);
      padding: 0 var(--gutter); height: 60px;
    }
    .nav-logo {
      font-size: 18px; font-weight: 800;
      color: var(--blue); letter-spacing: -.5px;
      flex-shrink: 0;
    }
    .nav-logo span { color: var(--text); }

    .nav-links { display: flex; gap: 4px; list-style: none; }
    .nav-links a {
      padding: 6px 14px; border-radius: var(--radius);
      font-size: 14px; font-weight: 500; color: var(--muted);
      transition: color .2s, background .2s;
    }
    .nav-links a:hover,
    .nav-links a.active { color: var(--text); background: var(--bg2); }
    .nav-links a.active  { color: var(--blue); }

    .nav-right {
      margin-left: auto; display: flex; align-items: center; gap: 12px;
    }

    .search-box {
      display: flex; align-items: center; gap: 8px;
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 7px 14px;
      transition: border-color .2s, box-shadow .2s;
    }
    .search-box:focus-within {
      border-color: var(--blue);
      box-shadow: var(--shadow);
    }
    .search-box svg { color: var(--muted); flex-shrink: 0; }
    .search-box input {
      border: none; background: none; outline: none;
      font-family: 'Outfit', sans-serif;
      font-size: 14px; color: var(--text); width: 160px;
    }
    .search-box input::placeholder { color: var(--muted); }

    .nav-icon {
      width: 36px; height: 36px; border-radius: var(--radius);
      display: flex; align-items: center; justify-content: center;
      background: var(--white); color: var(--muted); cursor: pointer;
      border: 1px solid var(--border); transition: background .2s, box-shadow .2s;
    }
    .nav-icon:hover {
      background: var(--bg2);
      box-shadow: var(--shadow);
    }

    /* ── NAV PROFILE / AVATAR ───────────────────── */
    .nav-profile { position: relative; }

    .nav-avatar-btn {
      display: flex; align-items: center; gap: 8px;
      background: none; border: 1px solid var(--border);
      border-radius: var(--radius-full);
      padding: 4px 12px 4px 4px;
      cursor: pointer; font-family: 'Outfit', sans-serif;
      transition: background .2s, border-color .2s, box-shadow .2s;
    }
    .nav-avatar-btn:hover {
      background: var(--bg2);
      border-color: var(--border-strong);
      box-shadow: var(--shadow);
    }
    .nav-avatar-btn[aria-expanded="true"] {
      background: var(--bg2);
      border-color: var(--blue);
    }

    .nav-avatar-img,
    .nav-avatar-initials {
      width: 28px; height: 28px; border-radius: 50%;
      flex-shrink: 0; overflow: hidden;
    }
    .nav-avatar-img { object-fit: cover; display: block; }
    .nav-avatar-initials {
      display: flex; align-items: center; justify-content: center;
      background: var(--blue); color: #fff;
      font-size: 12px; font-weight: 700; letter-spacing: 0;
    }

    .nav-avatar-name {
      font-size: 13px; font-weight: 600; color: var(--text);
      max-width: 100px; overflow: hidden;
      text-overflow: ellipsis; white-space: nowrap;
    }
    .nav-avatar-chevron {
      color: var(--muted);
      transition: transform .2s;
    }
    .nav-avatar-btn[aria-expanded="true"] .nav-avatar-chevron {
      transform: rotate(180deg);
    }

    /* ── DROPDOWN ────────────────────────────────── */
    .nav-dropdown {
      position: absolute; top: calc(100% + 8px); right: 0;
      min-width: 220px;
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-lg);
      padding: 6px;
      opacity: 0; pointer-events: none;
      transform: translateY(-6px);
      transition: opacity .18s ease, transform .18s ease;
      z-index: 200;
    }
    .nav-dropdown.open {
      opacity: 1; pointer-events: auto;
      transform: translateY(0);
    }

    .nav-dropdown-header {
      display: flex; align-items: center; gap: 10px;
      padding: 8px 10px 10px;
    }
    .nav-dropdown-avatar-img,
    .nav-dropdown-initials {
      width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    }
    .nav-dropdown-avatar-img { object-fit: cover; }
    .nav-dropdown-initials {
      display: flex; align-items: center; justify-content: center;
      background: var(--blue); color: #fff;
      font-size: 15px; font-weight: 700;
    }
    .nav-dropdown-name  { font-size: 13px; font-weight: 700; color: var(--text); }
    .nav-dropdown-email { font-size: 11px; color: var(--muted); margin-top: 1px; }

    .nav-dropdown-divider { border: none; border-top: 1px solid var(--border); margin: 4px 0; }

    .nav-dropdown-item {
      display: flex; align-items: center; gap: 9px;
      padding: 8px 10px; border-radius: var(--radius-sm);
      font-size: 13px; font-weight: 500; color: var(--text);
      transition: background .15s, color .15s;
      cursor: pointer;
    }
    .nav-dropdown-item:hover { background: var(--bg2); }
    .nav-dropdown-item svg   { color: var(--muted); flex-shrink: 0; }
    .nav-dropdown-item--danger       { color: #dc2626; }
    .nav-dropdown-item--danger:hover { background: #fef2f2; }
    .nav-dropdown-item--danger svg   { color: #dc2626; }

    @media (max-width: 768px) {
      .nav-avatar-name { display: none; }
    }

    @media (max-width: 768px) {
      .nav-links,
      .search-box { display: none; }
    }
  </style>
</head>
<body>
<nav>
  <div class="nav-inner">
    <a href="<?php echo $base_url; ?>index.php" class="nav-logo">Thurz<span>Shop</span></a>
    <ul class="nav-links">
      <li><a href="<?php echo $base_url; ?>index.php" 
             class="<?php echo (isset($active_page) && $active_page == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
      <li><a href="#"
             class="<?php echo (isset($active_page) && $active_page == 'cektransaksi') ? 'active' : ''; ?>">Cek Transaksi</a></li>
      <li><a href="#"
             class="<?php echo (isset($active_page) && $active_page == 'review') ? 'active' : ''; ?>">Reviews</a></li>
    </ul>
    <div class="nav-right">
      <?php if (isset($_SESSION['user'])): ?>
        <?php
          $nav_user     = $_SESSION['user'];
          $nav_username = htmlspecialchars($nav_user['username']);
          $nav_initial  = strtoupper(mb_substr($nav_user['username'], 0, 1));
          $nav_photo    = !empty($nav_user['profile_photo']) ? htmlspecialchars($nav_user['profile_photo']) : null;
        ?>
        <div class="nav-profile" id="navProfile">
          <button class="nav-avatar-btn" id="navAvatarBtn" aria-haspopup="true" aria-expanded="false" title="<?php echo $nav_username; ?>">
            <?php if ($nav_photo): ?>
              <img src="<?php echo $nav_photo; ?>" alt="<?php echo $nav_username; ?>" class="nav-avatar-img" />
            <?php else: ?>
              <span class="nav-avatar-initials"><?php echo $nav_initial; ?></span>
            <?php endif; ?>
            <span class="nav-avatar-name"><?php echo $nav_username; ?></span>
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="nav-avatar-chevron">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </button>

          <div class="nav-dropdown" id="navDropdown" role="menu">
            <div class="nav-dropdown-header">
              <?php if ($nav_photo): ?>
                <img src="<?php echo $nav_photo; ?>" alt="<?php echo $nav_username; ?>" class="nav-dropdown-avatar-img" />
              <?php else: ?>
                <span class="nav-dropdown-initials"><?php echo $nav_initial; ?></span>
              <?php endif; ?>
              <div>
                <div class="nav-dropdown-name"><?php echo $nav_username; ?></div>
                <div class="nav-dropdown-email"><?php echo htmlspecialchars($nav_user['email']); ?></div>
              </div>
            </div>
            <hr class="nav-dropdown-divider" />
            <a href="#" class="nav-dropdown-item" role="menuitem">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Profil Saya
            </a>
            <a href="#" class="nav-dropdown-item" role="menuitem">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/></svg>
              Transaksi Saya
            </a>
            <hr class="nav-dropdown-divider" />
            <a href="<?php echo $base_url; ?>pages/logout.php" class="nav-dropdown-item nav-dropdown-item--danger" role="menuitem">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
              Keluar
            </a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?php echo $base_url; ?>pages/login.php" class="btn btn-primary">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<script>
/* ── Navbar: highlight active link on scroll ── */
document.addEventListener('DOMContentLoaded', () => {
  const sections = document.querySelectorAll('section[id], div[id]');
  const navLinks = document.querySelectorAll('.nav-links a');

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

  /* ── Mobile menu toggle ──────────────────────── */
  const menuToggle = document.getElementById('menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');

  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener('click', () => {
      const isOpen = mobileMenu.style.display === 'flex';
      mobileMenu.style.display = isOpen ? 'none' : 'flex';
      menuToggle.setAttribute('aria-expanded', String(!isOpen));
    });
  }

  /* ── Profile avatar dropdown ─────────────────── */
  const avatarBtn  = document.getElementById('navAvatarBtn');
  const dropdown   = document.getElementById('navDropdown');

  if (avatarBtn && dropdown) {
    avatarBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = dropdown.classList.contains('open');
      dropdown.classList.toggle('open', !isOpen);
      avatarBtn.setAttribute('aria-expanded', String(!isOpen));
    });

    // Close when clicking outside
    document.addEventListener('click', () => {
      dropdown.classList.remove('open');
      avatarBtn.setAttribute('aria-expanded', 'false');
    });

    // Prevent closing when clicking inside dropdown
    dropdown.addEventListener('click', (e) => e.stopPropagation());
  }
});
</script>
