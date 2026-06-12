<?php
// ── Auth guard ───────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../pages/login.php');
    exit;
}

// ── Page config ──────────────────────────────────────────────────────────────
$page_title  = 'Admin Dashboard — ThurzShop';
$active_page = 'dashboard';

// ── DB ───────────────────────────────────────────────────────────────────────
include '../../includes/db.php';
include '../../includes/header.php';

// ── Stats ────────────────────────────────────────────────────────────────────
$total_users    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users"))['c'];
$total_listings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM account_listing"))['c'];
$total_orders   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders"))['c'];
$total_revenue  = (float) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) AS s FROM payment WHERE payment_status='confirmed'"))['s'];

$active_listings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM account_listing WHERE status='ready'"))['c'];
$sold_listings   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM account_listing WHERE status='sold'"))['c'];
$pending_orders  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders WHERE order_status='pending'"))['c'];
$banned_users    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE status='banned'"))['c'];

// ── Recent data ───────────────────────────────────────────────────────────────
$recent_users = mysqli_fetch_all(mysqli_query($conn,
    "SELECT ID_User, username, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 10"
), MYSQLI_ASSOC);

$recent_listings = mysqli_fetch_all(mysqli_query($conn,
    "SELECT al.*, u.username, g.name AS game_name
     FROM account_listing al
     JOIN users u ON al.user_id = u.ID_User
     JOIN games g ON al.game_id = g.id
     ORDER BY al.created_at DESC LIMIT 10"
), MYSQLI_ASSOC);

$recent_orders = mysqli_fetch_all(mysqli_query($conn,
    "SELECT o.*, u.username AS buyer, al.title AS listing_title, g.name AS game_name,
            p.payment_status, p.payment_method, p.paid_at
     FROM orders o
     JOIN users u ON o.user_id = u.ID_User
     JOIN account_listing al ON o.listing_id = al.listing_id
     JOIN games g ON al.game_id = g.id
     LEFT JOIN payment p ON o.order_id = p.order_id
     ORDER BY o.created_at DESC LIMIT 10"
), MYSQLI_ASSOC);

// ── All users (for management tab) ───────────────────────────────────────────
$all_users = mysqli_fetch_all(mysqli_query($conn,
    "SELECT u.*, 
            COUNT(DISTINCT al.listing_id) AS listing_count,
            COUNT(DISTINCT o.order_id) AS order_count
     FROM users u
     LEFT JOIN account_listing al ON u.ID_User = al.user_id
     LEFT JOIN orders o ON u.ID_User = o.user_id
     GROUP BY u.ID_User
     ORDER BY u.created_at DESC"
), MYSQLI_ASSOC);

// ── All listings (for management tab) ────────────────────────────────────────
$all_listings = mysqli_fetch_all(mysqli_query($conn,
    "SELECT al.*, u.username, g.name AS game_name
     FROM account_listing al
     JOIN users u ON al.user_id = u.ID_User
     JOIN games g ON al.game_id = g.id
     ORDER BY al.created_at DESC"
), MYSQLI_ASSOC);

// ── Games list ────────────────────────────────────────────────────────────────
$all_games = mysqli_fetch_all(mysqli_query($conn,
    "SELECT g.*, COUNT(al.listing_id) AS listing_count
     FROM games g
     LEFT JOIN account_listing al ON g.id = al.game_id
     GROUP BY g.id
     ORDER BY g.name ASC"
), MYSQLI_ASSOC);

// ── Helpers ───────────────────────────────────────────────────────────────────
function rupiah(float $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function listing_badge(string $s): string {
    return match($s) {
        'sold'  => '<span class="adm-badge adm-badge--sold">Terjual</span>',
        default => '<span class="adm-badge adm-badge--ready">Tersedia</span>',
    };
}
function order_badge(string $s): string {
    return match($s) {
        'confirmed' => '<span class="adm-badge adm-badge--confirmed">Dikonfirmasi</span>',
        'paid'      => '<span class="adm-badge adm-badge--paid">Dibayar</span>',
        'cancelled' => '<span class="adm-badge adm-badge--cancelled">Dibatalkan</span>',
        default     => '<span class="adm-badge adm-badge--pending">Pending</span>',
    };
}
function user_badge(string $s): string {
    return match($s) {
        'banned' => '<span class="adm-badge adm-badge--cancelled">Banned</span>',
        default  => '<span class="adm-badge adm-badge--confirmed">Aktif</span>',
    };
}
function role_badge(string $r): string {
    return match($r) {
        'admin' => '<span class="adm-badge adm-badge--admin">Admin</span>',
        default => '<span class="adm-badge adm-badge--user">User</span>',
    };
}

$admin_name = htmlspecialchars($_SESSION['user']['username']);
?>

<!-- ── Admin CSS ───────────────────────────────────────────────────────────── -->
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/page/css/admindashboard.css" />

<!-- ══════════════════════════════════════════════
     ADMIN WRAPPER
══════════════════════════════════════════════ -->
<div class="adm-wrapper">

  <!-- ══════════ SIDEBAR ══════════ -->
  <aside class="adm-sidebar">
    <div class="adm-sidebar-brand">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
      </svg>
      Admin Panel
    </div>

    <div class="adm-sidebar-admin">
      <div class="adm-sidebar-avatar"><?php echo strtoupper(mb_substr($admin_name, 0, 2)); ?></div>
      <div>
        <div class="adm-sidebar-name"><?php echo $admin_name; ?></div>
        <div class="adm-sidebar-role">Administrator</div>
      </div>
    </div>

    <nav class="adm-nav" aria-label="Admin menu">
      <button class="adm-nav-item active" data-panel="overview">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Overview
      </button>
      <button class="adm-nav-item" data-panel="users">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Manajemen User
        <span class="adm-nav-badge"><?php echo $total_users; ?></span>
      </button>
      <button class="adm-nav-item" data-panel="listings">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/></svg>
        Manajemen Listing
        <span class="adm-nav-badge"><?php echo $total_listings; ?></span>
      </button>
      <button class="adm-nav-item" data-panel="orders">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Manajemen Order
        <span class="adm-nav-badge"><?php echo $total_orders; ?></span>
      </button>
      <button class="adm-nav-item" data-panel="games">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M12 12h.01M7 12h.01M17 12h.01M7 9v6M10 12H4"/></svg>
        Manajemen Game
        <span class="adm-nav-badge"><?php echo count($all_games); ?></span>
      </button>
      <hr class="adm-nav-divider">
      <a href="<?php echo $base_url; ?>index.php" class="adm-nav-item">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Ke Beranda
      </a>
      <a href="<?php echo $base_url; ?>pages/logout.php" class="adm-nav-item adm-nav-item--danger">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        Keluar
      </a>
    </nav>
  </aside>
  <!-- /SIDEBAR -->

  <!-- ══════════ MAIN ══════════ -->
  <main class="adm-main">

    <!-- ════ PANEL: OVERVIEW ════ -->
    <section id="panel-overview" class="adm-panel active">

      <div class="adm-page-header">
        <div>
          <h2 class="adm-page-title">Overview</h2>
          <p class="adm-page-sub">Ringkasan aktivitas ThurzShop per hari ini</p>
        </div>
        <div class="adm-page-header-right">
          <span class="adm-date-badge">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?php echo date('d M Y'); ?>
          </span>
        </div>
      </div>

      <!-- Stat cards -->
      <div class="adm-stats">
        <div class="adm-stat">
          <div class="adm-stat-icon blue">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <div class="adm-stat-body">
            <div class="adm-stat-value"><?php echo $total_users; ?></div>
            <div class="adm-stat-label">Total User</div>
          </div>
          <div class="adm-stat-sub"><?php echo $banned_users; ?> banned</div>
        </div>
        <div class="adm-stat">
          <div class="adm-stat-icon green">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/></svg>
          </div>
          <div class="adm-stat-body">
            <div class="adm-stat-value"><?php echo $total_listings; ?></div>
            <div class="adm-stat-label">Total Listing</div>
          </div>
          <div class="adm-stat-sub"><?php echo $active_listings; ?> aktif · <?php echo $sold_listings; ?> terjual</div>
        </div>
        <div class="adm-stat">
          <div class="adm-stat-icon orange">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          </div>
          <div class="adm-stat-body">
            <div class="adm-stat-value"><?php echo $total_orders; ?></div>
            <div class="adm-stat-label">Total Order</div>
          </div>
          <div class="adm-stat-sub"><?php echo $pending_orders; ?> pending</div>
        </div>
        <div class="adm-stat">
          <div class="adm-stat-icon purple">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div class="adm-stat-body">
            <div class="adm-stat-value"><?php echo rupiah($total_revenue); ?></div>
            <div class="adm-stat-label">Total Pendapatan</div>
          </div>
          <div class="adm-stat-sub">dari pembayaran confirmed</div>
        </div>
      </div>

      <!-- Two-column recent activities -->
      <div class="adm-two-col">

        <!-- Recent Users -->
        <div class="adm-card">
          <div class="adm-card-header">
            <span class="adm-card-title">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              User Terbaru
            </span>
            <button class="adm-nav-item adm-link-btn" data-panel="users">Lihat Semua →</button>
          </div>
          <div class="adm-table-wrap">
            <table class="adm-table">
              <thead>
                <tr><th>Username</th><th>Role</th><th>Status</th><th>Terdaftar</th></tr>
              </thead>
              <tbody>
                <?php foreach ($recent_users as $u): ?>
                <tr>
                  <td>
                    <div class="adm-user-cell">
                      <div class="adm-mini-avatar"><?php echo strtoupper(mb_substr($u['username'],0,1)); ?></div>
                      <div>
                        <div class="adm-cell-name"><?php echo htmlspecialchars($u['username']); ?></div>
                        <div class="adm-cell-sub"><?php echo htmlspecialchars($u['email']); ?></div>
                      </div>
                    </div>
                  </td>
                  <td><?php echo role_badge($u['role']); ?></td>
                  <td><?php echo user_badge($u['status']); ?></td>
                  <td class="adm-cell-muted"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="adm-card">
          <div class="adm-card-header">
            <span class="adm-card-title">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
              Order Terbaru
            </span>
            <button class="adm-nav-item adm-link-btn" data-panel="orders">Lihat Semua →</button>
          </div>
          <div class="adm-table-wrap">
            <table class="adm-table">
              <thead>
                <tr><th>Pembeli</th><th>Listing</th><th>Harga</th><th>Status</th></tr>
              </thead>
              <tbody>
                <?php foreach ($recent_orders as $o): ?>
                <tr>
                  <td class="adm-cell-name"><?php echo htmlspecialchars($o['buyer']); ?></td>
                  <td>
                    <div class="adm-cell-name"><?php echo htmlspecialchars(mb_strimwidth($o['listing_title'],0,28,'…')); ?></div>
                    <div class="adm-cell-sub"><?php echo htmlspecialchars($o['game_name']); ?></div>
                  </td>
                  <td class="adm-cell-price"><?php echo rupiah((float)$o['total_price']); ?></td>
                  <td><?php echo order_badge($o['payment_status'] ?? $o['order_status']); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div><!-- /two-col -->

    </section>
    <!-- /OVERVIEW -->


    <!-- ════ PANEL: USERS ════ -->
    <section id="panel-users" class="adm-panel">

      <div class="adm-page-header">
        <div>
          <h2 class="adm-page-title">Manajemen User</h2>
          <p class="adm-page-sub"><?php echo $total_users; ?> user terdaftar</p>
        </div>
        <div class="adm-search-box">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchUsers" placeholder="Cari username atau email…">
        </div>
      </div>

      <div class="adm-card">
        <div class="adm-table-wrap">
          <table class="adm-table" id="tableUsers">
            <thead>
              <tr><th>#</th><th>User</th><th>No. HP</th><th>Role</th><th>Status</th><th>Listing</th><th>Terdaftar</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php foreach ($all_users as $i => $u): ?>
              <tr data-search="<?php echo strtolower($u['username'].' '.$u['email']); ?>">
                <td class="adm-cell-muted"><?php echo $i + 1; ?></td>
                <td>
                  <div class="adm-user-cell">
                    <div class="adm-mini-avatar"><?php echo strtoupper(mb_substr($u['username'],0,1)); ?></div>
                    <div>
                      <div class="adm-cell-name"><?php echo htmlspecialchars($u['username']); ?></div>
                      <div class="adm-cell-sub"><?php echo htmlspecialchars($u['email']); ?></div>
                    </div>
                  </div>
                </td>
                <td class="adm-cell-muted"><?php echo htmlspecialchars($u['phone_number'] ?? '-'); ?></td>
                <td><?php echo role_badge($u['role']); ?></td>
                <td><?php echo user_badge($u['status']); ?></td>
                <td class="adm-cell-muted"><?php echo $u['listing_count']; ?></td>
                <td class="adm-cell-muted"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                <td>
                  <div class="adm-action-group">
                    <?php if ($u['status'] === 'active'): ?>
                      <button class="adm-action-btn adm-action-btn--warn"
                              data-action="ban-user"
                              data-id="<?php echo $u['ID_User']; ?>"
                              data-name="<?php echo htmlspecialchars($u['username']); ?>"
                              title="Ban User">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                      </button>
                    <?php else: ?>
                      <button class="adm-action-btn adm-action-btn--success"
                              data-action="unban-user"
                              data-id="<?php echo $u['ID_User']; ?>"
                              data-name="<?php echo htmlspecialchars($u['username']); ?>"
                              title="Unban User">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                      </button>
                    <?php endif; ?>
                    <?php if ($u['role'] !== 'admin'): ?>
                    <button class="adm-action-btn adm-action-btn--danger"
                            data-action="delete-user"
                            data-id="<?php echo $u['ID_User']; ?>"
                            data-name="<?php echo htmlspecialchars($u['username']); ?>"
                            title="Hapus User">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </section>
    <!-- /USERS -->


    <!-- ════ PANEL: LISTINGS ════ -->
    <section id="panel-listings" class="adm-panel">

      <div class="adm-page-header">
        <div>
          <h2 class="adm-page-title">Manajemen Listing</h2>
          <p class="adm-page-sub"><?php echo $total_listings; ?> listing · <?php echo $active_listings; ?> aktif · <?php echo $sold_listings; ?> terjual</p>
        </div>
        <div class="adm-search-box">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchListings" placeholder="Cari judul atau seller…">
        </div>
      </div>

      <div class="adm-card">
        <div class="adm-table-wrap">
          <table class="adm-table" id="tableListings">
            <thead>
              <tr><th>#</th><th>Listing</th><th>Seller</th><th>Game</th><th>Harga</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php foreach ($all_listings as $i => $l): ?>
              <tr data-search="<?php echo strtolower($l['title'].' '.$l['username'].' '.$l['game_name']); ?>">
                <td class="adm-cell-muted"><?php echo $i + 1; ?></td>
                <td>
                  <div class="adm-cell-name"><?php echo htmlspecialchars(mb_strimwidth($l['title'],0,36,'…')); ?></div>
                  <?php if ($l['rank']): ?><div class="adm-cell-sub">Rank: <?php echo htmlspecialchars($l['rank']); ?><?php if ($l['level']): ?> · Lv <?php echo $l['level']; ?><?php endif; ?></div><?php endif; ?>
                </td>
                <td class="adm-cell-name"><?php echo htmlspecialchars($l['username']); ?></td>
                <td class="adm-cell-muted"><?php echo htmlspecialchars($l['game_name']); ?></td>
                <td class="adm-cell-price"><?php echo rupiah((float)$l['price']); ?></td>
                <td><?php echo listing_badge($l['status']); ?></td>
                <td class="adm-cell-muted"><?php echo date('d M Y', strtotime($l['created_at'])); ?></td>
                <td>
                  <div class="adm-action-group">
                    <a href="<?php echo $base_url; ?>pages/listing-detail.php?id=<?php echo $l['listing_id']; ?>"
                       class="adm-action-btn" title="Lihat Detail" target="_blank">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <button class="adm-action-btn adm-action-btn--danger"
                            data-action="delete-listing"
                            data-id="<?php echo $l['listing_id']; ?>"
                            data-name="<?php echo htmlspecialchars($l['title']); ?>"
                            title="Hapus Listing">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </section>
    <!-- /LISTINGS -->


    <!-- ════ PANEL: ORDERS ════ -->
    <section id="panel-orders" class="adm-panel">

      <div class="adm-page-header">
        <div>
          <h2 class="adm-page-title">Manajemen Order</h2>
          <p class="adm-page-sub"><?php echo $total_orders; ?> total transaksi · <?php echo $pending_orders; ?> pending</p>
        </div>
        <div class="adm-search-box">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchOrders" placeholder="Cari pembeli atau listing…">
        </div>
      </div>

      <div class="adm-card">
        <div class="adm-table-wrap">
          <table class="adm-table" id="tableOrders">
            <thead>
              <tr><th>#</th><th>Pembeli</th><th>Listing</th><th>Game</th><th>Harga</th><th>Metode</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php
              $all_orders = mysqli_fetch_all(mysqli_query($conn,
                  "SELECT o.*, u.username AS buyer, al.title AS listing_title, g.name AS game_name,
                          p.payment_status, p.payment_method, p.paid_at, p.payment_proof
                   FROM orders o
                   JOIN users u ON o.user_id = u.ID_User
                   JOIN account_listing al ON o.listing_id = al.listing_id
                   JOIN games g ON al.game_id = g.id
                   LEFT JOIN payment p ON o.order_id = p.order_id
                   ORDER BY o.created_at DESC"
              ), MYSQLI_ASSOC);
              foreach ($all_orders as $i => $o):
                $sts = $o['payment_status'] ?? $o['order_status'];
              ?>
              <tr data-search="<?php echo strtolower($o['buyer'].' '.$o['listing_title'].' '.$o['game_name']); ?>">
                <td class="adm-cell-muted"><?php echo $i + 1; ?></td>
                <td class="adm-cell-name"><?php echo htmlspecialchars($o['buyer']); ?></td>
                <td>
                  <div class="adm-cell-name"><?php echo htmlspecialchars(mb_strimwidth($o['listing_title'],0,32,'…')); ?></div>
                </td>
                <td class="adm-cell-muted"><?php echo htmlspecialchars($o['game_name']); ?></td>
                <td class="adm-cell-price"><?php echo rupiah((float)$o['total_price']); ?></td>
                <td class="adm-cell-muted"><?php echo htmlspecialchars($o['payment_method'] ?? '-'); ?></td>
                <td><?php echo order_badge($sts); ?></td>
                <td class="adm-cell-muted"><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                <td>
                  <div class="adm-action-group">
                    <?php if ($sts === 'paid'): ?>
                    <button class="adm-action-btn adm-action-btn--success"
                            data-action="confirm-order"
                            data-id="<?php echo $o['order_id']; ?>"
                            title="Konfirmasi Pembayaran">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                    <?php endif; ?>
                    <?php if ($o['payment_proof']): ?>
                    <a href="<?php echo htmlspecialchars($base_url . $o['payment_proof']); ?>"
                       class="adm-action-btn" title="Lihat Bukti Bayar" target="_blank">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if ($sts === 'pending' || $sts === 'cancelled'): ?>
                    <button class="adm-action-btn adm-action-btn--danger"
                            data-action="cancel-order"
                            data-id="<?php echo $o['order_id']; ?>"
                            title="Batalkan Order">
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </section>
    <!-- /ORDERS -->


    <!-- ════ PANEL: GAMES ════ -->
    <section id="panel-games" class="adm-panel">

      <div class="adm-page-header">
        <div>
          <h2 class="adm-page-title">Manajemen Game</h2>
          <p class="adm-page-sub"><?php echo count($all_games); ?> game terdaftar</p>
        </div>
        <button class="btn btn-primary" id="btnAddGame" style="font-size:13px;padding:9px 18px;">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Game
        </button>
      </div>

      <div class="adm-games-grid">
        <?php foreach ($all_games as $g): ?>
        <div class="adm-game-card">
          <div class="adm-game-img-wrap">
            <img src="<?php echo htmlspecialchars($g['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($g['name']); ?>"
                 onerror="this.src='';this.closest('.adm-game-img-wrap').innerHTML='<div class=adm-game-img-placeholder>🎮</div>'">
          </div>
          <div class="adm-game-info">
            <div class="adm-game-name"><?php echo htmlspecialchars($g['name']); ?></div>
            <div class="adm-game-meta"><?php echo htmlspecialchars($g['genre']); ?> · <?php echo htmlspecialchars($g['platform']); ?></div>
            <div class="adm-game-count"><?php echo $g['listing_count']; ?> listing</div>
          </div>
          <div class="adm-game-actions">
            <button class="adm-action-btn adm-action-btn--danger"
                    data-action="delete-game"
                    data-id="<?php echo $g['id']; ?>"
                    data-name="<?php echo htmlspecialchars($g['name']); ?>"
                    title="Hapus Game">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </section>
    <!-- /GAMES -->

  </main>
  <!-- /MAIN -->

</div>
<!-- /adm-wrapper -->


<!-- ══════ MODAL: TAMBAH GAME ══════ -->
<div class="adm-modal-backdrop" id="addGameModal" role="dialog" aria-modal="true">
  <div class="adm-modal">
    <div class="adm-modal-header">
      <span class="adm-modal-title">Tambah Game Baru</span>
      <button class="adm-modal-close" id="btnCloseGameModal" aria-label="Tutup">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="adm-modal-body">
      <form id="formAddGame" action="<?php echo $base_url; ?>pages/admin/add_game.php" method="POST">
        <div class="db-form-grid">
          <div class="db-form-group full">
            <label for="gm_name">Nama Game <span style="color:#dc2626">*</span></label>
            <input type="text" id="gm_name" name="name" required placeholder="cth: Valorant">
          </div>
          <div class="db-form-group">
            <label for="gm_genre">Genre <span style="color:#dc2626">*</span></label>
            <select id="gm_genre" name="genre" required>
              <option value="">-- Pilih Genre --</option>
              <option>FPS</option><option>MOBA</option><option>Battle Royale</option>
              <option>RPG</option><option>Strategy</option><option>Sports</option><option>Lainnya</option>
            </select>
          </div>
          <div class="db-form-group">
            <label for="gm_platform">Platform <span style="color:#dc2626">*</span></label>
            <select id="gm_platform" name="platform" required>
              <option value="">-- Pilih Platform --</option>
              <option>PC</option><option>Mobile</option><option>PC & Mobile</option><option>Console</option>
            </select>
          </div>
          <div class="db-form-group full">
            <label for="gm_image">URL Gambar <span style="color:#dc2626">*</span></label>
            <input type="url" id="gm_image" name="image_url" required placeholder="https://…">
          </div>
        </div>
        <div class="db-form-actions" style="margin-top:var(--space-2);padding-top:var(--space-2);border-top:1px solid var(--border);">
          <button type="button" class="btn btn-outline" style="font-size:13px;padding:8px 16px;"
                  onclick="document.getElementById('addGameModal').classList.remove('open');document.body.style.overflow=''">
            Batal
          </button>
          <button type="submit" class="btn btn-primary" style="font-size:13px;padding:8px 20px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- ── Admin JS ────────────────────────────────────────────────────────────── -->
<script>
  /* Expose base_url for JS */
  const BASE_URL = '<?php echo $base_url; ?>';
</script>
<script src="<?php echo $base_url; ?>assets/page/js/admindashboard.js"></script>

<?php include '../../includes/footer.php'; ?>
