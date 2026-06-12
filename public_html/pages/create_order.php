<?php
/** @var mysqli $conn */
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra));
    exit;
}

if (!isset($_SESSION['user'])) {
    respond(false, 'Silakan login terlebih dahulu.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method tidak diizinkan.');
}

include '../includes/db.php';

$buyer_id = (int) $_SESSION['user']['ID_User'];
$listing_id = (int) ($_POST['listing_id'] ?? 0);
$payment_method = trim($_POST['payment_method'] ?? '');
$allowed_methods = ['Transfer Bank', 'E-Wallet', 'QRIS'];

if ($listing_id <= 0) {
    respond(false, 'Listing tidak valid.');
}

if (!in_array($payment_method, $allowed_methods, true)) {
    respond(false, 'Pilih metode pembayaran yang valid.');
}

mysqli_begin_transaction($conn);

$stmt = mysqli_prepare(
    $conn,
    "SELECT listing_id, user_id, title, price, status
     FROM account_listing
     WHERE listing_id = ?
     LIMIT 1
     FOR UPDATE"
);

if (!$stmt) {
    mysqli_rollback($conn);
    respond(false, 'Gagal memproses order.');
}

mysqli_stmt_bind_param($stmt, 'i', $listing_id);
mysqli_stmt_execute($stmt);
$listing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$listing) {
    mysqli_rollback($conn);
    respond(false, 'Listing tidak ditemukan.');
}

$seller_id = (int) $listing['user_id'];

if ($seller_id === $buyer_id) {
    mysqli_rollback($conn);
    respond(false, 'Kamu tidak bisa membeli listing milik sendiri.');
}

if ($listing['status'] !== 'ready') {
    mysqli_rollback($conn);
    respond(false, 'Listing ini sudah tidak tersedia.');
}

$existingBuyer = mysqli_prepare(
    $conn,
    "SELECT order_id
     FROM orders
     WHERE user_id = ? AND listing_id = ? AND order_status <> 'cancelled'
     ORDER BY order_id DESC
     LIMIT 1"
);
if (!$existingBuyer) {
    mysqli_rollback($conn);
    respond(false, 'Gagal memeriksa order.');
}
mysqli_stmt_bind_param($existingBuyer, 'ii', $buyer_id, $listing_id);
mysqli_stmt_execute($existingBuyer);
$buyerOrder = mysqli_fetch_assoc(mysqli_stmt_get_result($existingBuyer));
mysqli_stmt_close($existingBuyer);

if ($buyerOrder) {
    mysqli_commit($conn);
    respond(true, 'Order untuk listing ini sudah ada.', [
        'order_id' => (int) $buyerOrder['order_id'],
        'redirect_url' => 'users/dashboard.php',
    ]);
}

$activeOrder = mysqli_prepare(
    $conn,
    "SELECT order_id
     FROM orders
     WHERE listing_id = ? AND order_status IN ('pending', 'paid', 'confirmed')
     LIMIT 1"
);
if (!$activeOrder) {
    mysqli_rollback($conn);
    respond(false, 'Gagal memeriksa ketersediaan listing.');
}
mysqli_stmt_bind_param($activeOrder, 'i', $listing_id);
mysqli_stmt_execute($activeOrder);
$active = mysqli_fetch_assoc(mysqli_stmt_get_result($activeOrder));
mysqli_stmt_close($activeOrder);

if ($active) {
    mysqli_rollback($conn);
    respond(false, 'Listing ini sedang diproses pembeli lain.');
}

$total_price = (float) $listing['price'];
$orderStmt = mysqli_prepare(
    $conn,
    "INSERT INTO orders (user_id, seller_id, listing_id, total_price, order_status, created_at)
     VALUES (?, ?, ?, ?, 'pending', NOW())"
);

if (!$orderStmt) {
    mysqli_rollback($conn);
    respond(false, 'Gagal membuat order.');
}

mysqli_stmt_bind_param($orderStmt, 'iiid', $buyer_id, $seller_id, $listing_id, $total_price);
$orderOk = mysqli_stmt_execute($orderStmt);
$order_id = mysqli_insert_id($conn);
mysqli_stmt_close($orderStmt);

if (!$orderOk || $order_id <= 0) {
    mysqli_rollback($conn);
    respond(false, 'Gagal membuat order.');
}

$paymentStmt = mysqli_prepare(
    $conn,
    "INSERT INTO payment (order_id, amount, payment_method, payment_status, updated_at)
     VALUES (?, ?, ?, 'pending', NOW())"
);

if (!$paymentStmt) {
    mysqli_rollback($conn);
    respond(false, 'Gagal membuat data pembayaran.');
}

mysqli_stmt_bind_param($paymentStmt, 'ids', $order_id, $total_price, $payment_method);
$paymentOk = mysqli_stmt_execute($paymentStmt);
mysqli_stmt_close($paymentStmt);

if (!$paymentOk) {
    mysqli_rollback($conn);
    respond(false, 'Gagal membuat data pembayaran.');
}

mysqli_commit($conn);

respond(true, 'Order berhasil dibuat. Lanjutkan pembayaran dari dashboard.', [
    'order_id' => (int) $order_id,
    'redirect_url' => 'users/dashboard.php',
]);
