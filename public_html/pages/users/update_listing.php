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

function deleteListingImageIfLocal($imageUrl) {
    if (empty($imageUrl)) return;

    $uploadRoot = realpath(__DIR__ . '/../../assets/uploads/listings');
    $imagePath = realpath(__DIR__ . '/../../' . ltrim($imageUrl, '/\\'));

    if ($uploadRoot && $imagePath && str_starts_with($imagePath, $uploadRoot) && is_file($imagePath)) {
        unlink($imagePath);
    }
}

function uploadListingImage($user_id) {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'image_url' => null, 'upload_path' => null];
    }

    $file = $_FILES['image'];
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $max_size = 2 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi batas server).',
            UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar.',
            UPLOAD_ERR_PARTIAL    => 'Upload tidak lengkap, coba lagi.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file.',
        ];

        return [
            'success' => false,
            'message' => $upload_errors[$file['error']] ?? 'Gagal upload gambar.',
        ];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Ukuran gambar maksimal 2MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mime_type, $allowed)) {
        return ['success' => false, 'message' => 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.'];
    }

    $ext = $allowed[$mime_type];
    $filename = 'listing_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $upload_dir = __DIR__ . '/../../assets/uploads/listings/';
    $upload_path = $upload_dir . $filename;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => false, 'message' => 'Gagal menyimpan gambar. Periksa permission folder.'];
    }

    return [
        'success' => true,
        'image_url' => 'assets/uploads/listings/' . $filename,
        'upload_path' => $upload_path,
    ];
}

$user_id = (int) $_SESSION['user']['ID_User'];
$listing_id = (int) ($_POST['listing_id'] ?? 0);
$game_id = (int) ($_POST['game_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = (float) ($_POST['price'] ?? 0);
$levelRaw = trim($_POST['level'] ?? '');
$level = $levelRaw !== '' ? (int) $levelRaw : null;
$rank = trim($_POST['rank'] ?? '') ?: null;
$account_login_type = trim($_POST['account_login_type'] ?? '') ?: null;
$id_akun = trim($_POST['id'] ?? '') ?: null;
$server = trim($_POST['server'] ?? '') ?: null;

$errors = [];

if ($listing_id <= 0) $errors[] = 'Listing tidak valid.';
if ($title === '') $errors[] = 'Judul listing wajib diisi.';
if (strlen($title) > 150) $errors[] = 'Judul maksimal 150 karakter.';
if ($game_id <= 0) $errors[] = 'Pilih game terlebih dahulu.';
if ($price < 1000) $errors[] = 'Harga minimal Rp 1.000.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$oldStmt = mysqli_prepare(
    $conn,
    "SELECT listing_id, game_id, image_url
     FROM account_listing
     WHERE listing_id = ? AND user_id = ? AND status = 'ready'
     LIMIT 1"
);
mysqli_stmt_bind_param($oldStmt, 'ii', $listing_id, $user_id);
mysqli_stmt_execute($oldStmt);
$oldListing = mysqli_fetch_assoc(mysqli_stmt_get_result($oldStmt));
mysqli_stmt_close($oldStmt);

if (!$oldListing) {
    echo json_encode(['success' => false, 'message' => 'Listing tidak ditemukan atau tidak bisa diedit.']);
    exit;
}

$chk = mysqli_prepare($conn, "SELECT id FROM games WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 'i', $game_id);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) === 0) {
    mysqli_stmt_close($chk);
    echo json_encode(['success' => false, 'message' => 'Game tidak ditemukan.']);
    exit;
}
mysqli_stmt_close($chk);

$upload = uploadListingImage($user_id);
if (!$upload['success']) {
    echo json_encode(['success' => false, 'message' => $upload['message']]);
    exit;
}

$image_url = $upload['image_url'] ?: $oldListing['image_url'];

$stmt = mysqli_prepare(
    $conn,
    "UPDATE account_listing
     SET game_id = ?, `id` = ?, title = ?, description = ?, server = ?, image_url = ?,
         price = ?, level = ?, rank = ?, account_login_type = ?
     WHERE listing_id = ? AND user_id = ? AND status = 'ready'"
);

mysqli_stmt_bind_param(
    $stmt,
    'isssssdissii',
    $game_id,
    $id_akun,
    $title,
    $description,
    $server,
    $image_url,
    $price,
    $level,
    $rank,
    $account_login_type,
    $listing_id,
    $user_id
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    $old_game_id = (int) $oldListing['game_id'];
    if ($old_game_id !== $game_id) {
        $dec = mysqli_prepare($conn, "UPDATE games SET listing_count = GREATEST(listing_count - 1, 0) WHERE id = ?");
        mysqli_stmt_bind_param($dec, 'i', $old_game_id);
        mysqli_stmt_execute($dec);
        mysqli_stmt_close($dec);

        $inc = mysqli_prepare($conn, "UPDATE games SET listing_count = listing_count + 1 WHERE id = ?");
        mysqli_stmt_bind_param($inc, 'i', $game_id);
        mysqli_stmt_execute($inc);
        mysqli_stmt_close($inc);
    }

    if (!empty($upload['image_url']) && $upload['image_url'] !== $oldListing['image_url']) {
        deleteListingImageIfLocal($oldListing['image_url']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Listing berhasil diperbarui.',
        'image_url' => $image_url,
    ]);
} else {
    mysqli_stmt_close($stmt);

    if (!empty($upload['upload_path']) && is_file($upload['upload_path'])) {
        unlink($upload['upload_path']);
    }

    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui listing.']);
}
