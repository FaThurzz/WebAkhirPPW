<?php
/** @var mysqli $conn */
// ── Koneksi Database ────────────────────────────────────────────────────
require_once '../includes/db.php';

// ── Filter dari URL ─────────────────────────────────────────────────────
$filterGame   = isset($_GET['game'])   ? trim($_GET['game'])   : '';
$filterSearch = isset($_GET['q'])      ? trim($_GET['q'])      : '';
$filterSort   = isset($_GET['sort'])   ? trim($_GET['sort'])   : 'newest';
$filterMin    = isset($_GET['min'])    ? trim($_GET['min'])    : '';
$filterMax    = isset($_GET['max'])    ? trim($_GET['max'])    : '';

// ── Ambil daftar game untuk dropdown filter ─────────────────────────────
$games = [];
$gamesResult = mysqli_query($conn, "SELECT id, name FROM games ORDER BY name ASC");
if ($gamesResult) {
    while ($g = mysqli_fetch_assoc($gamesResult)) {
        $games[] = $g;
    }
}

// ── Build WHERE dan ORDER BY ────────────────────────────────────────────
$whereParts = array("al.status = 'ready'");
$params     = array();
$types      = '';

if ($filterGame !== '') {
    $whereParts[] = "g.name = ?";
    $params[]     = $filterGame;
    $types       .= 's';
}
if ($filterSearch !== '') {
    $whereParts[] = "(al.title LIKE ? OR g.name LIKE ?)";
    $params[]     = "%" . $filterSearch . "%";
    $params[]     = "%" . $filterSearch . "%";
    $types       .= 'ss';
}
if ($filterMin !== '') {
    $whereParts[] = "al.price >= ?";
    $params[]     = (float)$filterMin;
    $types       .= 'd';
}
if ($filterMax !== '') {
    $whereParts[] = "al.price <= ?";
    $params[]     = (float)$filterMax;
    $types       .= 'd';
}

if ($filterSort === 'price_asc') {
    $orderBy = 'al.price ASC';
} elseif ($filterSort === 'price_desc') {
    $orderBy = 'al.price DESC';
} else {
    $orderBy = 'al.created_at DESC';
}

$whereClause = implode(' AND ', $whereParts);

$sql = "SELECT
            al.listing_id  AS id,
            al.title,
            al.description,
            al.server,
            al.image_url   AS image,
            al.price,
            al.level,
            al.rank,
            al.account_login_type,
            al.status      AS listing_status,
            al.created_at,
            g.name         AS game,
            g.image_url    AS game_image
        FROM account_listing al
        JOIN games g ON al.game_id = g.id
        WHERE $whereClause
        ORDER BY $orderBy";

$filtered = array();

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $filtered[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $filtered[] = $row;
        }
    }
}

$totalResults = count($filtered);

// Helper: format harga Rupiah
function formatRp($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Helper: badge otomatis dari data listing
function getBadge($listing) {
    $createdAt = strtotime($listing['created_at']);
    $isNew     = (time() - $createdAt) < (3 * 24 * 3600);
    if ($isNew)                         return array('class' => 'badge-new',      'label' => 'New');
    if ($listing['price'] >= 500000)    return array('class' => 'badge-hot',      'label' => '🔥 Hot');
    return                                     array('class' => 'badge-verified', 'label' => 'Ready');
}

include '../includes/header.php';
?>
<head>
  <link rel="stylesheet" href="../assets/page/marketplace.css">
</head>
<body>

<!-- ══ PAGE HEADER ══════════════════════════════════════ -->
<div class="mp-header">
  <div class="mp-header-inner">
    <div>
      <h2>Marketplace</h2>
      <div class="mp-header-sub">
        Temukan akun game terbaik dengan harga terjangkau
        <?php if ($filterGame !== ''): ?>
          &mdash; Menampilkan: <strong><?php echo htmlspecialchars($filterGame); ?></strong>
        <?php endif; ?>
      </div>
    </div>
    <button class="filter-toggle-btn" id="filterToggle">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="4" y1="6"  x2="20" y2="6"/>
        <line x1="8" y1="12" x2="20" y2="12"/>
        <line x1="12" y1="18" x2="20" y2="18"/>
      </svg>
      Filter
    </button>
  </div>
</div>

<!-- ══ BODY ════════════════════════════════════════════ -->
<div class="mp-body">

<!-- ── Sidebar Filter ── -->
<aside class="filter-sidebar" id="filterSidebar">
  <form method="GET" action="marketplace.php" id="filterForm">
    <?php if ($filterSearch !== ''): ?>
      <input type="hidden" name="q" value="<?php echo htmlspecialchars($filterSearch); ?>">
    <?php endif; ?>
    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($filterSort); ?>">

    <div class="filter-title">Filter</div>

    <!-- Game filter dropdown dari DB -->
    <div class="filter-group">
      <div class="filter-group-label">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="2" y="7" width="20" height="15" rx="2"/>
          <path d="M16 3h-2a2 2 0 0 0-4 0H8"/>
        </svg>
        Game
      </div>
      <select name="game" class="sort-select" style="width:100%;margin-top:6px;" onchange="this.form.submit()">
        <option value="">Semua Game</option>
        <?php foreach ($games as $g): ?>
          <option value="<?php echo htmlspecialchars($g['name']); ?>"
            <?php echo ($filterGame === $g['name']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($g['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <hr class="filter-divider">

    <!-- Price range -->
    <div class="filter-group">
      <div class="filter-group-label">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 8v4l3 3"/>
        </svg>
        Harga (Rp)
      </div>
      <div class="price-range">
        <input class="price-input" type="number" name="min" placeholder="Min"
               value="<?php echo htmlspecialchars($filterMin); ?>">
        <span class="price-dash">—</span>
        <input class="price-input" type="number" name="max" placeholder="Max"
               value="<?php echo htmlspecialchars($filterMax); ?>">
      </div>
      <button type="submit" class="filter-apply-btn" style="margin-top:12px;">
        Terapkan Filter
      </button>
    </div>

  </form>
</aside>

<!-- ── Listing Area ── -->
<div>

  <!-- Active filter chips -->
  <?php if ($filterGame !== '' || $filterSearch !== '' || $filterMin !== '' || $filterMax !== ''): ?>
  <div class="active-filters">
    <?php if ($filterSearch !== ''): ?>
      <span class="filter-chip">
        🔍 "<?php echo htmlspecialchars($filterSearch); ?>"
        <a href="marketplace.php?<?php echo http_build_query(array('game'=>$filterGame,'min'=>$filterMin,'max'=>$filterMax,'sort'=>$filterSort)); ?>"
           class="filter-chip-remove">✕</a>
      </span>
    <?php endif; ?>
    <?php if ($filterGame !== ''): ?>
      <span class="filter-chip">
        🎮 <?php echo htmlspecialchars($filterGame); ?>
        <a href="marketplace.php?<?php echo http_build_query(array('q'=>$filterSearch,'min'=>$filterMin,'max'=>$filterMax,'sort'=>$filterSort)); ?>"
           class="filter-chip-remove">✕</a>
      </span>
    <?php endif; ?>
    <?php if ($filterMin !== '' || $filterMax !== ''): ?>
      <span class="filter-chip">
        💰 <?php echo $filterMin !== '' ? formatRp((int)$filterMin) : '0'; ?>
            &ndash;
            <?php echo $filterMax !== '' ? formatRp((int)$filterMax) : '∞'; ?>
        <a href="marketplace.php?<?php echo http_build_query(array('game'=>$filterGame,'q'=>$filterSearch,'sort'=>$filterSort)); ?>"
           class="filter-chip-remove">✕</a>
      </span>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Toolbar -->
  <div class="mp-toolbar">
    <span class="mp-result-count">
      Menampilkan <strong><?php echo $totalResults; ?></strong> akun game
    </span>
    <form method="GET" action="marketplace.php" id="sortForm">
      <input type="hidden" name="game" value="<?php echo htmlspecialchars($filterGame); ?>">
      <input type="hidden" name="q"    value="<?php echo htmlspecialchars($filterSearch); ?>">
      <input type="hidden" name="min"  value="<?php echo htmlspecialchars($filterMin); ?>">
      <input type="hidden" name="max"  value="<?php echo htmlspecialchars($filterMax); ?>">
      <select class="sort-select" name="sort" onchange="this.form.submit()">
        <option value="newest"     <?php echo $filterSort === 'newest'     ? 'selected' : ''; ?>>Terbaru</option>
        <option value="price_asc"  <?php echo $filterSort === 'price_asc'  ? 'selected' : ''; ?>>Harga: Murah dulu</option>
        <option value="price_desc" <?php echo $filterSort === 'price_desc' ? 'selected' : ''; ?>>Harga: Mahal dulu</option>
      </select>
    </form>
  </div>

  <!-- Grid listing -->
  <div class="listing-grid">
    <?php if (empty($filtered)): ?>
      <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Tidak ada akun ditemukan</h3>
        <p>Coba ubah filter atau kata kunci pencarianmu.</p>
      </div>
    <?php else: ?>
      <?php foreach ($filtered as $item):
        $badge = getBadge($item);
        // Gambar: pakai image listing jika ada, fallback ke image game
        $imgSrc = (!empty($item['image'])) ? htmlspecialchars($item['image']) : htmlspecialchars($item['game_image']);
      ?>
      <div class="listing-card"
           onclick="window.location.href='listing-detail.php?id=<?php echo (int)$item['id']; ?>'">

        <!-- Thumbnail -->
        <img
          class="listing-card-img"
          src="../<?php echo $imgSrc; ?>"
          alt="<?php echo htmlspecialchars($item['title']); ?>"
          onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        />
        <div class="listing-card-img-placeholder" style="display:none;">🎮</div>

        <!-- Body -->
        <div class="listing-card-body">
          <div class="listing-card-top">
            <div>
              <div class="listing-card-game"><?php echo htmlspecialchars($item['game']); ?></div>
              <div class="listing-card-title"><?php echo htmlspecialchars($item['title']); ?></div>
            </div>
            <span class="badge <?php echo $badge['class']; ?>"><?php echo $badge['label']; ?></span>
          </div>

          <div class="listing-meta">
            <?php if (!empty($item['rank'])): ?>
            <div class="listing-meta-item">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              <?php echo htmlspecialchars($item['rank']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['level'])): ?>
            <div class="listing-meta-item">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
              </svg>
              Level <?php echo (int)$item['level']; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['server'])): ?>
            <div class="listing-meta-item">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
              </svg>
              <?php echo htmlspecialchars($item['server']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['account_login_type'])): ?>
            <div class="listing-meta-item">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <?php echo htmlspecialchars($item['account_login_type']); ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Footer -->
        <div class="listing-card-footer">
          <span class="listing-price"><?php echo formatRp($item['price']); ?></span>
          <button class="listing-buy-btn"
                  onclick="event.stopPropagation(); window.location.href='listing-detail.php?id=<?php echo (int)$item['id']; ?>'">
            Lihat Detail
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div><!-- end listing area -->
</div><!-- end mp-body -->

<?php
mysqli_close($conn);
include '../includes/footer.php';
?>
<script src="../assets/page/js/marketplace.js"></script>
</body>
</html>