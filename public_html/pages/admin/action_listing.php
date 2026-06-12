<?php
/** @var mysqli $conn */
// pages/admin/action_listing.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../../includes/db.php';

$action = $_POST['action'] ?? '';
$id     = (int) ($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
    exit;
}

switch ($action) {
    case 'delete':
        // Delete related orders & payments first (FK safety)
        $orders = mysqli_fetch_all(
            mysqli_query($conn, "SELECT order_id FROM orders WHERE listing_id=$id"),
            MYSQLI_ASSOC
        );
        foreach ($orders as $o) {
            $oid = (int) $o['order_id'];
            mysqli_query($conn, "DELETE FROM payment WHERE order_id=$oid");
            mysqli_query($conn, "DELETE FROM orders WHERE order_id=$oid");
        }

        // Delete the listing image file if exists
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image_url FROM account_listing WHERE listing_id=$id"));
        if ($row && $row['image_url']) {
            $file = __DIR__ . '/../../' . ltrim($row['image_url'], '/');
            if (file_exists($file)) @unlink($file);
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM account_listing WHERE listing_id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
}
