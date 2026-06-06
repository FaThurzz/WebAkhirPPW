<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

include '../includes/db.php';
/** @var mysqli $conn */

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']       ?? '';
    $password2 = $_POST['password2']      ?? '';

    // Validasi
    if (empty($username)) {
        $errors['username'] = 'Username tidak boleh kosong.';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username minimal 3 karakter.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username hanya boleh huruf, angka, dan underscore.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email tidak boleh kosong.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password tidak boleh kosong.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }

    if (empty($password2)) {
        $errors['password2'] = 'Konfirmasi password tidak boleh kosong.';
    } elseif ($password !== $password2) {
        $errors['password2'] = 'Password tidak cocok.';
    }

    // Cek duplikat username / email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Cek mana yang duplikat
            $stmtU = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmtU->bind_param('s', $username);
            $stmtU->execute();
            $stmtU->store_result();
            if ($stmtU->num_rows > 0) $errors['username'] = 'Username sudah digunakan.';
            $stmtU->close();

            $stmtE = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmtE->bind_param('s', $email);
            $stmtE->execute();
            $stmtE->store_result();
            if ($stmtE->num_rows > 0) $errors['email'] = 'Email sudah terdaftar.';
            $stmtE->close();
        }
        $stmt->close();
    }

    // Insert jika tidak ada error
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role   = 'user';
        $stmt   = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $username, $email, $hashed, $role);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors['general'] = 'Terjadi kesalahan server. Silakan coba lagi.';
        }
        $stmt->close();
    }
}

$is_register = true;
$page_title  = 'Daftar — ThurzShop';
$active_page = 'register';
include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/page/css/login.css">
<link rel="stylesheet" href="../assets/page/css/register.css">

<div class="login-wrap">

  <!-- ── Left Panel ── -->
  <div class="login-panel-left">
    <div class="login-brand-inner">
      <a href="../index.php" class="nav-logo" style="font-size:26px;">Thurz<span>Shop</span></a>
      <p class="login-tagline">Bergabung dengan 50.000+ gamer<br/>yang sudah trading di ThurzShop.</p>

      <div class="login-features">
        <div class="login-feature-item">
          <div class="login-feature-icon">🎮</div>
          <div>
            <div class="login-feature-title">Jual & Beli Akun</div>
            <div class="login-feature-sub">Ribuan listing game premium tersedia</div>
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
          <div class="login-feature-icon">⭐</div>
          <div>
            <div class="login-feature-title">Rating & Review</div>
            <div class="login-feature-sub">Sistem reputasi seller terpercaya</div>
          </div>
        </div>
        <div class="login-feature-item">
          <div class="login-feature-icon">⚡</div>
          <div>
            <div class="login-feature-title">Gratis Daftar</div>
            <div class="login-feature-sub">Tidak ada biaya pendaftaran</div>
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

  <!-- ── Right Panel ── -->
  <div class="login-panel-right">
    <div class="login-form-card reg-form-card">

      <?php if ($success): ?>
      <!-- Success state -->
      <div class="reg-success">
        <div class="reg-success-icon">
          <svg width="32" height="32" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M20 6L9 17l-5-5"/>
          </svg>
        </div>
        <h2>Akun berhasil dibuat!</h2>
        <p>Selamat datang di ThurzShop. Kamu sudah bisa login sekarang.</p>
        <a href="login.php" class="btn btn-primary login-submit-btn" style="margin-top: 24px; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px;">
          Masuk Sekarang
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </a>
      </div>

      <?php else: ?>

      <div class="login-form-header">
        <h2>Buat akun baru</h2>
        <p>Daftar gratis dan mulai trading sekarang</p>
      </div>

      <?php if (!empty($errors['general'])): ?>
      <div class="login-alert login-alert-error">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?php echo htmlspecialchars($errors['general']); ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="register.php" class="login-form" id="registerForm" novalidate>

        <!-- Username -->
        <div class="form-group <?php echo isset($errors['username']) ? 'has-error' : ''; ?>" id="group-username">
          <label class="form-label" for="username">Username</label>
          <div class="form-input-wrap">
            <svg class="form-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" id="username" name="username" class="form-input"
              placeholder="Contoh: Fathur"
              value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
              autocomplete="username" required />
            <span class="form-input-check" id="check-username"></span>
          </div>
          <span class="form-error-msg" id="err-username">
            <?php echo htmlspecialchars($errors['username'] ?? ''); ?>
          </span>
        </div>

        <!-- Email -->
        <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>" id="group-email">
          <label class="form-label" for="email">Email</label>
          <div class="form-input-wrap">
            <svg class="form-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            <input type="email" id="email" name="email" class="form-input"
              placeholder="email@gmail.com"
              value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
              autocomplete="email" required />
            <span class="form-input-check" id="check-email"></span>
          </div>
          <span class="form-error-msg" id="err-email">
            <?php echo htmlspecialchars($errors['email'] ?? ''); ?>
          </span>
        </div>

        <!-- Password -->
        <div class="form-group <?php echo isset($errors['password']) ? 'has-error' : ''; ?>" id="group-password">
          <label class="form-label" for="password">Password</label>
          <div class="form-input-wrap">
            <svg class="form-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" id="password" name="password" class="form-input"
              placeholder="Minimal 6 karakter"
              autocomplete="new-password" required />
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
          <span class="form-error-msg" id="err-password">
            <?php echo htmlspecialchars($errors['password'] ?? ''); ?>
          </span>
          <!-- Password strength bar -->
          <div class="pw-strength" id="pwStrength" style="display:none;">
            <div class="pw-strength-bar">
              <div class="pw-strength-fill" id="pwStrengthFill"></div>
            </div>
            <span class="pw-strength-label" id="pwStrengthLabel"></span>
          </div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="form-group <?php echo isset($errors['password2']) ? 'has-error' : ''; ?>" id="group-password2">
          <label class="form-label" for="password2">Konfirmasi Password</label>
          <div class="form-input-wrap">
            <svg class="form-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" id="password2" name="password2" class="form-input"
              placeholder="Ulangi password kamu"
              autocomplete="new-password" required />
            <button type="button" class="toggle-pw" id="togglePw2" aria-label="Tampilkan konfirmasi password">
              <svg id="eyeOpen2" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg id="eyeOff2" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          <span class="form-error-msg" id="err-password2">
            <?php echo htmlspecialchars($errors['password2'] ?? ''); ?>
          </span>
        </div>

        <!-- Terms -->
        <div class="form-group" id="group-terms">
          <label class="login-remember reg-terms">
            <input type="checkbox" name="terms" id="terms" required />
            <span class="login-remember-box"></span>
            <span>Saya menyetujui <a href="#" class="reg-terms-link">Syarat & Ketentuan</a> serta <a href="#" class="reg-terms-link">Kebijakan Privasi</a> ThurzShop</span>
          </label>
          <span class="form-error-msg" id="err-terms"></span>
        </div>

        <button type="submit" class="btn btn-primary login-submit-btn" id="submitBtn">
          <span id="btnText">Buat Akun</span>
          <svg id="btnArrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
          <svg id="btnSpinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:none;" class="spin">
            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
          </svg>
        </button>

      </form>

      <div class="login-divider"><span>sudah punya akun?</span></div>

      <div class="login-register-prompt">
        <a href="login.php" class="login-register-link">← Masuk ke akun yang ada</a>
      </div>

      <?php endif; ?>
    </div>
  </div>

</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/page/js/register.js"></script>
</body>