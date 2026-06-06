<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

include '../includes/db.php';
/** @var mysqli $conn */
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'] ?? 'user',
            ];
            header(($_SESSION['user']['role'] === 'admin')
                ? 'Location: admin/dashboard.php'
                : 'Location: ../index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}

$page_title  = "Login ThurzShop";
$active_page = "login";
include '../includes/header.php';
?>
<head>
    <link rel="stylesheet" href="../assets/page/css/login.css">
</head>
<body>
<div class="login-wrap">

  <!-- Left: branding -->
  <div class="login-panel-left">
    <div class="login-brand-inner">
      <a href="../index.php" class="nav-logo" style="font-size:26px;">Thurz<span>Shop</span></a>
      <p class="login-tagline">Marketplace akun game<br/>paling terpercaya di Indonesia.</p>

      <div class="login-features">
        <div class="login-feature-item">
          <div class="login-feature-icon">⚡</div>
          <div>
            <div class="login-feature-title">Fast Delivery</div>
            <div class="login-feature-sub">Proses instan setelah pembayaran</div>
          </div>
        </div>
        <div class="login-feature-item">
          <div class="login-feature-icon">🔒</div>
          <div>
            <div class="login-feature-title">Escrow System</div>
            <div class="login-feature-sub">Dana aman hingga transaksi selesai</div>
          </div>
        </div>
        <div class="login-feature-item">
          <div class="login-feature-icon">🎧</div>
          <div>
            <div class="login-feature-title">24/7 Support</div>
            <div class="login-feature-sub">Tim siap membantu kapanpun</div>
          </div>
        </div>
      </div>

      <div class="login-stats">
        <div class="login-stat">
          <span class="login-stat-num">50K+</span>
          <span class="login-stat-label">Gamers</span>
        </div>
        <div class="login-stat-divider"></div>
        <div class="login-stat">
          <span class="login-stat-num">10K+</span>
          <span class="login-stat-label">Transaksi</span>
        </div>
        <div class="login-stat-divider"></div>
        <div class="login-stat">
          <span class="login-stat-num">4.9★</span>
          <span class="login-stat-label">Rating</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: form -->
  <div class="login-panel-right">
    <div class="login-form-card">

      <div class="login-form-header">
        <h2>Selamat datang kembali</h2>
        <p>Masuk ke akun ThurzShop kamu</p>
      </div>

      <?php if ($error): ?>
      <div class="login-alert login-alert-error">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?php echo htmlspecialchars($error); ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="login.php" class="login-form" id="loginForm" novalidate>

        <div class="form-group" id="group-username">
          <label class="form-label" for="username">Username</label>
          <div class="form-input-wrap">
            <svg class="form-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" id="username" name="username" class="form-input"
              placeholder="Masukkan username"
              value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
              autocomplete="username" required />
          </div>
          <span class="form-error-msg" id="err-username"></span>
        </div>

        <div class="form-group" id="group-password">
          <label class="form-label" for="password">Password</label>
          <div class="form-input-wrap">
            <svg class="form-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" id="password" name="password" class="form-input"
              placeholder="Masukkan password"
              autocomplete="current-password" required />
            <button type="button" class="toggle-pw" id="togglePw" aria-label="Tampilkan password">
              <svg id="eyeOpen" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg id="eyeOff" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          <span class="form-error-msg" id="err-password"></span>
        </div>

        <div class="login-row">
          <label class="login-remember">
            <input type="checkbox" name="remember" id="remember" />
            <span class="login-remember-box"></span>
            Ingat saya
          </label>
          <a href="#" class="login-forgot">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-primary login-submit-btn" id="submitBtn">
          <span id="btnText">Masuk</span>
          <svg id="btnArrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
          <svg id="btnSpinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:none;" class="spin">
            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
          </svg>
        </button>

      </form>

      <div class="login-divider"><span>atau</span></div>

      <div class="login-register-prompt">
        Belum punya akun?
        <a href="register.php" class="login-register-link">Daftar sekarang →</a>
      </div>

    </div>
  </div>

</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/page/js/login.js"></script>
</body>
