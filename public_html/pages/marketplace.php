<?php
// ── Data dummy listings (nanti diganti dari database) ──────────────────
$listings = [
  [
    "id"       => 1,
    "title"    => "Valorant Immortal 3 — All Agents",
    "game"     => "Valorant",
    "rank"     => "Immortal 3",
    "level"    => 150,
    "server"   => "Asia",
    "price"    => 1299000,
    "heroes"   => 22,
    "skins"    => 35,
    "status"   => "verified",
    "image"    => "https://i.pinimg.com/736x/cf/ae/88/cfae886e263126f685510e2f45b82970.jpg",
  ],
  [
    "id"       => 2,
    "title"    => "Mobile Legends Epic — 68% Winrate",
    "game"     => "Mobile Legends",
    "rank"     => "Epic",
    "level"    => 85,
    "server"   => "Indonesia",
    "price"    => 450000,
    "heroes"   => 68,
    "skins"    => 15,
    "status"   => "hot",
    "image"    => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQf8Nd_t-goiTMb9piIKbt8MwubxvGgCY3QyQ&s",
  ],
  [
    "id"       => 3,
    "title"    => "PUBG Mobile Conqueror S12",
    "game"     => "PUBG Mobile",
    "rank"     => "Conqueror",
    "level"    => 72,
    "server"   => "Asia",
    "price"    => 895000,
    "heroes"   => 0,
    "skins"    => 12,
    "status"   => "verified",
    "image"    => "https://upload.wikimedia.org/wikipedia/en/4/44/PlayerUnknown%27s_Battlegrounds_Mobile.webp",
  ],
  [
    "id"       => 4,
    "title"    => "Free Fire Heroic — Full Set",
    "game"     => "Free Fire",
    "rank"     => "Heroic",
    "level"    => 60,
    "server"   => "Indonesia",
    "price"    => 320000,
    "heroes"   => 0,
    "skins"    => 28,
    "status"   => "new",
    "image"    => "https://play-lh.googleusercontent.com/EJ83sg58Oo2gAjMHFxFVLM6Z53kuH4_R0M7Yq7gts5fWSIlFchUlmskG1vJKMoncmfOxBXcgJyIaO-nak6sO-MM",
  ],
  [
    "id"       => 5,
    "title"    => "Genshin Impact AR55 — C2 Hu Tao",
    "game"     => "Genshin Impact",
    "rank"     => "AR 55",
    "level"    => 55,
    "server"   => "Asia",
    "price"    => 2100000,
    "heroes"   => 30,
    "skins"    => 5,
    "status"   => "verified",
    "image"    => "https://static.wikia.nocookie.net/logopedia/images/b/bc/Genshin_Impact_Icon_Version_2.5.png/revision/latest/scale-to-width-down/250?cb=20240613170351",
  ],
  [
    "id"       => 6,
    "title"    => "Mobile Legends Mythic — 500 Points",
    "game"     => "Mobile Legends",
    "rank"     => "Mythic",
    "level"    => 110,
    "server"   => "Indonesia",
    "price"    => 750000,
    "heroes"   => 92,
    "skins"    => 22,
    "status"   => "hot",
    "image"    => "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQf8Nd_t-goiTMb9piIKbt8MwubxvGgCY3QyQ&s",
  ],
  [
    "id"       => 7,
    "title"    => "Valorant Diamond 1 — Rare Skins",
    "game"     => "Valorant",
    "rank"     => "Diamond 1",
    "level"    => 98,
    "server"   => "Asia",
    "price"    => 580000,
    "heroes"   => 18,
    "skins"    => 8,
    "status"   => "new",
    "image"    => "https://i.pinimg.com/736x/cf/ae/88/cfae886e263126f685510e2f45b82970.jpg",
  ],
  [
    "id"       => 8,
    "title"    => "Honkai Star Rail TL50 — 5★ Chars",
    "game"     => "Star Rail",
    "rank"     => "TL 50",
    "level"    => 50,
    "server"   => "Asia",
    "price"    => 1550000,
    "heroes"   => 12,
    "skins"    => 3,
    "status"   => "verified",
    "image"    => "https://static.wikia.nocookie.net/houkai-star-rail/images/8/84/Honkai_Star_Rail_App.png/revision/latest/scale-to-width/360?cb=20260313085854",
  ],
];

// ── Filter dari URL ────────────────────────────────────────────────────
$filterGame   = $_GET['game']   ?? '';
$filterSearch = $_GET['q']      ?? '';
$filterSort   = $_GET['sort']   ?? 'newest';
$filterMin    = $_GET['min']    ?? '';
$filterMax    = $_GET['max']    ?? '';

// Terapkan filter
$filtered = array_filter($listings, function($item) use ($filterGame, $filterSearch, $filterMin, $filterMax) {
  if ($filterGame   && $item['game']  !== $filterGame)  return false;
  if ($filterSearch && stripos($item['title'], $filterSearch) === false &&
                       stripos($item['game'],  $filterSearch) === false) return false;
  if ($filterMin    && $item['price'] < (int)$filterMin) return false;
  if ($filterMax    && $item['price'] > (int)$filterMax) return false;
  return true;
});

// Sort
usort($filtered, function($a, $b) use ($filterSort) {
  if ($filterSort === 'price_asc')  return $a['price'] - $b['price'];
  if ($filterSort === 'price_desc') return $b['price'] - $a['price'];
  return $b['id'] - $a['id']; // newest
});

$games  = ['Valorant', 'Mobile Legends', 'Free Fire', 'PUBG Mobile', 'Genshin Impact', 'Star Rail'];
$totalResults = count($filtered);

// Helper: format harga
function formatRp($price) {
  return 'Rp ' . number_format($price, 0, ',', '.');
}

include '../includes/header.php';
?>
</head>
<body>

<!-- ══ PAGE HEADER ══════════════════════════════════════ -->
<div class="mp-header">
  <div class="mp-header-inner">
    <div>
      <h2>Marketplace</h2>
      <div class="mp-header-sub">
        Temukan akun game terbaik dengan harga terjangkau
        <?php if ($filterGame): ?>
          Menampilkan: <strong><?php echo htmlspecialchars($filterGame); ?></strong>
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
    <?php if ($filterSearch): ?>
      <input type="hidden" name="q" value="<?php echo htmlspecialchars($filterSearch); ?>">
    <?php endif; ?>
    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($filterSort); ?>">

    <div class="filter-title">Filter</div>

    <!-- Game filter -->
    <div class="filter-group">
      <div class="filter-group-label">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="2" y="7" width="20" height="15" rx="2"/>
          <path d="M16 3h-2a2 2 0 0 0-4 0H8"/>
        </svg>
        <?php echo $filterGame?>
      </div>
      <input type="hidden" name="game" id="gameInput" value="<?php echo htmlspecialchars($filterGame); ?>">
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

  <!-- ── Listing Area ────────────────────────────────── -->
  <div>

    <!-- Active filter chips -->
    <?php if ($filterGame || $filterSearch || $filterMin || $filterMax): ?>
    <div class="active-filters">
      <?php if ($filterSearch): ?>
        <span class="filter-chip">
          🔍 "<?php echo htmlspecialchars($filterSearch); ?>"
          <a href="marketplace.php?<?php echo http_build_query(['game'=>$filterGame,'min'=>$filterMin,'max'=>$filterMax,'sort'=>$filterSort]); ?>"
             class="filter-chip-remove">✕</a>
        </span>
      <?php endif; ?>
      <?php if ($filterMin || $filterMax): ?>
        <span class="filter-chip">
          💰 <?php echo $filterMin ? formatRp((int)$filterMin) : '0'; ?> – <?php echo $filterMax ? formatRp((int)$filterMax) : '∞'; ?>
          <a href="marketplace.php?<?php echo http_build_query(['game'=>$filterGame,'q'=>$filterSearch,'sort'=>$filterSort]); ?>"
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
        <input type="hidden" name="game"  value="<?php echo htmlspecialchars($filterGame); ?>">
        <input type="hidden" name="q"     value="<?php echo htmlspecialchars($filterSearch); ?>">
        <input type="hidden" name="min"   value="<?php echo htmlspecialchars($filterMin); ?>">
        <input type="hidden" name="max"   value="<?php echo htmlspecialchars($filterMax); ?>">
        <select class="sort-select" name="sort" onchange="this.form.submit()">
          <option value="newest"     <?php echo $filterSort==='newest'     ? 'selected':'' ?>>Terbaru</option>
          <option value="price_asc"  <?php echo $filterSort==='price_asc'  ? 'selected':'' ?>>Harga: Murah dulu</option>
          <option value="price_desc" <?php echo $filterSort==='price_desc' ? 'selected':'' ?>>Harga: Mahal dulu</option>
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
          $badgeClass = match($item['status']) {
            'verified' => 'badge-verified',
            'hot'      => 'badge-hot',
            default    => 'badge-new',
          };
          $badgeLabel = match($item['status']) {
            'verified' => 'Verified',
            'hot'      => '🔥 Hot',
            default    => 'New',
          };
        ?>
        <div class="listing-card"
             onclick="window.location.href='listing-detail.php?id=<?php echo $item['id']; ?>'">
          <!-- Thumbnail -->
          <img
            class="listing-card-img"
            src="<?php echo htmlspecialchars($item['image']); ?>"
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
              <span class="badge <?php echo $badgeClass; ?>"><?php echo $badgeLabel; ?></span>
            </div>

            <div class="listing-meta">
              <!-- Rank -->
              <div class="listing-meta-item">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <?php echo htmlspecialchars($item['rank']); ?>
              </div>
              <!-- Level -->
              <div class="listing-meta-item">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                Level <?php echo $item['level']; ?>
              </div>
              <!-- Server -->
              <div class="listing-meta-item">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
                <?php echo htmlspecialchars($item['server']); ?>
              </div>
              <!-- Heroes / Skins -->
              <?php if ($item['heroes'] > 0): ?>
              <div class="listing-meta-item">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <?php echo $item['heroes']; ?> heroes
              </div>
              <?php endif; ?>
              <div class="listing-meta-item">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <?php echo $item['skins']; ?> skins
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="listing-card-footer">
            <span class="listing-price"><?php echo formatRp($item['price']); ?></span>
            <button class="listing-buy-btn"
                    onclick="event.stopPropagation(); window.location.href='listing-detail.php?id=<?php echo $item['id']; ?>'">
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
include '../includes/footer.php';
?>
</body>
</html>