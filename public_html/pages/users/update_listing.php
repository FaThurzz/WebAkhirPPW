<?php
/** @var mysqli $conn */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

include '../../includes/db.php';

$user_id    = (int) $_SESSION['user']['ID_User'];
$listing_id = (int) ($_POST['listing_id'] ?? 0);
$title      = trim($_POST['title'] ?? '');
$game_id    = (int) ($_POST['game_id'] ?? 0);
$price      = (float) ($_POST['price'] ?? 0);
$rank       = trim($_POST['rank'] ?? '') ?: null;
$level      = !empty($_POST['level']) ? (int)$_POST['level'] : null;
$server     = trim($_POST['server'] ?? '') ?: null;
$account_login_type = trim($_POST['account_login_type'] ?? '') ?: null;
$id_akun    = trim($_POST['id'] ?? '') ?: null;
$description = trim($_POST['description'] ?? '');
$account_email    = trim($_POST['account_email'] ?? '');
$account_password = trim($_POST['account_password'] ?? '');
$cred_notes       = trim($_POST['cred_notes'] ?? '') ?: null;

// Validasi
$errors = [];
if ($listing_id <= 0)     $errors[] = 'Listing tidak valid.';
if (empty($title))        $errors[] = 'Judul listing wajib diisi.';
if (strlen($title) > 150) $errors[] = 'Judul maksimal 150 karakter.';
if ($game_id <= 0)        $errors[] = 'Pilih game terlebih dahulu.';
if ($price < 1000)        $errors[] = 'Harga minimal Rp 1.000.';
if (empty($account_email))    $errors[] = 'Email akun wajib diisi.';
if (empty($account_password)) $errors[] = 'Password akun wajib diisi.';

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Pastikan listing ini milik user dan masih ready
$chk = mysqli_prepare($conn,
    "SELECT listing_id, image_url FROM account_listing WHERE listing_id = ? AND user_id = ? AND status = 'ready'");
mysqli_stmt_bind_param($chk, 'ii', $listing_id, $user_id);
mysqli_stmt_execute($chk);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
mysqli_stmt_close($chk);

if (!$existing) {
    echo json_encode(['success' => false, 'message' => 'Listing tidak ditemukan atau tidak bisa diedit.']);
    exit;
}

// Handle gambar baru (opsional)
$image_url = $existing['image_url'];

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file    = $_FILES['image'];
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $maxSize = 2 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Gagal upload gambar.']);
        exit;
    }
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Ukuran gambar maksimal 2MB.']);
        exit;
    }
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!array_key_exists($mimeType, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Format gambar tidak didukung.']);
        exit;
    }

    $ext         = $allowed[$mimeType];
    $filename    = 'listing_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $upload_dir  = __DIR__ . '/../../assets/uploads/listings/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $upload_path = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan gambar.']);
        exit;
    }

    // Hapus gambar lama
    if (!empty($existing['image_url'])) {
        $old = __DIR__ . '/../../' . $existing['image_url'];
        if (file_exists($old)) @unlink($old);
    }

    $image_url = 'assets/uploads/listings/' . $filename;
}

// Update DB
$stmt = mysqli_prepare($conn,
    "UPDATE account_listing
     SET game_id=?, `id`=?, title=?, description=?, server=?, image_url=?,
         price=?, level=?, rank=?, account_login_type=?, updated_at=NOW()
     WHERE listing_id=? AND user_id=?");
mysqli_stmt_bind_param($stmt, 'isssssdissii',
    $game_id, $id_akun, $title, $description, $server, $image_url,
    $price, $level, $rank, $account_login_type,
    $listing_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    // ── Upsert kredential akun ──────────────────────────────────────────
    $credStmt = mysqli_prepare($conn,
        "INSERT INTO account_credentials (listing_id, account_email, account_password, notes)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           account_email    = VALUES(account_email),
           account_password = VALUES(account_password),
           notes            = VALUES(notes)"
    );
    mysqli_stmt_bind_param($credStmt, 'isss', $listing_id, $account_email, $account_password, $cred_notes);
    mysqli_stmt_execute($credStmt);
    mysqli_stmt_close($credStmt);

    echo json_encode(['success' => true, 'message' => 'Listing berhasil diperbarui!']);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui listing.']);
}