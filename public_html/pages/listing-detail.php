<?php
/** @var mysqli $conn */
require_once '../includes/db.php';

// ── Auth & ID ────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: marketplace.php');
    exit;
}

// ── Ambil detail listing ─────────────────────────────────────────────────────
$stmt = mysqli_prepare($conn,
    "SELECT
        al.listing_id,
        al.title,
        al.description,
        al.server,
        al.image_url      AS image,
        al.price,
        al.level,
        al.rank,
        al.account_login_type,
        al.status         AS listing_status,
        al.created_at,
        al.user_id,
        al.`id`           AS account_id,
        g.name            AS game,
        g.image_url       AS game_image,
        u.username        AS seller_name,
        u.created_at      AS seller_since
     FROM account_listing al
     JOIN games  g ON al.game_id  = g.id
     JOIN users  u ON al.user_id  = u.ID_User
     WHERE al.listing_id = ? AND al.status = 'ready'");

if (!$stmt) {
    header('Location: marketplace.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$listing = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$listing) {
    header('Location: marketplace.php');
    exit;
}

// ── Listing lain dari game yang sama ─────────────────────────────────────────
$relStmt = mysqli_prepare($conn,
    "SELECT al.listing_id, al.title, al.price, al.image_url, al.rank, al.level, g.name AS game
     FROM account_listing al
     JOIN games g ON al.game_id = g.id
     WHERE g.name = ? AND al.listing_id != ? AND al.status = 'ready'
     ORDER BY al.created_at DESC
     LIMIT 4");

$relatedListings = [];
if ($relStmt) {
    mysqli_stmt_bind_param($relStmt, 'si', $listing['game'], $id);
    mysqli_stmt_execute($relStmt);
    $relResult = mysqli_stmt_get_result($relStmt);
    while ($r = mysqli_fetch_assoc($relResult)) {
        $relatedListings[] = $r;
    }
    mysqli_stmt_close($relStmt);
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function formatRp($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}
function getBadge($listing) {
    $isNew = (time() - strtotime($listing['created_at'])) < (3 * 24 * 3600);
    if ($isNew)                         return ['class' => 'badge-new',      'label' => 'New'];
    if ($listing['price'] >= 500000)    return ['class' => 'badge-hot',      'label' => '🔥 Hot'];
    return                                     ['class' => 'badge-verified', 'label' => 'Ready'];
}
function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)         return 'Baru saja';
    if ($diff < 3600)       return floor($diff/60) . ' menit lalu';
    if ($diff < 86400)      return floor($diff/3600) . ' jam lalu';
    if ($diff < 2592000)    return floor($diff/86400) . ' hari lalu';
    return date('d M Y', strtotime($datetime));
}

$badge      = getBadge($listing);
$imgSrc     = !empty($listing['image']) ? '../' . htmlspecialchars($listing['image']) : null;
$gameImg    = !empty($listing['game_image']) ? '../' . htmlspecialchars($listing['game_image']) : null;
$heroImg    = $imgSrc ?? $gameImg;
$sellerInit = strtoupper(mb_substr($listing['seller_name'], 0, 1));

$page_title  = htmlspecialchars($listing['title']) . ' — ThurzShop';
$active_page = 'marketplace';

include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/page/css/listing-detail.css">
</head>
<body>

<!-- ══ BREADCRUMB ══════════════════════════════════════════════════════════ -->
<div class="ld-breadcrumb">
  <div class="ld-breadcrumb-inner">
    <a href="../index.php">Home</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    <a href="marketplace.php">Marketplace</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    <span><?php echo htmlspecialchars($listing['title']); ?></span>
  </div>
</div>

<!-- ══ MAIN CONTENT ════════════════════════════════════════════════════════ -->
<div class="ld-container">

  <!-- ── LEFT COLUMN ────────────────────────────────────────────────────── -->
  <div class="ld-left">

    <!-- Hero image -->
    <div class="ld-gallery">
      <?php if ($heroImg): ?>
        <img src="<?php echo $heroImg; ?>"
             alt="<?php echo htmlspecialchars($listing['title']); ?>"
             class="ld-gallery-img"
             onerror="this.style.display='none'; document.getElementById('galleryPlaceholder').style.display='flex';" />
      <?php endif; ?>
      <div class="ld-gallery-placeholder" id="galleryPlaceholder" <?php echo $heroImg ? 'style="display:none"' : ''; ?>>
        🎮
      </div>
      <!-- Badge overlay -->
      <span class="ld-gallery-badge badge <?php echo $badge['class']; ?>"><?php echo $badge['label']; ?></span>
    </div>

    <!-- Description card -->
    <div class="ld-card">
      <div class="ld-card-header">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
          <polyline points="10 9 9 9 8 9"/>
        </svg>
        Deskripsi Akun
      </div>
      <div class="ld-description">
        <?php if (!empty($listing['description'])): ?>
          <?php echo nl2br(htmlspecialchars($listing['description'])); ?>
        <?php else: ?>
          <span class="ld-no-desc">Tidak ada deskripsi tersedia.</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Specs card -->
    <div class="ld-card">
      <div class="ld-card-header">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="2" y="3" width="20" height="14" rx="2"/>
          <line x1="8" y1="21" x2="16" y2="21"/>
          <line x1="12" y1="17" x2="12" y2="21"/>
        </svg>
        Detail Akun
      </div>
      <div class="ld-specs-grid">
        <div class="ld-spec-item">
          <div class="ld-spec-label">Game</div>
          <div class="ld-spec-value"><?php echo htmlspecialchars($listing['game']); ?></div>
        </div>
        <?php if (!empty($listing['rank'])): ?>
        <div class="ld-spec-item">
          <div class="ld-spec-label">Rank</div>
          <div class="ld-spec-value">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            <?php echo htmlspecialchars($listing['rank']); ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($listing['level'])): ?>
        <div class="ld-spec-item">
          <div class="ld-spec-label">Level</div>
          <div class="ld-spec-value">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
            </svg>
            Level <?php echo (int)$listing['level']; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($listing['server'])): ?>
        <div class="ld-spec-item">
          <div class="ld-spec-label">Server</div>
          <div class="ld-spec-value">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10"/>
              <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            <?php echo htmlspecialchars($listing['server']); ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($listing['account_login_type'])): ?>
        <div class="ld-spec-item">
          <div class="ld-spec-label">Login Via</div>
          <div class="ld-spec-value">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="11" width="18" height="11" rx="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <?php echo htmlspecialchars($listing['account_login_type']); ?>
          </div>
        </div>
        <?php endif; ?>
        <div class="ld-spec-item">
          <div class="ld-spec-label">Status</div>
          <div class="ld-spec-value ld-spec-status">
            <span class="ld-status-dot"></span>
            Tersedia
          </div>
        </div>
        <div class="ld-spec-item">
          <div class="ld-spec-label">Diposting</div>
          <div class="ld-spec-value"><?php echo timeAgo($listing['created_at']); ?></div>
        </div>
      </div>
    </div>

    <!-- Safety info -->
    <div class="ld-safety-banner">
      <div class="ld-safety-icon">🛡️</div>
      <div>
        <div class="ld-safety-title">Transaksi Aman</div>
        <div class="ld-safety-sub">Selalu gunakan metode pembayaran resmi ThurzShop. Jangan transfer langsung ke penjual sebelum transaksi dikonfirmasi sistem.</div>
      </div>
    </div>

  </div><!-- end left -->

  <!-- ── RIGHT COLUMN (Sticky Sidebar) ──────────────────────────────────── -->
  <aside class="ld-sidebar">

    <!-- Price card -->
    <div class="ld-price-card">
      <!-- Game label -->
      <div class="ld-game-label">
        <?php if ($gameImg): ?>
          <img src="<?php echo $gameImg; ?>" alt="<?php echo htmlspecialchars($listing['game']); ?>" class="ld-game-icon-img"
               onerror="this.style.display='none'">
        <?php else: ?>
          <span class="ld-game-icon-fallback">🎮</span>
        <?php endif; ?>
        <span><?php echo htmlspecialchars($listing['game']); ?></span>
      </div>

      <!-- Title -->
      <h1 class="ld-title"><?php echo htmlspecialchars($listing['title']); ?></h1>

      <!-- Quick meta chips -->
      <div class="ld-meta-chips">
        <?php if (!empty($listing['rank'])): ?>
          <span class="ld-chip">⭐ <?php echo htmlspecialchars($listing['rank']); ?></span>
        <?php endif; ?>
        <?php if (!empty($listing['level'])): ?>
          <span class="ld-chip">⚡ Level <?php echo (int)$listing['level']; ?></span>
        <?php endif; ?>
        <?php if (!empty($listing['server'])): ?>
          <span class="ld-chip">🌐 <?php echo htmlspecialchars($listing['server']); ?></span>
        <?php endif; ?>
        <?php if (!empty($listing['account_login_type'])): ?>
          <span class="ld-chip">🔐 <?php echo htmlspecialchars($listing['account_login_type']); ?></span>
        <?php endif; ?>
      </div>

      <div class="ld-price-divider"></div>

      <!-- Price -->
      <div class="ld-price-label">Harga</div>
      <div class="ld-price"><?php echo formatRp($listing['price']); ?></div>

      <!-- CTA buttons -->
      <?php if (isset($_SESSION['user'])): ?>
        <button class="ld-btn-buy" id="btnBeli">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
          Beli Sekarang
        </button>
      <?php else: ?>
        <a href="login.php" class="ld-btn-buy">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
          </svg>
          Login untuk Membeli
        </a>
      <?php endif; ?>

      <!-- Trust badges -->
      <div class="ld-trust-row">
        <div class="ld-trust-item">
          <svg width="13" height="13" fill="none" stroke="var(--green)" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          Escrow Aman
        </div>
        <div class="ld-trust-item">
          <svg width="13" height="13" fill="none" stroke="var(--green)" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Akun Terverifikasi
        </div>
        <div class="ld-trust-item">
          <svg width="13" height="13" fill="none" stroke="var(--green)" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          Respon Cepat
        </div>
      </div>
    </div>

    <!-- Seller card -->
    <div class="ld-card ld-seller-card">
      <div class="ld-card-header">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        Penjual
      </div>
      <div class="ld-seller-info">
        <span class="ld-seller-initials"><?php echo $sellerInit; ?></span>
        <div>
          <div class="ld-seller-name"><?php echo htmlspecialchars($listing['seller_name']); ?></div>
          <div class="ld-seller-since">Member sejak <?php echo date('M Y', strtotime($listing['seller_since'])); ?></div>
        </div>
      </div>
    </div>

    <!-- Back button -->
    <a href="marketplace.php" class="ld-btn-back">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M19 12H5M12 5l-7 7 7 7"/>
      </svg>
      Kembali ke Marketplace
    </a>

  </aside>

</div><!-- end ld-container -->

<!-- ══ RELATED LISTINGS ════════════════════════════════════════════════════ -->
<?php if (!empty($relatedListings)): ?>
<div class="ld-related">
  <div class="ld-related-inner">
    <div class="ld-related-header">
      <div>
        <div class="section-title">Listing Serupa</div>
        <div class="section-sub">Akun <?php echo htmlspecialchars($listing['game']); ?> lainnya</div>
      </div>
      <a href="marketplace.php?game=<?php echo urlencode($listing['game']); ?>" class="view-all">
        Lihat Semua
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
    <div class="ld-related-grid">
      <?php foreach ($relatedListings as $rel):
        $relImg = !empty($rel['image_url']) ? '../' . htmlspecialchars($rel['image_url']) : null;
      ?>
      <div class="listing-card" onclick="window.location.href='listing-detail.php?id=<?php echo (int)$rel['listing_id']; ?>'">
        <?php if ($relImg): ?>
          <img class="listing-card-img" src="<?php echo $relImg; ?>"
               alt="<?php echo htmlspecialchars($rel['title']); ?>"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <?php endif; ?>
        <div class="listing-card-img-placeholder" <?php echo $relImg ? 'style="display:none"' : ''; ?>>🎮</div>
        <div class="listing-card-body">
          <div class="listing-card-top">
            <div>
              <div class="listing-card-game"><?php echo htmlspecialchars($rel['game']); ?></div>
              <div class="listing-card-title"><?php echo htmlspecialchars($rel['title']); ?></div>
            </div>
          </div>
          <div class="listing-meta">
            <?php if (!empty($rel['rank'])): ?>
            <div class="listing-meta-item">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              <?php echo htmlspecialchars($rel['rank']); ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($rel['level'])): ?>
            <div class="listing-meta-item">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
              </svg>
              Level <?php echo (int)$rel['level']; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="listing-card-footer">
          <span class="listing-price"><?php echo formatRp($rel['price']); ?></span>
          <button class="listing-buy-btn">Lihat</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══ MODAL KONFIRMASI BELI ════════════════════════════════════════════════ -->
<div class="ld-modal-overlay" id="modalBuy" aria-hidden="true">
  <div class="ld-modal">
    <div class="ld-modal-header">
      <h3>Konfirmasi Pembelian</h3>
      <button class="ld-modal-close" id="modalClose" aria-label="Tutup">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="ld-modal-body">
      <div class="ld-modal-item">
        <span class="ld-modal-label">Akun</span>
        <span class="ld-modal-value"><?php echo htmlspecialchars($listing['title']); ?></span>
      </div>
      <div class="ld-modal-item">
        <span class="ld-modal-label">Game</span>
        <span class="ld-modal-value"><?php echo htmlspecialchars($listing['game']); ?></span>
      </div>
      <div class="ld-modal-item">
        <span class="ld-modal-label">Penjual</span>
        <span class="ld-modal-value"><?php echo htmlspecialchars($listing['seller_name']); ?></span>
      </div>
      <div class="ld-modal-divider"></div>
      <div class="ld-modal-item ld-modal-total">
        <span class="ld-modal-label">Total</span>
        <span class="ld-modal-price"><?php echo formatRp($listing['price']); ?></span>
      </div>
    </div>
    <div class="ld-modal-footer">
      <button class="ld-modal-btn-cancel" id="modalCancel">Batal</button>
      <button class="ld-modal-btn-confirm" id="modalConfirm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Konfirmasi Beli
      </button>
    </div>
  </div>
</div>

<?php
mysqli_close($conn);
include '../includes/footer.php';
?>
<script src="../assets/page/js/listing-detail.js"></script>
</body>
</html>
