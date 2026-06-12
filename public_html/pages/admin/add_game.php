<?php
/** @var mysqli $conn */
// pages/admin/add_game.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../../includes/db.php';

$name      = trim($_POST['name']      ?? '');
$genre     = trim($_POST['genre']     ?? '');
$platform  = trim($_POST['platform']  ?? '');
$image_url = trim($_POST['image_url'] ?? '');

if (!$name || !$genre || !$platform || !$image_url) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
    exit;
}

// Check duplicate name
$chk = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id FROM games WHERE name='" . mysqli_real_escape_string($conn, $name) . "'"
));
if ($chk) {
    echo json_encode(['success' => false, 'message' => 'Game dengan nama ini sudah ada.']);
    exit;
}

$stmt = mysqli_prepare($conn,
    "INSERT INTO games (name, genre, platform, image_url) VALUES (?,?,?,?)"
);
mysqli_stmt_bind_param($stmt, 'ssss', $name, $genre, $platform, $image_url);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database.']);
}
