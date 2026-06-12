<?php
/** @var mysqli $conn */
// pages/admin/action_order.php
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
    case 'confirm':
        // Update payment status → confirmed + paid_at
        $stmt = mysqli_prepare($conn,
            "UPDATE payment SET payment_status='confirmed', paid_at=NOW() WHERE order_id=?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        // Update order status → confirmed
        $stmt2 = mysqli_prepare($conn,
            "UPDATE orders SET order_status='confirmed' WHERE order_id=?"
        );
        mysqli_stmt_bind_param($stmt2, 'i', $id);
        mysqli_stmt_execute($stmt2);

        // Mark listing as sold
        $listing = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT listing_id FROM orders WHERE order_id=$id"
        ));
        if ($listing) {
            $lid = (int) $listing['listing_id'];
            mysqli_query($conn, "UPDATE account_listing SET status='sold' WHERE listing_id=$lid");
        }

        echo json_encode(['success' => true]);
        break;

    case 'cancel':
        $stmt = mysqli_prepare($conn,
            "UPDATE orders SET order_status='cancelled' WHERE order_id=?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        // Also update payment if exists
        mysqli_query($conn,
            "UPDATE payment SET payment_status='cancelled' WHERE order_id=$id"
        );

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
}
