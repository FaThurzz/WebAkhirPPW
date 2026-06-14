<?php
/** @var mysqli $conn */
// pages/admin/action_user.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// Auth guard
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

// Prevent admin from self-modifying
if ($id === (int) $_SESSION['user']['ID_User'] && in_array($action, ['ban', 'delete'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak bisa melakukan aksi pada akun sendiri.']);
    exit;
}

// Prevent banning or deleting other admins
if (in_array($action, ['ban', 'delete'])) {
    $target = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE ID_User=$id"));
    if ($target && $target['role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Tidak bisa melakukan aksi pada akun admin lain.']);
        exit;
    }
}

switch ($action) {
    case 'ban':
        $stmt = mysqli_prepare($conn, "UPDATE users SET status='banned' WHERE ID_User=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true]);
        break;

    case 'unban':
        $stmt = mysqli_prepare($conn, "UPDATE users SET status='active' WHERE ID_User=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE ID_User=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
}