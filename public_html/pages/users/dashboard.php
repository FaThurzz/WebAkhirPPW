<?php
/** @var mysqli $conn */
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['user']['role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
}

require_once '../../includes/db.php';

$user_id = (int) $_SESSION['user']['ID_User'];

// ── Ambil data user terbaru ──────────────────────────────────────────────
$uStmt = mysqli_prepare($conn, "SELECT * FROM users WHERE ID_User = ?");
mysqli_stmt_bind_param($uStmt, 'i', $user_id);
mysqli_stmt_execute($uStmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($uStmt));
mysqli_stmt_close($uStmt);
$_SESSION['user'] = $user;

// ── Stats ────────────────────────────────────────────────────────────────
$statStmt = mysqli_prepare($conn,
    "SELECT COUNT(*) AS total_listing,
            SUM(status='ready') AS listing_ready,
            SUM(status='sold')  AS listing_sold
     FROM account_listing WHERE user_id = ?");
mysqli_stmt_bind_param($statStmt, 'i', $user_id);
mysqli_stmt_execute($statStmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($statStmt));
mysqli_stmt_close($statStmt);

$salesStmt = mysqli_prepare($conn,
    "SELECT COUNT(*) AS total_sales, COALESCE(SUM(total_price),0) AS total_income
     FROM orders WHERE seller_id = ? AND order_status = 'confirmed'");
mysqli_stmt_bind_param($salesStmt, 'i', $user_id);
mysqli_stmt_execute($salesStmt);
$salesStats = mysqli_fetch_assoc(mysqli_stmt_get_result($salesStmt));
mysqli_stmt_close($salesStmt);

$buyCountStmt = mysqli_prepare($conn,
    "SELECT COUNT(*) AS total_purchases FROM orders WHERE user_id = ? AND order_status != 'cancelled'");
mysqli_stmt_bind_param($buyCountStmt, 'i', $user_id);
mysqli_stmt_execute($buyCountStmt);
$buyStats = mysqli_fetch_assoc(mysqli_stmt_get_result($buyCountStmt));
mysqli_stmt_close($buyCountStmt);

// Total belanja (memakai fungsi DB fn_user_total_belanja)
$belanjaStmt = mysqli_prepare($conn, "SELECT fn_user_total_belanja(?) AS total_belanja");
mysqli_stmt_bind_param($belanjaStmt, 'i', $user_id);
mysqli_stmt_execute($belanjaStmt);
$belanjaRow = mysqli_fetch_assoc(mysqli_stmt_get_result($belanjaStmt));
$buyStats['total_belanja'] = $belanjaRow['total_belanja'] ?? 0;
mysqli_stmt_close($belanjaStmt);

// ── Listing milik user ───────────────────────────────────────────────────
$lStmt = mysqli_prepare($conn,
    "SELECT al.*, g.name AS game_name,
            ac.account_email, ac.account_password, ac.notes AS cred_notes
     FROM account_listing al
     JOIN games g ON al.game_id = g.id
     LEFT JOIN account_credentials ac ON ac.listing_id = al.listing_id
     WHERE al.user_id = ? ORDER BY al.created_at DESC");
mysqli_stmt_bind_param($lStmt, 'i', $user_id);
mysqli_stmt_execute($lStmt);
$myListings = mysqli_fetch_all(mysqli_stmt_get_result($lStmt), MYSQLI_ASSOC);
mysqli_stmt_close($lStmt);

// ── Order penjualan ──────────────────────────────────────────────────────
$sellOrdStmt = mysqli_prepare($conn,
    "SELECT o.*, al.title, al.image_url, al.price,
            g.name AS game_name, u.username AS buyer_name,
            p.payment_method, p.payment_status, p.payment_proof, p.paid_at
     FROM orders o
     JOIN account_listing al ON o.listing_id = al.listing_id
     JOIN games g ON al.game_id = g.id
     JOIN users u ON o.user_id = u.ID_User
     LEFT JOIN payment p ON o.order_id = p.order_id
     WHERE o.seller_id = ? ORDER BY o.created_at DESC");
mysqli_stmt_bind_param($sellOrdStmt, 'i', $user_id);
mysqli_stmt_execute($sellOrdStmt);
$sellOrders = mysqli_fetch_all(mysqli_stmt_get_result($sellOrdStmt), MYSQLI_ASSOC);
mysqli_stmt_close($sellOrdStmt);

// ── Order pembelian (memakai VIEW v_order_detail) ───────────────────────
$buyOrdStmt = mysqli_prepare($conn,
    "SELECT v.order_id, v.listing_id, v.total_price, al.price, al.image_url,
            v.order_status, v.order_created_at AS created_at,
            v.listing_title AS title, v.game_name,
            v.seller_username AS seller_name,
            v.payment_method, v.payment_status, v.payment_proof, v.paid_at,
            ac.account_email, ac.account_password, ac.notes AS cred_notes
     FROM v_order_detail v
     LEFT JOIN account_listing al ON al.listing_id = v.listing_id
     LEFT JOIN account_credentials ac ON ac.listing_id = v.listing_id
     WHERE v.buyer_id = ? ORDER BY v.order_created_at DESC");
mysqli_stmt_bind_param($buyOrdStmt, 'i', $user_id);
mysqli_stmt_execute($buyOrdStmt);
$buyOrders = mysqli_fetch_all(mysqli_stmt_get_result($buyOrdStmt), MYSQLI_ASSOC);
mysqli_stmt_close($buyOrdStmt);

// ── Games untuk dropdown ─────────────────────────────────────────────────
$games = mysqli_fetch_all(mysqli_query($conn, "SELECT id, name FROM games ORDER BY name ASC"), MYSQLI_ASSOC);

// ── Helpers ──────────────────────────────────────────────────────────────
function formatRp($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function statusLabel($s) {
    return ['pending'=>'Menunggu','paid'=>'Dibayar','confirmed'=>'Dikonfirmasi','cancelled'=>'Dibatalkan'][$s] ?? ucfirst($s);
}
function statusBadge($s) { return "badge-{$s}"; }
function timeAgo($dt) {
    $d = time() - strtotime($dt);
    if ($d < 60) return 'Baru saja';
    if ($d < 3600) return floor($d/60).' menit lalu';
    if ($d < 86400) return floor($d/3600).' jam lalu';
    if ($d < 2592000) return floor($d/86400).' hari lalu';
    return date('d M Y', strtotime($dt));
}

$page_title  = 'Dashboard — ThurzShop';
$active_page = 'profile';
$userInitial = strtoupper(mb_substr($user['username'], 0, 1));

include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/page/css/userdashboard.css">
<style>
.db-modal-footer{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:var(--space-2) var(--space-3);border-top:1px solid var(--border)}
.lst-image-dropzone{border:2px dashed var(--border);border-radius:var(--radius-md);padding:28px 20px;text-align:center;cursor:pointer;background:var(--bg);transition:border-color .2s,background .2s;position:relative}
.lst-image-dropzone:hover{border-color:var(--blue);background:var(--blue-lt)}
.lst-drop-icon{font-size:30px;margin-bottom:8px}
.lst-drop-text{font-size:13px;color:var(--muted);line-height:1.6}
.lst-drop-text strong{color:var(--blue)}
#lstImagePreview{width:100%;max-height:180px;object-fit:cover;border-radius:var(--radius);display:none}
#lstImageError{color:#dc2626;font-size:12px;margin-top:4px;display:none}
</style>
</head><body>

<div class="dashboard-wrapper">

<!-- ══ SIDEBAR ════════════════════════════════════════════════════════════ -->
<aside class="db-sidebar">
  <div class="db-profile-card">
    <div class="db-avatar"><?php echo $userInitial; ?></div>
    <div class="db-profile-name"><?php echo htmlspecialchars($user['username']); ?></div>
    <div class="db-profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
    <span class="db-profile-badge">
      <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Member Aktif
    </span>
  </div>
  <nav class="db-nav">
    <button class="db-nav-item" data-panel="overview">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Overview
    </button>
    <button class="db-nav-item" data-panel="transaksi">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      Transaksi
    </button>
    <button class="db-nav-item" data-panel="listing">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
      Listing Saya
    </button>
    <button class="db-nav-item" data-panel="profil">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Profil
    </button>
    <hr class="db-nav-divider">
    <a href="../logout.php" class="db-nav-item db-nav-item--danger" style="text-decoration:none">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Keluar
    </a>
  </nav>
</aside>

<!-- ══ MAIN ════════════════════════════════════════════════════════════════ -->
<main class="db-main">

<!-- ── PANEL: OVERVIEW ──────────────────────────────────────────────────── -->
<div class="db-panel" id="panel-overview">

  <div class="db-stats">
    <div class="db-stat"><div class="db-stat-value blue"><?php echo (int)$stats['total_listing']; ?></div><div class="db-stat-label">Total Listing</div></div>
    <div class="db-stat"><div class="db-stat-value green"><?php echo (int)$salesStats['total_sales']; ?></div><div class="db-stat-label">Akun Terjual</div></div>
    <div class="db-stat"><div class="db-stat-value orange"><?php echo (int)$buyStats['total_purchases']; ?></div><div class="db-stat-label">Pembelian</div></div>
    <div class="db-stat"><div class="db-stat-value blue"><?php echo formatRp((float)$buyStats['total_belanja']); ?></div><div class="db-stat-label">Total Belanja</div></div>
  </div>

  <div class="db-card">
    <div class="db-card-header">
      <div class="db-card-title"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>Listing Terbaru</div>
      <button class="db-nav-item" data-panel="listing" style="width:auto;padding:6px 14px;font-size:12px">Lihat Semua</button>
    </div>
    <?php if (empty($myListings)): ?>
      <div class="db-empty"><div class="db-empty-icon">📦</div><strong>Belum ada listing</strong><p>Mulai jual akun game kamu!</p></div>
    <?php else: ?>
      <div class="db-item-list">
        <?php foreach (array_slice($myListings, 0, 3) as $lst):
          $imgSrc = !empty($lst['image_url']) ? '../../'.htmlspecialchars($lst['image_url']) : null; ?>
        <div class="db-item">
          <?php if ($imgSrc): ?><img src="<?php echo $imgSrc; ?>" class="db-item-thumb" onerror="this.style.display='none'">
          <?php else: ?><div class="db-item-thumb" style="display:flex;align-items:center;justify-content:center;font-size:20px">🎮</div><?php endif; ?>
          <div class="db-item-info">
            <div class="db-item-title"><?php echo htmlspecialchars($lst['title']); ?></div>
            <div class="db-item-meta"><span><?php echo htmlspecialchars($lst['game_name']); ?></span><span>·</span><span><?php echo timeAgo($lst['created_at']); ?></span></div>
          </div>
          <span class="badge badge-<?php echo $lst['status']; ?>"><?php echo $lst['status']==='ready'?'Tersedia':'Terjual'; ?></span>
          <div class="db-item-price"><?php echo formatRp($lst['price']); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="db-card">
    <div class="db-card-header">
      <div class="db-card-title"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>Pembelian Terbaru</div>
      <button class="db-nav-item" data-panel="transaksi" style="width:auto;padding:6px 14px;font-size:12px">Lihat Semua</button>
    </div>
    <?php if (empty($buyOrders)): ?>
      <div class="db-empty"><div class="db-empty-icon">🛒</div><strong>Belum ada pembelian</strong><p>Temukan akun game impianmu di marketplace!</p></div>
    <?php else: ?>
      <div class="db-item-list">
        <?php foreach (array_slice($buyOrders, 0, 3) as $ord):
          $imgSrc = !empty($ord['image_url']) ? '../../'.htmlspecialchars($ord['image_url']) : null; ?>
        <div class="db-item">
          <?php if ($imgSrc): ?><img src="<?php echo $imgSrc; ?>" class="db-item-thumb" onerror="this.style.display='none'">
          <?php else: ?><div class="db-item-thumb" style="display:flex;align-items:center;justify-content:center;font-size:20px">🎮</div><?php endif; ?>
          <div class="db-item-info">
            <div class="db-item-title"><?php echo htmlspecialchars($ord['title']); ?></div>
            <div class="db-item-meta"><span><?php echo htmlspecialchars($ord['game_name']); ?></span><span>·</span><span>#<?php echo $ord['order_id']; ?></span></div>
          </div>
          <span class="badge <?php echo statusBadge($ord['order_status']); ?>"><?php echo statusLabel($ord['order_status']); ?></span>
          <div class="db-item-price"><?php echo formatRp($ord['total_price']); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div><!-- /panel-overview -->


<!-- ── PANEL: TRANSAKSI ──────────────────────────────────────────────────── -->
<div class="db-panel" id="panel-transaksi">
  <div class="db-card">
    <div class="db-card-header">
      <div class="db-card-title"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>Riwayat Transaksi</div>
    </div>
    <div class="db-tabs" style="margin-bottom:var(--space-2)">
      <button class="db-tab active" data-tab="penjualan">Penjualan</button>
      <button class="db-tab" data-tab="pembelian">Pembelian</button>
    </div>

    <!-- Tab Penjualan -->
    <div class="db-tab-panel active" id="tab-penjualan">
      <?php if (empty($sellOrders)): ?>
        <div class="db-empty"><div class="db-empty-icon">💸</div><strong>Belum ada penjualan</strong><p>Listing yang dibeli pembeli muncul di sini.</p></div>
      <?php else: ?>
        <div class="db-item-list">
          <?php foreach ($sellOrders as $ord):
            $imgSrc = !empty($ord['image_url']) ? '../../'.htmlspecialchars($ord['image_url']) : null; ?>
          <div class="db-item">
            <?php if ($imgSrc): ?><img src="<?php echo $imgSrc; ?>" class="db-item-thumb" onerror="this.style.display='none'">
            <?php else: ?><div class="db-item-thumb" style="display:flex;align-items:center;justify-content:center;font-size:20px">🎮</div><?php endif; ?>
            <div class="db-item-info">
              <div class="db-item-title"><?php echo htmlspecialchars($ord['title']); ?></div>
              <div class="db-item-meta"><span><?php echo htmlspecialchars($ord['game_name']); ?></span><span>·</span><span>Pembeli: <?php echo htmlspecialchars($ord['buyer_name']); ?></span><span>·</span><span>#<?php echo $ord['order_id']; ?></span></div>
            </div>
            <span class="badge <?php echo statusBadge($ord['order_status']); ?>"><?php echo statusLabel($ord['order_status']); ?></span>
            <div class="db-item-price"><?php echo formatRp($ord['total_price']); ?><small><?php echo timeAgo($ord['created_at']); ?></small></div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Tab Pembelian -->
    <div class="db-tab-panel" id="tab-pembelian">
      <?php if (empty($buyOrders)): ?>
        <div class="db-empty"><div class="db-empty-icon">🛒</div><strong>Belum ada pembelian</strong><p>Akun game yang kamu beli muncul di sini.</p></div>
      <?php else: ?>
        <div class="db-item-list">
          <?php foreach ($buyOrders as $ord):
            $imgSrc   = !empty($ord['image_url']) ? '../../'.htmlspecialchars($ord['image_url']) : '';
            $proofUrl = !empty($ord['payment_proof']) ? '../../'.htmlspecialchars($ord['payment_proof']) : '';
            $statusLbl = statusLabel($ord['order_status']);
            $paidAt    = !empty($ord['paid_at']) ? date('d M Y, H:i', strtotime($ord['paid_at'])) : '-';
            $createdAt = date('d M Y, H:i', strtotime($ord['created_at'])); ?>
          <div class="db-item">
            <?php if ($imgSrc): ?><img src="<?php echo $imgSrc; ?>" class="db-item-thumb" onerror="this.style.display='none'">
            <?php else: ?><div class="db-item-thumb" style="display:flex;align-items:center;justify-content:center;font-size:20px">🎮</div><?php endif; ?>
            <div class="db-item-info">
              <div class="db-item-title"><?php echo htmlspecialchars($ord['title']); ?></div>
              <div class="db-item-meta"><span><?php echo htmlspecialchars($ord['game_name']); ?></span><span>·</span><span>Penjual: <?php echo htmlspecialchars($ord['seller_name']); ?></span></div>
            </div>
            <span class="badge <?php echo statusBadge($ord['order_status']); ?>"><?php echo $statusLbl; ?></span>
            <div class="db-item-price"><?php echo formatRp($ord['total_price']); ?><small><?php echo timeAgo($ord['created_at']); ?></small></div>
            <button class="db-purchase-detail-btn"
              data-order-id="<?php echo $ord['order_id']; ?>"
              data-title="<?php echo htmlspecialchars($ord['title'],ENT_QUOTES); ?>"
              data-game="<?php echo htmlspecialchars($ord['game_name'],ENT_QUOTES); ?>"
              data-seller="<?php echo htmlspecialchars($ord['seller_name'],ENT_QUOTES); ?>"
              data-status="<?php echo $ord['order_status']; ?>"
              data-status-label="<?php echo $statusLbl; ?>"
              data-method="<?php echo htmlspecialchars($ord['payment_method']??'-',ENT_QUOTES); ?>"
              data-created="<?php echo $createdAt; ?>"
              data-paid="<?php echo $paidAt; ?>"
              data-total="<?php echo formatRp($ord['total_price']); ?>"
              data-image="<?php echo $imgSrc; ?>"
              data-proof-url="<?php echo $proofUrl; ?>"
              data-account-email="<?php echo ($ord['order_status']==='confirmed') ? htmlspecialchars($ord['account_email']??'',ENT_QUOTES) : ''; ?>"
              data-account-password="<?php echo ($ord['order_status']==='confirmed') ? htmlspecialchars($ord['account_password']??'',ENT_QUOTES) : ''; ?>"
              data-cred-notes="<?php echo ($ord['order_status']==='confirmed') ? htmlspecialchars($ord['cred_notes']??'',ENT_QUOTES) : ''; ?>">Detail</button>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div><!-- /panel-transaksi -->


<!-- ── PANEL: LISTING ────────────────────────────────────────────────────── -->
<div class="db-panel" id="panel-listing">
  <div class="db-card">
    <div class="db-card-header">
      <div class="db-card-title"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>Listing Saya</div>
      <button class="btn btn-primary" id="btnAddListing" style="font-size:13px;padding:8px 16px">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Jual Akun
      </button>
    </div>
    <?php if (empty($myListings)): ?>
      <div class="db-empty"><div class="db-empty-icon">📦</div><strong>Belum ada listing</strong><p>Klik "Jual Akun" untuk mulai menjual.</p></div>
    <?php else: ?>
      <div class="db-item-list">
        <?php foreach ($myListings as $lst):
          $imgSrc = !empty($lst['image_url']) ? '../../'.htmlspecialchars($lst['image_url']) : ''; ?>
        <div class="db-item">
          <?php if ($imgSrc): ?><img src="<?php echo $imgSrc; ?>" class="db-item-thumb" onerror="this.style.display='none'">
          <?php else: ?><div class="db-item-thumb" style="display:flex;align-items:center;justify-content:center;font-size:20px">🎮</div><?php endif; ?>
          <div class="db-item-info">
            <div class="db-item-title"><?php echo htmlspecialchars($lst['title']); ?></div>
            <div class="db-item-meta">
              <span><?php echo htmlspecialchars($lst['game_name']); ?></span>
              <?php if (!empty($lst['rank'])): ?><span>·</span><span><?php echo htmlspecialchars($lst['rank']); ?></span><?php endif; ?>
              <?php if (!empty($lst['level'])): ?><span>·</span><span>Lv.<?php echo $lst['level']; ?></span><?php endif; ?>
              <span>·</span><span><?php echo timeAgo($lst['created_at']); ?></span>
            </div>
          </div>
          <span class="badge badge-<?php echo $lst['status']; ?>"><?php echo $lst['status']==='ready'?'Tersedia':'Terjual'; ?></span>
          <div class="db-item-price"><?php echo formatRp($lst['price']); ?></div>
          <?php if ($lst['status']==='ready'): ?>
          <button class="btn-edit-listing" title="Edit"
            style="border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--white);padding:7px 10px;cursor:pointer;color:var(--muted);transition:all .15s"
            data-id="<?php echo $lst['listing_id']; ?>"
            data-title="<?php echo htmlspecialchars($lst['title'],ENT_QUOTES); ?>"
            data-game-id="<?php echo $lst['game_id']; ?>"
            data-price="<?php echo $lst['price']; ?>"
            data-rank="<?php echo htmlspecialchars($lst['rank']??'',ENT_QUOTES); ?>"
            data-level="<?php echo $lst['level']; ?>"
            data-server="<?php echo htmlspecialchars($lst['server']??'',ENT_QUOTES); ?>"
            data-login-type="<?php echo htmlspecialchars($lst['account_login_type']??'',ENT_QUOTES); ?>"
            data-account-id="<?php echo htmlspecialchars($lst['id']??'',ENT_QUOTES); ?>"
            data-description="<?php echo htmlspecialchars($lst['description']??'',ENT_QUOTES); ?>"
            data-image-url="<?php echo $imgSrc; ?>"
            data-account-email="<?php echo htmlspecialchars($lst['account_email']??'',ENT_QUOTES); ?>"
            data-account-password="<?php echo htmlspecialchars($lst['account_password']??'',ENT_QUOTES); ?>"
            data-cred-notes="<?php echo htmlspecialchars($lst['cred_notes']??'',ENT_QUOTES); ?>">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          </button>
          <button class="btn-delete-listing" title="Hapus"
            style="border:1px solid #fee2e2;border-radius:var(--radius-sm);background:#fef2f2;padding:7px 10px;cursor:pointer;color:#dc2626;transition:all .15s"
            data-id="<?php echo $lst['listing_id']; ?>">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
          </button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div><!-- /panel-listing -->


<!-- ── PANEL: PROFIL ────────────────────────────────────────────────────── -->
<div class="db-panel" id="panel-profil">
  <div class="db-card">
    <div class="db-card-header">
      <div class="db-card-title"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Profil Saya</div>
      <button class="btn" id="btnEditProfile" style="font-size:13px;padding:8px 16px;border:1px solid var(--border);border-radius:var(--radius);background:var(--white);cursor:pointer;font-family:Outfit,sans-serif">Edit Profil</button>
      <button class="btn" id="btnCancelEdit" style="font-size:13px;padding:8px 16px;border:1px solid var(--border);border-radius:var(--radius);background:var(--white);cursor:pointer;display:none;font-family:Outfit,sans-serif">Batal</button>
    </div>
    <form id="profileForm" action="update_profile.php" method="POST" autocomplete="off">
      <div class="db-form-grid">
        <div class="db-form-group">
          <label>Username</label>
          <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
        </div>
        <div class="db-form-group">
          <label>Email</label>
          <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
        </div>
        <div class="db-form-group">
          <label for="full_name">Nama Lengkap</label>
          <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']??''); ?>" placeholder="Nama lengkap kamu">
        </div>
        <div class="db-form-group">
          <label for="phone_number">Nomor HP</label>
          <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']??''); ?>" placeholder="08xxxxxxxx">
        </div>
        <div class="db-form-group full">
          <label for="new_password">Password Baru <span style="color:var(--muted);font-weight:400">(kosongkan jika tidak ingin ganti)</span></label>
          <input type="password" id="new_password" name="new_password" placeholder="Password baru (min. 6 karakter)">
        </div>
      </div>
      <div class="db-form-actions">
        <button type="submit" id="btnSaveProfile" class="btn btn-primary" style="font-size:13px;display:none">Simpan Perubahan</button>
      </div>
    </form>
  </div>
  <div class="db-card">
    <div class="db-card-header"><div class="db-card-title">Informasi Akun</div></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;padding-top:4px">
      <div><div style="font-size:12px;color:var(--muted);margin-bottom:4px">Bergabung Sejak</div><div style="font-weight:600"><?php echo date('d M Y', strtotime($user['created_at'])); ?></div></div>
      <div><div style="font-size:12px;color:var(--muted);margin-bottom:4px">Status Akun</div><span class="badge badge-ready">Aktif</span></div>
      <div><div style="font-size:12px;color:var(--muted);margin-bottom:4px">Total Listing</div><div style="font-weight:600"><?php echo $stats['total_listing']; ?></div></div>
      <div><div style="font-size:12px;color:var(--muted);margin-bottom:4px">Akun Terjual</div><div style="font-weight:600"><?php echo $stats['listing_sold']; ?></div></div>
    </div>
  </div>
</div><!-- /panel-profil -->

</main>
</div>


<!-- ══ MODAL: TAMBAH / EDIT LISTING ════════════════════════════════════════ -->
<div class="db-modal-backdrop" id="addListingModal">
  <div class="db-modal">
    <div class="db-modal-header">
      <div class="db-modal-title" id="modalTitle">Jual Akun Game</div>
      <button class="db-modal-close" id="btnCloseModal">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="db-modal-body">
      <form id="formAddListing" action="add_listing.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="lst_listing_id" name="listing_id">
        <div class="db-form-grid">
          <div class="db-form-group full">
            <label for="lst_title">Judul Listing <span style="color:#dc2626">*</span></label>
            <input type="text" id="lst_title" name="title" placeholder="Contoh: Akun Valorant Immortal 3" maxlength="150" required>
          </div>
          <div class="db-form-group">
            <label for="lst_game">Game <span style="color:#dc2626">*</span></label>
            <select id="lst_game" name="game_id" required>
              <option value="">-- Pilih Game --</option>
              <?php foreach ($games as $g): ?>
                <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="db-form-group">
            <label for="lst_price">Harga (Rp) <span style="color:#dc2626">*</span></label>
            <input type="number" id="lst_price" name="price" placeholder="50000" min="1000" required>
          </div>
          <div class="db-form-group">
            <label for="lst_rank">Rank</label>
            <input type="text" id="lst_rank" name="rank" placeholder="Contoh: Immortal, Diamond">
          </div>
          <div class="db-form-group">
            <label for="lst_level">Level</label>
            <input type="number" id="lst_level" name="level" placeholder="Contoh: 60" min="1">
          </div>
          <div class="db-form-group">
            <label for="lst_server">Server</label>
            <input type="text" id="lst_server" name="server" placeholder="Contoh: Asia, NA">
          </div>
          <div class="db-form-group">
            <label for="lst_login_type">Login Via</label>
            <select id="lst_login_type" name="account_login_type">
              <option value="">-- Pilih --</option>
              <option value="Email">Email</option>
              <option value="Google">Google</option>
              <option value="Facebook">Facebook</option>
              <option value="Apple ID">Apple ID</option>
              <option value="Phone">Phone</option>
            </select>
          </div>
          <div class="db-form-group">
            <label for="lst_id">ID Akun</label>
            <input type="text" id="lst_id" name="id" placeholder="ID in-game kamu">
          </div>
          <div class="db-form-group full">
            <label for="lst_desc">Deskripsi</label>
            <textarea id="lst_desc" name="description" rows="3" placeholder="Ceritakan detail akun: skin, hero, item, dll." style="padding:10px 14px;border:1px solid var(--border);border-radius:var(--radius);font-family:Outfit,sans-serif;font-size:14px;resize:vertical;outline:none;width:100%;box-sizing:border-box;transition:border-color .2s"></textarea>
          </div>
          <!-- ── Kredential Akun ──────────────────────────────────────────── -->
          <div class="db-form-group full" style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px">
            <label style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px">🔐 Kredential Akun <span style="color:#dc2626">*</span></label>
            <p style="font-size:12px;color:var(--muted);margin:4px 0 12px">Informasi ini hanya ditampilkan ke pembeli setelah admin mengkonfirmasi pembayaran.</p>
          </div>
          <div class="db-form-group">
            <label for="lst_account_email">Email Akun <span style="color:#dc2626">*</span></label>
            <input type="email" id="lst_account_email" name="account_email" placeholder="email@contoh.com" required>
          </div>
          <div class="db-form-group">
            <label for="lst_account_password">Password Akun <span style="color:#dc2626">*</span></label>
            <input type="text" id="lst_account_password" name="account_password" placeholder="Password akun game" required>
          </div>
          <div class="db-form-group full">
            <label for="lst_cred_notes">Catatan Tambahan (opsional)</label>
            <input type="text" id="lst_cred_notes" name="cred_notes" placeholder="Contoh: PIN: 1234, kode recovery: ABCD">
          </div>
          <div class="db-form-group full">
            <label id="lstImageRequired">Foto Akun <span style="color:#dc2626">*</span></label>
            <div class="lst-image-dropzone" id="lstImageDropzone"
                 onclick="document.getElementById('lst_image').click()"
                 ondragover="event.preventDefault();this.style.borderColor='var(--blue)'"
                 ondragleave="this.style.borderColor=''"
                 ondrop="window.handleImageDrop(event)">
              <img id="lstImagePreview" alt="Preview">
              <div id="lstDropIcon" class="lst-drop-icon">🖼️</div>
              <div id="lstDropText" class="lst-drop-text"><strong>Klik atau drag foto akun</strong><br>JPG, PNG, WEBP · Maks. 2MB</div>
            </div>
            <input type="file" id="lst_image" name="image" accept="image/jpeg,image/png,image/webp" style="display:none">
            <div id="lstImageError"></div>
          </div>
        </div>
        <div class="db-form-actions">
          <button type="button" id="btnCloseModal2" class="btn" style="padding:9px 18px;border:1px solid var(--border);border-radius:var(--radius);background:var(--white);cursor:pointer;font-family:Outfit,sans-serif;font-size:14px">Batal</button>
          <button type="submit" class="btn btn-primary" style="font-size:14px;padding:9px 20px"><span id="listingSubmitText">Tambah Listing</span></button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- ══ MODAL: DETAIL PEMBELIAN ══════════════════════════════════════════════ -->
<div class="db-modal-backdrop" id="purchaseDetailModal">
  <div class="db-modal db-modal--purchase">
    <div class="db-modal-header">
      <div class="db-modal-title">Detail Pembelian</div>
      <button class="db-modal-close" id="btnClosePurchaseModal">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="db-modal-body">
      <div class="db-purchase-hero">
        <img id="purchaseModalImage" src="" alt="" class="db-purchase-hero-img" onerror="this.style.display='none'">
        <div class="db-purchase-hero-copy">
          <div class="db-purchase-game" id="purchaseModalGame"></div>
          <div class="db-purchase-title" id="purchaseModalItem"></div>
        </div>
      </div>
      <div class="db-detail-grid">
        <div class="db-detail-row"><span>Order ID</span><strong id="purchaseModalOrderId"></strong></div>
        <div class="db-detail-row"><span>Penjual</span><strong id="purchaseModalSeller"></strong></div>
        <div class="db-detail-row"><span>Status</span><strong id="purchaseModalStatus"></strong></div>
        <div class="db-detail-row"><span>Metode</span><strong id="purchaseModalMethod"></strong></div>
        <div class="db-detail-row"><span>Tanggal Order</span><strong id="purchaseModalCreated"></strong></div>
        <div class="db-detail-row"><span>Tanggal Bayar</span><strong id="purchaseModalPaid"></strong></div>
        <div class="db-detail-row db-detail-row--total"><span>Total Pembayaran</span><strong id="purchaseModalTotal"></strong></div>
      </div>
      <!-- ── Kredential Akun (hanya muncul jika confirmed) ────────────────── -->
      <div id="purchaseCredentialBox" style="display:none;margin-top:14px;border:1.5px solid #16a34a;border-radius:var(--radius);padding:14px 16px;background:#f0fdf4;">
        <div style="font-weight:700;color:#15803d;margin-bottom:10px;font-size:13px">🔐 Kredential Akun</div>
        <div style="display:grid;gap:8px;font-size:13px">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:12px">
            <span style="color:#64748b">Email</span>
            <strong id="purchaseCredEmail" style="color:#1e293b;word-break:break-all"></strong>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;gap:12px">
            <span style="color:#64748b">Password</span>
            <span style="display:flex;align-items:center;gap:6px">
              <strong id="purchaseCredPassword" style="color:#1e293b;letter-spacing:.5px"></strong>
              <button id="btnTogglePassword" onclick="(function(){var el=document.getElementById('purchaseCredPasswordRaw');var hidden=document.getElementById('purchaseCredPassword');var isHidden=hidden.dataset.hidden==='1';hidden.textContent=isHidden?el.value:'••••••••';hidden.dataset.hidden=isHidden?'0':'1';document.getElementById('btnTogglePassword').textContent=isHidden?'👁️':'🙈';})()" style="background:none;border:none;cursor:pointer;font-size:14px;padding:0" title="Toggle tampilkan password">🙈</button>
              <input type="hidden" id="purchaseCredPasswordRaw">
            </span>
          </div>
          <div id="purchaseCredNotesRow" style="display:none;border-top:1px solid #bbf7d0;padding-top:8px">
            <span style="color:#64748b">Catatan</span><br>
            <strong id="purchaseCredNotes" style="color:#1e293b"></strong>
          </div>
        </div>
        <button onclick="(function(){var text='Email: '+document.getElementById('purchaseCredEmail').textContent+'\nPassword: '+document.getElementById('purchaseCredPasswordRaw').value+(document.getElementById('purchaseCredNotesRow').style.display!=='none'?'\nCatatan: '+document.getElementById('purchaseCredNotes').textContent:'');navigator.clipboard.writeText(text).then(function(){window.showToast&&window.showToast('Kredential disalin!','success');});})()" style="margin-top:12px;width:100%;padding:8px;border:1px solid #16a34a;border-radius:var(--radius);background:white;color:#15803d;font-weight:600;font-size:12px;cursor:pointer;font-family:Outfit,sans-serif">📋 Salin Kredential</button>
      </div>
      <div class="db-status-note" id="purchaseModalNote"></div>
      <a id="purchaseModalProofLink" href="#" target="_blank" class="db-proof-link" style="display:none">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
        Lihat Bukti Pembayaran
      </a>
      <!-- Upload bukti wrapper (JS mengatur display) -->
      <div id="paymentProofForm" style="display:none" class="db-payment-form">
        <form id="paymentProofFormEl" action="upload_proof.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" id="pay_order_id" name="order_id">
          <input type="hidden" id="pay_method" name="payment_method">
          <div class="db-form-group" style="margin-bottom:12px">
            <label for="payment_proof" style="font-size:13px;font-weight:600;margin-bottom:6px;display:block">Upload Bukti Pembayaran</label>
            <input type="file" id="payment_proof" name="payment_proof" accept="image/jpeg,image/png,image/webp"
                   style="padding:8px;border:1px solid var(--border);border-radius:var(--radius);width:100%;box-sizing:border-box;font-family:Outfit,sans-serif;font-size:13px">
            <div class="db-form-help" style="margin-top:4px">Format JPG, PNG, atau WEBP. Maks. 2MB.</div>
          </div>
          <div class="db-form-actions" style="padding-top:0;border:none">
            <button type="submit" id="btnSubmitPayment" class="btn btn-primary" style="font-size:13px;padding:9px 20px">Kirim Bukti Pembayaran</button>
          </div>
        </form>
      </div>
    </div>
    <div class="db-modal-footer">
      <button id="btnCancelPurchaseModal" class="btn" style="padding:8px 18px;border:1px solid var(--border);border-radius:var(--radius);background:var(--white);cursor:pointer;font-family:Outfit,sans-serif;font-size:13px">Tutup</button>
      <button id="btnCancelOrder" style="display:none;padding:8px 18px;border:1px solid #dc2626;border-radius:var(--radius);background:#fef2f2;color:#dc2626;cursor:pointer;font-family:Outfit,sans-serif;font-size:13px;font-weight:600">
        ✕ Batalkan Order
      </button>
    </div>
  </div>
</div>


<?php mysqli_close($conn); include '../../includes/footer.php'; ?>
<script>
// Handle upload bukti: inner form
document.addEventListener('DOMContentLoaded', () => {
  const formEl        = document.getElementById('paymentProofFormEl');
  const proofContainer = document.getElementById('paymentProofForm');
  if (formEl) {
    formEl.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('btnSubmitPayment');
      const orig = btn ? btn.textContent : '';
      if (btn) { btn.textContent = 'Mengirim...'; btn.disabled = true; }
      try {
        const res  = await fetch(formEl.action, { method: 'POST', body: new FormData(formEl) });
        const data = await res.json();
        if (data.success) {
          document.getElementById('purchaseDetailModal').classList.remove('open');
          document.body.style.overflow = '';
          window.showToast(data.message || 'Bukti berhasil dikirim.', 'success');
          setTimeout(() => window.location.reload(), 1000);
        } else { window.showToast(data.message || 'Gagal mengirim bukti.', 'error'); }
      } catch { window.showToast('Gagal terhubung ke server.', 'error'); }
      finally { if (btn) { btn.textContent = orig; btn.disabled = false; } }
    });
  }
});
</script>
<script src="../../assets/page/js/userdashboard.js"></script>
</body></html>
