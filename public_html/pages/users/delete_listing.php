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

$user_id = (int) $_SESSION['user']['ID_User'];
$listing_id = (int) ($_POST['listing_id'] ?? 0);

if ($listing_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Listing tidak valid.']);
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT listing_id, game_id, image_url
     FROM account_listing
     WHERE listing_id = ? AND user_id = ? AND status = 'ready'
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'ii', $listing_id, $user_id);
mysqli_stmt_execute($stmt);
$listing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$listing) {
    echo json_encode(['success' => false, 'message' => 'Listing tidak ditemukan atau tidak bisa dihapus.']);
    exit;
}

$delete = mysqli_prepare(
    $conn,
    "DELETE FROM account_listing WHERE listing_id = ? AND user_id = ? AND status = 'ready'"
);
mysqli_stmt_bind_param($delete, 'ii', $listing_id, $user_id);
$success = mysqli_stmt_execute($delete);
$affected = mysqli_stmt_affected_rows($delete);
mysqli_stmt_close($delete);

if (!$success || $affected < 1) {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus listing.']);
    exit;
}

$game_id = (int) $listing['game_id'];
$update = mysqli_prepare(
    $conn,
    "UPDATE games SET listing_count = GREATEST(listing_count - 1, 0) WHERE id = ?"
);
mysqli_stmt_bind_param($update, 'i', $game_id);
mysqli_stmt_execute($update);
mysqli_stmt_close($update);

if (!empty($listing['image_url'])) {
    $uploadRoot = realpath(__DIR__ . '/../../assets/uploads/listings');
    $imagePath = realpath(__DIR__ . '/../../' . ltrim($listing['image_url'], '/\\'));

    if ($uploadRoot && $imagePath && str_starts_with($imagePath, $uploadRoot) && is_file($imagePath)) {
        unlink($imagePath);
    }
}

echo json_encode(['success' => true, 'message' => 'Listing berhasil dihapus.']);
