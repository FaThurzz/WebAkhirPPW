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

$user_id      = (int) $_SESSION['user']['ID_User'];
$full_name    = trim($_POST['full_name'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$new_password = $_POST['new_password'] ?? '';

$errors = [];
if (strlen($full_name) > 100) $errors[] = 'Nama lengkap maksimal 100 karakter.';
if ($phone_number && !preg_match('/^[0-9+\-\s]{6,20}$/', $phone_number)) {
    $errors[] = 'Format nomor HP tidak valid.';
}
if ($new_password !== '' && strlen($new_password) < 6) {
    $errors[] = 'Password baru minimal 6 karakter.';
}

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

if ($new_password !== '') {
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET full_name=?, phone_number=?, password=? WHERE ID_User=?");
    mysqli_stmt_bind_param($stmt, 'sssi', $full_name, $phone_number, $hashed, $user_id);
} else {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET full_name=?, phone_number=? WHERE ID_User=?");
    mysqli_stmt_bind_param($stmt, 'ssi', $full_name, $phone_number, $user_id);
}

if (mysqli_stmt_execute($stmt)) {
    // Update session
    $_SESSION['user']['full_name']    = $full_name;
    $_SESSION['user']['phone_number'] = $phone_number;
    echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil.']);
}
mysqli_stmt_close($stmt);
