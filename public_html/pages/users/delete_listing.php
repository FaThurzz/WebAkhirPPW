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

if ($listing_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Listing tidak valid.']);
    exit;
}

// Pastikan milik user dan statusnya ready (belum ada order aktif)
$chk = mysqli_prepare($conn,
    "SELECT listing_id, game_id, image_url FROM account_listing
     WHERE listing_id = ? AND user_id = ? AND status = 'ready'");
mysqli_stmt_bind_param($chk, 'ii', $listing_id, $user_id);
mysqli_stmt_execute($chk);
$lst = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
mysqli_stmt_close($chk);

if (!$lst) {
    echo json_encode(['success' => false, 'message' => 'Listing tidak ditemukan atau tidak dapat dihapus.']);
    exit;
}

// Cek tidak ada order aktif
$ordChk = mysqli_prepare($conn,
    "SELECT order_id FROM orders WHERE listing_id = ? AND order_status IN ('pending','paid') LIMIT 1");
mysqli_stmt_bind_param($ordChk, 'i', $listing_id);
mysqli_stmt_execute($ordChk);
$activeOrd = mysqli_fetch_assoc(mysqli_stmt_get_result($ordChk));
mysqli_stmt_close($ordChk);

if ($activeOrd) {
    echo json_encode(['success' => false, 'message' => 'Tidak bisa menghapus listing yang sedang dalam proses order.']);
    exit;
}

// Hapus dari DB
$del = mysqli_prepare($conn, "DELETE FROM account_listing WHERE listing_id = ? AND user_id = ?");
mysqli_stmt_bind_param($del, 'ii', $listing_id, $user_id);
$ok = mysqli_stmt_execute($del);
mysqli_stmt_close($del);

if ($ok) {
    // Hapus file gambar
    if (!empty($lst['image_url'])) {
        $imgPath = __DIR__ . '/../../' . $lst['image_url'];
        if (file_exists($imgPath)) @unlink($imgPath);
    }
    // Kurangi listing_count
    $upd = mysqli_prepare($conn,
        "UPDATE games SET listing_count = GREATEST(listing_count - 1, 0) WHERE id = ?");
    mysqli_stmt_bind_param($upd, 'i', $lst['game_id']);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    echo json_encode(['success' => true, 'message' => 'Listing berhasil dihapus.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus listing.']);
}
