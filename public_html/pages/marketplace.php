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
$currentPage  = isset($_GET['page'])   ? max(1, (int)$_GET['page']) : 1;
$perPage      = 6;

// ── Ambil daftar game untuk dropdown filter ─────────────────────────────
$games = [];
$gamesResult = mysqli_query($conn, "SELECT id, name FROM games ORDER BY name ASC");
if ($gamesResult) {
    while ($g = mysqli_fetch_assoc($gamesResult)) {
        $games[] = $g;
    }
}

// ── Build WHERE dan ORDER BY ────────────────────────────────────────────
$whereParts = array("al.status IN ('ready', 'sold')");
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

$countSql = "SELECT COUNT(*) AS total
             FROM account_listing al
             JOIN games g ON al.game_id = g.id
             WHERE $whereClause";

$totalResults = 0;

if (!empty($params)) {
    $countStmt = mysqli_prepare($conn, $countSql);
    if ($countStmt) {
        mysqli_stmt_bind_param($countStmt, $types, ...$params);
        mysqli_stmt_execute($countStmt);
        $countResult = mysqli_stmt_get_result($countStmt);
        $countRow = mysqli_fetch_assoc($countResult);
        $totalResults = (int)($countRow['total'] ?? 0);
        mysqli_stmt_close($countStmt);
    }
} else {
    $countResult = mysqli_query($conn, $countSql);
    if ($countResult) {
        $countRow = mysqli_fetch_assoc($countResult);
        $totalResults = (int)($countRow['total'] ?? 0);
    }
}

$totalPages = max(1, (int)ceil($totalResults / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;

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
        ORDER BY FIELD(al.status,'ready','sold'), $orderBy
        LIMIT ? OFFSET ?";

$filtered = array();

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $listParams = $params;
    $listTypes  = $types . 'ii';
    $listParams[] = $perPage;
    $listParams[] = $offset;

    mysqli_stmt_bind_param($stmt, $listTypes, ...$listParams);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $filtered[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$firstShown = $totalResults > 0 ? $offset + 1 : 0;
$lastShown  = min($offset + count($filtered), $totalResults);

function marketplaceUrl($page, $overrides = array()) {
    global $filterGame, $filterSearch, $filterMin, $filterMax, $filterSort;

    $query = array_merge(array(
        'game' => $filterGame,
        'q'    => $filterSearch,
        'min'  => $filterMin,
        'max'  => $filterMax,
        'sort' => $filterSort,
        'page' => $page,
    ), $overrides);

    foreach ($query as $key => $value) {
        if ($value === '' || $value === null) {
            unset($query[$key]);
        }
    }

    return 'marketplace.php?' . http_build_query($query);
}

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
  <link rel="stylesheet" href="../assets/page/css/marketplace.css">
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
    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($filterSort); ?>">
    <input type="hidden" name="page" value="1">

    <div class="filter-title">Filter</div>

    <div class="filter-group">
      <div class="filter-group-label">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"/>
          <path d="M21 21l-4.35-4.35"/>
        </svg>
        Pencarian
      </div>
      <input class="filter-search-input" type="search" name="q"
             placeholder="Cari judul atau game"
             value="<?php echo htmlspecialchars($filterSearch); ?>">
    </div>

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
        <a href="<?php echo marketplaceUrl(1, array('q' => '')); ?>"
           class="filter-chip-remove">✕</a>
      </span>
    <?php endif; ?>
    <?php if ($filterGame !== ''): ?>
      <span class="filter-chip">
        🎮 <?php echo htmlspecialchars($filterGame); ?>
        <a href="<?php echo marketplaceUrl(1, array('game' => '')); ?>"
           class="filter-chip-remove">✕</a>
      </span>
    <?php endif; ?>
    <?php if ($filterMin !== '' || $filterMax !== ''): ?>
      <span class="filter-chip">
        💰 <?php echo $filterMin !== '' ? formatRp((int)$filterMin) : '0'; ?>
            &ndash;
            <?php echo $filterMax !== '' ? formatRp((int)$filterMax) : '∞'; ?>
        <a href="<?php echo marketplaceUrl(1, array('min' => '', 'max' => '')); ?>"
           class="filter-chip-remove">✕</a>
      </span>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Toolbar -->
  <div class="mp-toolbar">
    <span class="mp-result-count">
      Menampilkan <strong><?php echo $firstShown; ?>-<?php echo $lastShown; ?></strong>
      dari <strong><?php echo $totalResults; ?></strong> akun game
    </span>
    <form method="GET" action="marketplace.php" id="sortForm">
      <input type="hidden" name="game" value="<?php echo htmlspecialchars($filterGame); ?>">
      <input type="hidden" name="q"    value="<?php echo htmlspecialchars($filterSearch); ?>">
      <input type="hidden" name="min"  value="<?php echo htmlspecialchars($filterMin); ?>">
      <input type="hidden" name="max"  value="<?php echo htmlspecialchars($filterMax); ?>">
      <input type="hidden" name="page" value="1">
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
      <?php $isSold = ($item['listing_status'] === 'sold'); ?>
      <div class="listing-card<?php echo $isSold ? ' listing-card--sold' : ''; ?>"
           onclick="window.location.href='listing-detail.php?id=<?php echo (int)$item['id']; ?>'">

        <!-- Thumbnail -->
        <div class="listing-card-img-wrap">
          <img
            class="listing-card-img"
            src="../<?php echo $imgSrc; ?>"
            alt="<?php echo htmlspecialchars($item['title']); ?>"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
          />
          <div class="listing-card-img-placeholder" style="display:none;">🎮</div>
          <?php if ($isSold): ?>
            <div class="listing-sold-overlay">
              <span class="listing-sold-label">TERJUAL</span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Body -->
        <div class="listing-card-body">
          <div class="listing-card-top">
            <div>
              <div class="listing-card-game"><?php echo htmlspecialchars($item['game']); ?></div>
              <div class="listing-card-title"><?php echo htmlspecialchars($item['title']); ?></div>
            </div>
            <?php if ($isSold): ?>
              <span class="badge badge-sold">Terjual</span>
            <?php else: ?>
              <span class="badge <?php echo $badge['class']; ?>"><?php echo $badge['label']; ?></span>
            <?php endif; ?>
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
          <?php if ($isSold): ?>
            <button class="listing-buy-btn listing-buy-btn--sold" disabled>
              Terjual
            </button>
          <?php else: ?>
            <button class="listing-buy-btn"
                    onclick="event.stopPropagation(); window.location.href='listing-detail.php?id=<?php echo (int)$item['id']; ?>'">
              Lihat Detail
            </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav class="mp-pagination" aria-label="Pagination marketplace">
      <a class="mp-page-btn <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>"
         href="<?php echo $currentPage <= 1 ? '#' : marketplaceUrl($currentPage - 1); ?>"
         aria-label="Halaman sebelumnya">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
      </a>

      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= 1): ?>
          <a class="mp-page-btn <?php echo $i === $currentPage ? 'active' : ''; ?>"
             href="<?php echo marketplaceUrl($i); ?>">
            <?php echo $i; ?>
          </a>
        <?php elseif ($i === $currentPage - 2 || $i === $currentPage + 2): ?>
          <span class="mp-page-ellipsis">...</span>
        <?php endif; ?>
      <?php endfor; ?>

      <a class="mp-page-btn <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>"
         href="<?php echo $currentPage >= $totalPages ? '#' : marketplaceUrl($currentPage + 1); ?>"
         aria-label="Halaman berikutnya">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </a>
    </nav>
  <?php endif; ?>

</div><!-- end listing area -->
</div><!-- end mp-body -->

<?php
mysqli_close($conn);
include '../includes/footer.php';
?>
<script src="../assets/page/js/marketplace.js"></script>
</body>
</html>
