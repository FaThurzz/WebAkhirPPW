<?php
/** @var mysqli $conn */
// pages/admin/action_game.php
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
        // Check if game has active listings
        $count = (int) mysqli_fetch_assoc(
            mysqli_query($conn, "SELECT COUNT(*) AS c FROM account_listing WHERE game_id=$id AND status='ready'")
        )['c'];

        if ($count > 0) {
            echo json_encode([
                'success' => false,
                'message' => "Tidak bisa menghapus game yang masih memiliki $count listing aktif."
            ]);
            exit;
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM games WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
}
