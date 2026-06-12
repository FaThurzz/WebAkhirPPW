<?php
/** @var mysqli $conn */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

function respond(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

if (!isset($_SESSION['user'])) respond(false, 'Silakan login terlebih dahulu.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(false, 'Method tidak diizinkan.');

include '../../includes/db.php';

$buyer_id = (int) $_SESSION['user']['ID_User'];
$order_id = (int) ($_POST['order_id'] ?? 0);

if ($order_id <= 0) respond(false, 'Order tidak valid.');

// Pastikan order ini milik buyer dan masih pending
$chk = mysqli_prepare($conn,
    "SELECT o.order_id FROM orders o
     LEFT JOIN payment p ON o.order_id = p.order_id
     WHERE o.order_id = ? AND o.user_id = ? AND o.order_status = 'pending'
     LIMIT 1");
mysqli_stmt_bind_param($chk, 'ii', $order_id, $buyer_id);
mysqli_stmt_execute($chk);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
mysqli_stmt_close($chk);

if (!$row) respond(false, 'Order tidak ditemukan atau sudah tidak bisa diperbarui.');

// Validasi file
if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE) {
    respond(false, 'Pilih file bukti pembayaran terlebih dahulu.');
}

$file     = $_FILES['payment_proof'];
$allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$max_size = 2 * 1024 * 1024;

if ($file['error'] !== UPLOAD_ERR_OK) respond(false, 'Gagal mengupload file.');
if ($file['size'] > $max_size) respond(false, 'Ukuran file maksimal 2MB.');

$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
if (!array_key_exists($mimeType, $allowed)) respond(false, 'Format tidak didukung. Gunakan JPG, PNG, atau WEBP.');

$ext        = $allowed[$mimeType];
$filename   = 'proof_' . $buyer_id . '_' . $order_id . '_' . time() . '.' . $ext;
$upload_dir = __DIR__ . '/../../assets/uploads/payments/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$upload_path = $upload_dir . $filename;
if (!move_uploaded_file($file['tmp_name'], $upload_path)) respond(false, 'Gagal menyimpan file.');

$proof_path = 'assets/uploads/payments/' . $filename;

// Update payment: set proof dan status paid
$stmt = mysqli_prepare($conn,
    "UPDATE payment SET payment_proof=?, payment_status='paid', paid_at=NOW() WHERE order_id=?");
mysqli_stmt_bind_param($stmt, 'si', $proof_path, $order_id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ok) {
    @unlink($upload_path);
    respond(false, 'Gagal menyimpan bukti pembayaran.');
}

// Update order status ke paid
$o = mysqli_prepare($conn, "UPDATE orders SET order_status='paid' WHERE order_id=?");
mysqli_stmt_bind_param($o, 'i', $order_id);
mysqli_stmt_execute($o);
mysqli_stmt_close($o);

respond(true, 'Bukti pembayaran berhasil dikirim. Tunggu konfirmasi admin.');
