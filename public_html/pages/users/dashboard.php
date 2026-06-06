<?php
/** @var mysqli $conn */
// ── Auth guard ──────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../pages/login.php');
    exit;
}

// ── Page config ─────────────────────────────────────────────────────────────
$page_title  = 'Dashboard Saya — ThurzShop';
$active_page = 'dashboard';

// ── DB & user ───────────────────────────────────────────────────────────────
include '../../includes/db.php';
include '../../includes/header.php';

$user_id = (int) $_SESSION['user']['ID_User'];

// ── Fetch: user detail (fresh from DB) ──────────────────────────────────────
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE ID_User = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

// Kalau user tidak ditemukan di DB, paksa logout
if (!$user) {
    session_destroy();
    header('Location: ../../pages/login.php');
    exit;
}

// ── Fetch: listings milik user (Penjualan) ───────────────────────────────────
$stmt2 = mysqli_prepare($conn,
    "SELECT al.*, g.name AS game_name, g.image_url AS game_image
     FROM account_listing al
     JOIN games g ON al.game_id = g.id
     WHERE al.user_id = ?
     ORDER BY al.created_at DESC"
);
mysqli_stmt_bind_param($stmt2, 'i', $user_id);
mysqli_stmt_execute($stmt2);
$listings = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);

$total_listings = count($listings);
$sold_count     = count(array_filter($listings, fn($l) => $l['status'] === 'sold'));
$active_count   = $total_listings - $sold_count;

// ── Fetch: pembelian milik user ──────────────────────────────────────────────
$stmt3 = mysqli_prepare($conn,
    "SELECT o.*, al.title, al.rank, al.level, g.name AS game_name, g.image_url AS game_image,
            p.payment_status, p.payment_method, p.paid_at
     FROM orders o
     JOIN account_listing al ON o.listing_id = al.listing_id
     JOIN games g ON al.game_id = g.id
     LEFT JOIN payment p ON o.order_id = p.order_id
     WHERE o.user_id = ?
     ORDER BY o.created_at DESC"
);
mysqli_stmt_bind_param($stmt3, 'i', $user_id);
mysqli_stmt_execute($stmt3);
$purchases = mysqli_fetch_all(mysqli_stmt_get_result($stmt3), MYSQLI_ASSOC);

$purchase_count     = count($purchases);
$confirmed_count    = count(array_filter($purchases, fn($p) => $p['payment_status'] === 'confirmed'));

// ── Fetch: games list (untuk form tambah listing) ────────────────────────────
$games_result = mysqli_query($conn, "SELECT id, name FROM games ORDER BY name ASC");
$games_list   = mysqli_fetch_all($games_result, MYSQLI_ASSOC);

// ── Helper: format rupiah ────────────────────────────────────────────────────
function rupiah(float $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

// ── Helper: status badge HTML ────────────────────────────────────────────────
function listing_badge(string $status): string {
    return match ($status) {
        'sold'  => '<span class="badge badge-sold">● Terjual</span>',
        default => '<span class="badge badge-ready">● Tersedia</span>',
    };
}
function order_badge(string $status): string {
    return match ($status) {
        'confirmed' => '<span class="badge badge-confirmed">✓ Terkonfirmasi</span>',
        'paid'      => '<span class="badge badge-paid">● Dibayar</span>',
        'cancelled' => '<span class="badge badge-cancelled">✕ Dibatalkan</span>',
        default     => '<span class="badge badge-pending">◌ Menunggu</span>',
    };
}

$initial = strtoupper(mb_substr($user['username'], 0, 2));
?>

<!-- ── Dashboard CSS & page-specific link ──────────────────────────────────── -->
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/page/css/userdashboard.css" />

<!-- ══════════════════════════════════════════════
     DASHBOARD WRAPPER
══════════════════════════════════════════════ -->
<div class="dashboard-wrapper">

  <!-- ══════════ SIDEBAR ══════════ -->
  <aside class="db-sidebar">

    <!-- Profile card -->
    <div class="db-profile-card">
      <div class="db-avatar"><?php echo htmlspecialchars($initial); ?></div>
      <div class="db-profile-name"><?php echo htmlspecialchars($user['username']); ?></div>
      <div class="db-profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
      <span class="db-profile-badge">
        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        Aktif
      </span>
    </div>

    <!-- Sidebar navigation -->
    <nav class="db-nav" aria-label="Dashboard menu">
      <button class="db-nav-item" data-panel="overview">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Overview
      </button>
      <button class="db-nav-item" data-panel="transaksi">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/></svg>
        Transaksi
      </button>
      <button class="db-nav-item" data-panel="profil">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profil Saya
      </button>
      <hr class="db-nav-divider">
      <a href="<?php echo $base_url; ?>pages/logout.php" class="db-nav-item db-nav-item--danger">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        Keluar
      </a>
    </nav>

  </aside>
  <!-- /SIDEBAR -->

  <!-- ══════════ MAIN ══════════ -->
  <main class="db-main">

    <!-- ─────────────── PANEL: OVERVIEW ─────────────── -->
    <section id="panel-overview" class="db-panel">

      <div class="db-card">
        <div class="db-card-header">
          <span class="db-card-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Selamat datang, <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>!
          </span>
          <span style="font-size:12px;color:var(--muted);">
            Bergabung: <?php echo date('d M Y', strtotime($user['created_at'])); ?>
          </span>
        </div>
        <div class="db-alert db-alert-info">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Pantau semua aktivitas jual-beli akun game kamu di sini. Gunakan menu <strong>Transaksi</strong> untuk melihat detail penjualan dan pembelian.
        </div>
      </div>

      <!-- Stats -->
      <div class="db-stats">
        <div class="db-stat">
          <div class="db-stat-value blue"><?php echo $total_listings; ?></div>
          <div class="db-stat-label">Total Listing</div>
        </div>
        <div class="db-stat">
          <div class="db-stat-value green"><?php echo $sold_count; ?></div>
          <div class="db-stat-label">Akun Terjual</div>
        </div>
        <div class="db-stat">
          <div class="db-stat-value orange"><?php echo $purchase_count; ?></div>
          <div class="db-stat-label">Pembelian</div>
        </div>
      </div>

      <!-- Recent listings -->
      <div class="db-card">
        <div class="db-card-header">
          <span class="db-card-title">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Listing Terbaru
          </span>
          <button class="db-nav-item" data-panel="transaksi" style="padding:6px 12px;font-size:12px;color:var(--blue);">Lihat Semua →</button>
        </div>

        <?php if (empty($listings)): ?>
          <div class="db-empty">
            <div class="db-empty-icon">📦</div>
            <strong>Belum ada listing</strong>
            <p>Kamu belum pernah menjual akun. Mulai sekarang!</p>
          </div>
        <?php else: ?>
          <div class="db-item-list">
            <?php foreach (array_slice($listings, 0, 3) as $item): ?>
            <div class="db-item">
              <img src="<?php echo htmlspecialchars($item['game_image']); ?>"
                   alt="<?php echo htmlspecialchars($item['game_name']); ?>"
                   class="db-item-thumb"
                   onerror="this.src='<?php echo $base_url; ?>assets/img/placeholder.png'">
              <div class="db-item-info">
                <div class="db-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                <div class="db-item-meta">
                  <span><?php echo htmlspecialchars($item['game_name']); ?></span>
                  <?php if ($item['rank']): ?>
                    <span>· <?php echo htmlspecialchars($item['rank']); ?></span>
                  <?php endif; ?>
                  <?php if ($item['level']): ?>
                    <span>· Lv <?php echo $item['level']; ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <?php echo listing_badge($item['status']); ?>
              <div class="db-item-price">
                <?php echo rupiah((float)$item['price']); ?>
                <small><?php echo date('d M Y', strtotime($item['created_at'])); ?></small>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </section>
    <!-- /OVERVIEW -->


    <!-- ─────────────── PANEL: TRANSAKSI ─────────────── -->
    <section id="panel-transaksi" class="db-panel">

      <div class="db-card">
        <div class="db-card-header">
          <span class="db-card-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/></svg>
            Transaksi Saya
          </span>
          <!-- Tombol tambah listing -->
          <button class="btn btn-primary" id="btnAddListing" style="font-size:13px;padding:8px 16px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Jual Akun
          </button>
        </div>

        <!-- Tabs -->
        <div class="db-tabs">
          <button class="db-tab active" data-tab="penjualan">
            Penjualan
            <span style="background:var(--blue-lt);color:var(--blue);border-radius:999px;padding:1px 7px;font-size:11px;margin-left:4px;"><?php echo $total_listings; ?></span>
          </button>
          <button class="db-tab" data-tab="pembelian">
            Pembelian
            <span style="background:var(--bg3);color:var(--muted);border-radius:999px;padding:1px 7px;font-size:11px;margin-left:4px;"><?php echo $purchase_count; ?></span>
          </button>
        </div>

        <!-- ── TAB: PENJUALAN ── -->
        <div id="tab-penjualan" class="db-tab-panel active">
          <?php if (empty($listings)): ?>
            <div class="db-empty">
              <div class="db-empty-icon">🎮</div>
              <strong>Belum ada listing</strong>
              <p>Klik tombol <strong>Jual Akun</strong> untuk mulai menjual akun game kamu.</p>
            </div>
          <?php else: ?>
            <div class="db-item-list">
              <?php foreach ($listings as $item): ?>
              <div class="db-item">
                <img src="<?php echo htmlspecialchars($item['game_image']); ?>"
                     alt="<?php echo htmlspecialchars($item['game_name']); ?>"
                     class="db-item-thumb"
                     onerror="this.src=''">
                <div class="db-item-info">
                  <div class="db-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                  <div class="db-item-meta">
                    <span><?php echo htmlspecialchars($item['game_name']); ?></span>
                    <?php if ($item['rank']): ?>
                      <span>· <?php echo htmlspecialchars($item['rank']); ?></span>
                    <?php endif; ?>
                    <?php if ($item['level']): ?>
                      <span>· Lv <?php echo $item['level']; ?></span>
                    <?php endif; ?>
                    <?php if ($item['account_login_type']): ?>
                      <span>· <?php echo htmlspecialchars($item['account_login_type']); ?></span>
                    <?php endif; ?>
                    <span style="color:var(--muted);">· <?php echo date('d M Y', strtotime($item['created_at'])); ?></span>
                  </div>
                </div>
                <?php echo listing_badge($item['status']); ?>
                <div class="db-item-price">
                  <?php echo rupiah((float)$item['price']); ?>
                  <small>
                    <?php if ($item['status'] === 'sold'): ?>
                      Sudah terjual
                    <?php else: ?>
                      Aktif dijual
                    <?php endif; ?>
                  </small>
                </div>
                <?php if ($item['status'] === 'ready'): ?>
                  <button class="btn-delete-listing" data-id="<?php echo $item['listing_id']; ?>"
                    title="Hapus listing"
                    style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;border-radius:var(--radius-sm);transition:color .15s;"
                    onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='var(--muted)'">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                  </button>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <!-- /PENJUALAN -->

        <!-- ── TAB: PEMBELIAN ── -->
        <div id="tab-pembelian" class="db-tab-panel">
          <?php if (empty($purchases)): ?>
            <div class="db-empty">
              <div class="db-empty-icon">🛒</div>
              <strong>Belum ada pembelian</strong>
              <p>Kamu belum pernah membeli akun. Jelajahi <a href="<?php echo $base_url; ?>index.php" style="color:var(--blue);font-weight:600;">marketplace</a> sekarang.</p>
            </div>
          <?php else: ?>
            <div class="db-item-list">
              <?php foreach ($purchases as $p): ?>
              <div class="db-item">
                <img src="<?php echo htmlspecialchars($p['game_image']); ?>"
                     alt="<?php echo htmlspecialchars($p['game_name']); ?>"
                     class="db-item-thumb"
                     onerror="this.src=''">
                <div class="db-item-info">
                  <div class="db-item-title"><?php echo htmlspecialchars($p['title']); ?></div>
                  <div class="db-item-meta">
                    <span><?php echo htmlspecialchars($p['game_name']); ?></span>
                    <?php if ($p['rank']): ?><span>· <?php echo htmlspecialchars($p['rank']); ?></span><?php endif; ?>
                    <?php if ($p['level']): ?><span>· Lv <?php echo $p['level']; ?></span><?php endif; ?>
                    <?php if ($p['payment_method']): ?><span>· <?php echo htmlspecialchars($p['payment_method']); ?></span><?php endif; ?>
                    <span>· <?php echo date('d M Y', strtotime($p['created_at'])); ?></span>
                  </div>
                </div>
                <?php echo order_badge($p['payment_status'] ?? $p['order_status']); ?>
                <div class="db-item-price">
                  <?php echo rupiah((float)$p['total_price']); ?>
                  <small>
                    <?php if ($p['paid_at']): ?>
                      Dibayar <?php echo date('d M Y', strtotime($p['paid_at'])); ?>
                    <?php else: ?>
                      Belum dibayar
                    <?php endif; ?>
                  </small>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <!-- /PEMBELIAN -->

      </div>
    </section>
    <!-- /TRANSAKSI -->


    <!-- ─────────────── PANEL: PROFIL ─────────────── -->
    <section id="panel-profil" class="db-panel">

      <div class="db-card">
        <div class="db-card-header">
          <span class="db-card-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profil Saya
          </span>
          <button class="btn btn-outline" id="btnEditProfile" style="font-size:13px;padding:7px 14px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Profil
          </button>
        </div>

        <form id="profileForm"
              action="<?php echo $base_url; ?>pages/users/update_profile.php"
              method="POST">
          <div class="db-form-grid">
            <div class="db-form-group">
              <label for="inp_username">Username</label>
              <input type="text" id="inp_username" name="username"
                     value="<?php echo htmlspecialchars($user['username']); ?>"
                     readonly>
            </div>
            <div class="db-form-group">
              <label for="inp_email">Email</label>
              <input type="email" id="inp_email" name="email"
                     value="<?php echo htmlspecialchars($user['email']); ?>"
                     readonly>
            </div>
            <div class="db-form-group full">
              <label for="inp_fullname">Nama Lengkap</label>
              <input type="text" id="inp_fullname" name="full_name"
                     value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                     placeholder="Masukkan nama lengkap">
            </div>
            <div class="db-form-group">
              <label for="inp_phone">Nomor Telepon</label>
              <input type="tel" id="inp_phone" name="phone_number"
                     value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>"
                     placeholder="08xxxxxxxxxx">
            </div>
            <div class="db-form-group">
              <label for="inp_role">Status</label>
              <input type="text" id="inp_role"
                     value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>"
                     readonly>
            </div>
            <div class="db-form-group full">
              <label for="inp_password">Password Baru <span style="color:var(--muted);font-weight:400;">(kosongkan jika tidak ingin mengubah)</span></label>
              <input type="password" id="inp_password" name="password"
                     placeholder="••••••••">
            </div>
          </div>

          <div class="db-form-actions">
            <button type="button" id="btnCancelEdit" class="btn btn-outline"
                    style="display:none;font-size:13px;padding:8px 16px;">
              Batal
            </button>
            <button type="submit" id="btnSaveProfile" class="btn btn-primary"
                    style="display:none;font-size:13px;padding:8px 20px;">
              Simpan Perubahan
            </button>
          </div>
        </form>

      </div>

      <!-- Info akun -->
      <div class="db-card">
        <div class="db-card-header">
          <span class="db-card-title">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Info Akun
          </span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13px;color:var(--muted);">
          <div>
            <div style="font-weight:600;color:var(--text);margin-bottom:2px;">Status Akun</div>
            <span class="badge <?php echo $user['status'] === 'active' ? 'badge-ready' : 'badge-cancelled'; ?>">
              <?php echo ucfirst($user['status']); ?>
            </span>
          </div>
          <div>
            <div style="font-weight:600;color:var(--text);margin-bottom:2px;">Terdaftar</div>
            <?php echo date('d M Y, H:i', strtotime($user['created_at'])); ?> WIB
          </div>
          <div>
            <div style="font-weight:600;color:var(--text);margin-bottom:2px;">Total Listing</div>
            <?php echo $total_listings; ?> akun
          </div>
          <div>
            <div style="font-weight:600;color:var(--text);margin-bottom:2px;">Total Pembelian</div>
            <?php echo $purchase_count; ?> transaksi
          </div>
        </div>
      </div>

    </section>
    <!-- /PROFIL -->

  </main>
  <!-- /MAIN -->

</div>
<!-- /dashboard-wrapper -->


<!-- ══════════════════════════════════════════════
     MODAL: TAMBAH LISTING
══════════════════════════════════════════════ -->
<div class="db-modal-backdrop" id="addListingModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
  <div class="db-modal">
    <div class="db-modal-header">
      <span class="db-modal-title" id="modalTitle">Jual Akun Game</span>
      <button class="db-modal-close" id="btnCloseModal" aria-label="Tutup">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="db-modal-body">
      <form id="formAddListing"
            action="<?php echo $base_url; ?>pages/users/add_listing.php"
            method="POST">

        <div class="db-form-grid">

          <div class="db-form-group full">
            <label for="lst_title">Judul Listing <span style="color:#dc2626;">*</span></label>
            <input type="text" id="lst_title" name="title" required
                   placeholder="cth: Valorant Immortal 3 – All Agents Unlocked">
          </div>

          <div class="db-form-group">
            <label for="lst_game">Game <span style="color:#dc2626;">*</span></label>
            <select id="lst_game" name="game_id" required>
              <option value="">-- Pilih Game --</option>
              <?php foreach ($games_list as $g): ?>
                <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="db-form-group">
            <label for="lst_price">Harga (Rp) <span style="color:#dc2626;">*</span></label>
            <input type="number" id="lst_price" name="price" required
                   min="1000" step="1000" placeholder="350000">
          </div>

          <div class="db-form-group">
            <label for="lst_rank">Rank</label>
            <input type="text" id="lst_rank" name="rank" placeholder="cth: Immortal 3">
          </div>

          <div class="db-form-group">
            <label for="lst_level">Level</label>
            <input type="number" id="lst_level" name="level" min="1" placeholder="150">
          </div>

          <div class="db-form-group full">
            <label for="lst_login_type">Tipe Login Akun</label>
            <select id="lst_login_type" name="account_login_type">
              <option value="">-- Pilih Tipe --</option>
              <option value="Email">Email</option>
              <option value="Google">Google</option>
              <option value="Facebook">Facebook</option>
              <option value="VNG">VNG</option>
              <option value="Moonton">Moonton</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>

          <div class="db-form-group full">
            <label for="lst_id">ID Akun (opsional)</label>
            <input type="text" id="lst_id" name="id" placeholder="ID dalam game">
          </div>

          <div class="db-form-group full">
            <label for="lst_desc">Deskripsi</label>
            <textarea id="lst_desc" name="description" rows="4"
                      style="padding:10px 14px;border:1px solid var(--border);border-radius:var(--radius);font-family:'Outfit',sans-serif;font-size:14px;color:var(--text);resize:vertical;outline:none;width:100%;transition:border-color .2s,box-shadow .2s;"
                      placeholder="Ceritakan keunggulan akun ini: hero/karakter yang dimiliki, skins, prestasi, dsb."
                      onfocus="this.style.borderColor='var(--blue)';this.style.boxShadow='0 0 0 3px rgba(26,86,255,.1)'"
                      onblur="this.style.borderColor='';this.style.boxShadow=''"></textarea>
          </div>

        </div>

        <div class="db-form-actions" style="margin-top:var(--space-2);padding-top:var(--space-2);border-top:1px solid var(--border);">
          <button type="button" id="btnCloseModal2"
                  class="btn btn-outline" style="font-size:13px;padding:8px 16px;"
                  onclick="document.getElementById('addListingModal').classList.remove('open');document.body.style.overflow=''">
            Batal
          </button>
          <button type="submit" class="btn btn-primary" style="font-size:13px;padding:8px 20px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Listing
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
<!-- /MODAL -->


<!-- ── Dashboard JS ──────────────────────────────────────────────────────── -->
<script src="<?php echo $base_url; ?>assets/page/js/userdashboard.js"></script>

<?php include '../../includes/footer.php'; ?>