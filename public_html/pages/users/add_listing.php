<?php
/** @var mysqli $conn */
// ── Auth guard ───────────────────────────────────────────────────────────────
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

// ── Koneksi DB ───────────────────────────────────────────────────────────────
include '../../includes/db.php';

// ── Ambil data form ───────────────────────────────────────────────────────────
$user_id            = (int) $_SESSION['user']['ID_User'];
$game_id            = (int) ($_POST['game_id'] ?? 0);
$title              = trim($_POST['title'] ?? '');
$description        = trim($_POST['description'] ?? '');
$price              = (float) ($_POST['price'] ?? 0);
$level              = !empty($_POST['level']) ? (int) $_POST['level'] : null;
$rank               = trim($_POST['rank'] ?? '') ?: null;
$account_login_type = trim($_POST['account_login_type'] ?? '') ?: null;
$id_akun            = trim($_POST['id'] ?? '') ?: null;
$server = trim($_POST['server'] ?? '') ?: null;

// ── Validasi field teks ───────────────────────────────────────────────────────
$errors = [];

if (empty($title))       $errors[] = 'Judul listing wajib diisi.';
if (strlen($title) > 150) $errors[] = 'Judul maksimal 150 karakter.';
if ($game_id <= 0)       $errors[] = 'Pilih game terlebih dahulu.';
if ($price < 1000)       $errors[] = 'Harga minimal Rp 1.000.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Cek game_id valid ─────────────────────────────────────────────────────────
$chk = mysqli_prepare($conn, "SELECT id FROM games WHERE id = ?");
mysqli_stmt_bind_param($chk, 'i', $game_id);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) === 0) {
    echo json_encode(['success' => false, 'message' => 'Game tidak ditemukan.']);
    exit;
}
mysqli_stmt_close($chk);

// ── Proses upload gambar ──────────────────────────────────────────────────────
$image_url = null;

if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'Foto akun wajib diupload.']);
    exit;
}

$file      = $_FILES['image'];
$allowed   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$max_size  = 2 * 1024 * 1024; // 2MB

// Cek error upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi batas server).',
        UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar.',
        UPLOAD_ERR_PARTIAL    => 'Upload tidak lengkap, coba lagi.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file.',
    ];
    $msg = $upload_errors[$file['error']] ?? 'Gagal upload gambar.';
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// Cek ukuran
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Ukuran gambar maksimal 2MB.']);
    exit;
}

// Cek tipe file (gunakan finfo, bukan extension)
$finfo     = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($file['tmp_name']);

if (!array_key_exists($mime_type, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.']);
    exit;
}

// Buat nama file unik
$ext       = $allowed[$mime_type];
$filename  = 'listing_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

// Path folder upload (sesuaikan jika struktur folder berbeda)
$upload_dir = __DIR__ . '/../../assets/uploads/listings/';
$upload_path = $upload_dir . $filename;

// Pastikan folder ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan gambar. Periksa permission folder.']);
    exit;
}

// Path relatif untuk disimpan di DB
$image_url = 'assets/uploads/listings/' . $filename;

// ── Insert ke account_listing ─────────────────────────────────────────────────
$stmt = mysqli_prepare($conn,
    "INSERT INTO account_listing 
        (user_id, game_id, `id`, title, description, server, image_url, price, level, rank, account_login_type, status, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ready', NOW())"
);

// urutan: user_id, game_id, id_akun, title, description, server, image_url, price, level, rank, account_login_type
// type:   i        i        s        s      s             s       s          d      i      s     s
mysqli_stmt_bind_param(
    $stmt,
    'iisssssdiss',
    $user_id,
    $game_id,
    $id_akun,
    $title,
    $description,
    $server,
    $image_url,
    $price,
    $level,
    $rank,
    $account_login_type
);

if (mysqli_stmt_execute($stmt)) {
    $new_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Update listing_count di tabel games
    $upd = mysqli_prepare($conn, "UPDATE games SET listing_count = listing_count + 1 WHERE id = ?");
    mysqli_stmt_bind_param($upd, 'i', $game_id);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    echo json_encode([
        'success'    => true,
        'message'    => 'Listing berhasil ditambahkan!',
        'listing_id' => $new_id,
        'image_url'  => $image_url,
    ]);
} else {
    mysqli_stmt_close($stmt);
    // Hapus file yang sudah terupload kalau insert gagal
    if (file_exists($upload_path)) unlink($upload_path);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan listing ke database.',
    ]);
}