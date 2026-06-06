<?php

$page_title = "Home ThurzShop";
$active_page = "dashboard";
include 'includes/header.php';


date_default_timezone_set('Asia/Jakarta');

$hour = (int) date("H");
if ($hour >= 5 && $hour < 12) {
    $greeting    = "Selamat Pagi, Gamer! ☀️";
} elseif ($hour >= 12 && $hour < 18) {
    $greeting    = "Selamat Siang, Gamer! 🌤️";
} else {
    $greeting    = "Selamat Malam, Gamer! 🌙";
}
?>


<!-- ══ HERO ════════════════════════════════════════ -->
<section style="background: var(--white); border-bottom: 1px solid var(--border);">
  <div class="hero">
    <!-- Left -->
    <div class="hero-left">
      <div class="hero-badge">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        <?php echo $greeting; ?> , Trusted web by 50k+ Gamers
      </div>

      <h1>Safe &amp; Secure Gaming<br/>Asset Marketplace</h1>
      <p>Buy and sell verified game accounts, items, and services with absolute confidence. Our escrow system ensures total transaction security.</p>
      <div class="hero-btns">
        <a href="#hot-deals" class="btn-ghost-lg">View Hot Deals</a>
      </div>
    </div>

    <!-- Right: Hero Card -->
    <div class="hero-card">
      <img src="https://i.pinimg.com/736x/27/ff/b3/27ffb3a31d27a69fd736062f18f9965e.jpg" alt="#">
      <div class="secure-badge">
        <div class="secure-badge-icon">
          <svg width="14" height="14" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
        </div>
        <div>
          <div class="secure-badge-text">100% Secure</div>
          <div class="secure-badge-sub">Escrow Protection</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ BROWSE BY GAME ═══════════════════════════════ -->
<?php
    include 'includes/db.php';
    /** @var mysqli $conn */
    $games = mysqli_query($conn, "SELECT * FROM games ORDER BY listing_count DESC");
?>

<div style="background: var(--white);">
  <div class="section">
    <div class="section-header">
      <div>
        <div class="section-title">Browse by Game</div>
        <div class="section-sub">Find premium accounts for your favorite titles</div>
        <div class="category-count">Total game tersedia: <?php echo mysqli_num_rows($games); ?> kategori</div>
      </div>
      <a href="pages/marketplace.php" class="view-all">
        View All Categories
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </a>
    </div>
    <div class="games-grid">
      <?php foreach ($games as $game): ?>
      <div class="game-card">
        <div class="game-icon">
          <img
            src="<?php echo htmlspecialchars($game['image_url']); ?>"
            alt="<?php echo htmlspecialchars($game['name']); ?>"
            style="width:56px; object-fit:cover; border-radius:12px; display:block;"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
          />
          <!-- Fallback emoji jika URL gambar gagal dimuat -->
          <span style="display:none; width:56px; height:56px; font-size:28px; align-items:center; justify-content:center;">🎮</span>
        </div>
        <div class="game-name"><?php echo htmlspecialchars($game['name']); ?></div>
        <div class="game-count"><?php echo $game['listing_count']; ?> listings</div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<!-- ══ HOT DEALS ═════════════════════════════════════ -->
<div id="hot-deals" style="background: var(--bg);">
  <div class="section">
    <div class="section-header">
      <div>
        <div class="section-title">🔥 Hot Deals</div>
        <div class="section-sub">Limited-time offers — grab them before they're gone</div>
      </div>
    </div>
    <div class="deals-grid">
      <!-- Featured deal -->
      <div class="deal-featured">
        <div class="deal-featured-img">
          <svg width="310" height="200" viewBox="0 0 310 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="310" height="200" fill="#0a1628"/>
            <rect width="310" height="200" fill="url(#dealGlow)"/>
            <defs>
              <radialGradient id="dealGlow" cx="50%" cy="50%" r="60%">
                <stop offset="0%" stop-color="#1a56ff" stop-opacity=".3"/>
                <stop offset="100%" stop-color="#0a1628" stop-opacity="1"/>
              </radialGradient>
            </defs>
            <circle cx="155" cy="95" r="55" fill="none" stroke="#1a56ff" stroke-width="1.5" opacity=".4"/>
            <circle cx="155" cy="95" r="38" fill="#0d1f4a" stroke="#3a6fc4" stroke-width="1.5"/>
            <circle cx="155" cy="85" r="12" fill="none" stroke="#00c8ff" stroke-width="2"/>
            <rect x="133" y="103" width="44" height="3" rx="1.5" fill="#1a56ff"/>
            <rect x="133" y="111" width="32" height="3" rx="1.5" fill="#1a56ff"/>
            <rect x="125" y="133" width="60" height="3" rx="1.5" fill="#00c8ff" opacity=".9"/>
            <rect x="40" y="158" width="150" height="18" rx="4" fill="#0d1628" stroke="#1a56ff" stroke-width="1"/>
            <ellipse cx="160" cy="165" rx="100" ry="8" fill="#1a56ff" opacity=".15"/>
          </svg>
        </div>
        <div class="deal-featured-content">
          <div class="deal-tag">Exclusive Deal</div>
          <div class="deal-featured-title">Valorant Ascendant Account<br/>All Agents Unlocked</div>
          <div class="deal-price">
            <span class="deal-price-main">Rp 1.299.000</span>
            <span class="deal-price-old">Rp 1.999.000</span>
          </div>
        </div>
      </div>

      <!-- Side deals -->
      <div class="deal-list">
        <div class="deal-card">
          <div class="deal-card-top">
            <div class="deal-card-icon">⚔️</div>
            <span class="badge-verified">Verified</span>
          </div>
          <div>
            <div class="deal-card-title">Mobile Legends Epic</div>
            <div class="deal-card-sub">Winrate 68%, 15 Exclusive Skins</div>
          </div>
          <div class="deal-card-bottom">
            <span class="deal-card-price">Rp 450.000</span>
            <a href="pages/marketplace.php" class="deal-link">
              Details
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M7 17 17 7M7 7h10v10"/>
              </svg>
            </a>
          </div>
        </div>
        <div class="deal-card">
          <div class="deal-card-top">
            <div class="deal-card-icon">🎮</div>
            <span class="badge-hot">Hot</span>
          </div>
          <div>
            <div class="deal-card-title">PUBG Mobile Conqueror</div>
            <div class="deal-card-sub">Season 12 Frame, Full Sets</div>
          </div>
          <div class="deal-card-bottom">
            <span class="deal-card-price">Rp 895.000</span>
            <a href="pages/marketplace.php" class="deal-link">
              Details
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M7 17 17 7M7 7h10v10"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ TRUST SECTION ════════════════════════════════ -->
<?php
$trusts = [
  ["icon" => "⚡",
  "title" => "Fast Delivery",
  "text" => "Automated delivery systems ensure you get your assets within minutes of payment confirmation."
  ],
  ["icon" => "🔒",
  "title" => "Secure Escrow",
  "text" => "Funds are held safely in escrow until the buyer confirms everything is as described."
  ],
  ["icon" => "🎧",
  "title" => "24/7 Support",
  "text" => "Our dedicated dispute resolution team is always available to help ensure fair transactions."
  ],
];

?>

<div class="trust-section"> 
  <div class="trust-inner">
    <h2>Trust is our Currency</h2>
    <p>We've built a platform where security isn't a feature — it's the foundation.</p>
    <div class="trust-grid">
      <?php foreach ($trusts as $trust): ?>
        <div class="trust-card">
          <div class="trust-icon"><?php echo htmlspecialchars($trust['icon']); ?></div>
          <h3><?php echo htmlspecialchars($trust['title']); ?></h3>
          <p><?php echo htmlspecialchars($trust['text']); ?></p>
        </div>
      <?php endforeach?>
    </div>
  </div>
</div>

<!-- ══ CTA BANNER ═══════════════════════════════════ -->
<div class="cta-section">
  <div class="cta-inner">
    <div class="cta-text">
      <h2>Turn your gaming hours<br/>into real value.</h2>
      <p>Join thousands of sellers who earn daily by trading their high-value gaming assets securely.</p>
    </div>
    <a href="pages/register.php" class="btn-white">Start Selling Today</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>