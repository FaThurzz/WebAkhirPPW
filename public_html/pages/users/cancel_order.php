<?php
/** @var mysqli $conn */
// pages/users/cancel_order.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// Harus login
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Kamu harus login terlebih dahulu.']);
    exit;
}

require_once '../../includes/db.php';

$userId  = (int) $_SESSION['user']['ID_User'];
$orderId = (int) ($_POST['order_id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'ID order tidak valid.']);
    exit;
}

// Pastikan order ini milik user yang sedang login dan masih berstatus pending
$stmt = mysqli_prepare($conn,
    "SELECT order_id, order_status, listing_id FROM orders WHERE order_id = ? AND user_id = ?"
);
mysqli_stmt_bind_param($stmt, 'ii', $orderId, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order  = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order tidak ditemukan atau bukan milikmu.']);
    exit;
}

if ($order['order_status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Hanya order dengan status Menunggu yang bisa dibatalkan.']);
    exit;
}

// Batalkan order
$stmt2 = mysqli_prepare($conn,
    "UPDATE orders SET order_status = 'cancelled' WHERE order_id = ?"
);
mysqli_stmt_bind_param($stmt2, 'i', $orderId);
$ok = mysqli_stmt_execute($stmt2);
mysqli_stmt_close($stmt2);

if ($ok) {
    // Batalkan payment jika ada
    mysqli_query($conn,
        "UPDATE payment SET payment_status = 'cancelled' WHERE order_id = $orderId"
    );

    echo json_encode(['success' => true, 'message' => 'Order berhasil dibatalkan.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal membatalkan order. Coba lagi.']);
}

mysqli_close($conn);
