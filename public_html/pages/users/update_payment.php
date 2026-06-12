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

function deletePaymentProofIfLocal(?string $proofUrl): void {
    if (empty($proofUrl)) return;

    $uploadRoot = realpath(__DIR__ . '/../../assets/uploads/payments');
    $proofPath = realpath(__DIR__ . '/../../' . ltrim($proofUrl, '/\\'));

    if ($uploadRoot && $proofPath && str_starts_with($proofPath, $uploadRoot) && is_file($proofPath)) {
        unlink($proofPath);
    }
}

function uploadPaymentProof(int $user_id, int $order_id): array {
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'message' => 'Bukti pembayaran wajib diupload.'];
    }

    $file = $_FILES['payment_proof'];
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];
    $max_size = 2 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi batas server).',
            UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar.',
            UPLOAD_ERR_PARTIAL    => 'Upload tidak lengkap, coba lagi.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file.',
        ];

        return [
            'success' => false,
            'message' => $upload_errors[$file['error']] ?? 'Gagal upload bukti pembayaran.',
        ];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Ukuran bukti pembayaran maksimal 2MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mime_type, $allowed)) {
        return ['success' => false, 'message' => 'Format bukti pembayaran harus JPG, PNG, WEBP, atau PDF.'];
    }

    $ext = $allowed[$mime_type];
    $filename = 'payment_' . $user_id . '_' . $order_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $upload_dir = __DIR__ . '/../../assets/uploads/payments/';
    $upload_path = $upload_dir . $filename;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => false, 'message' => 'Gagal menyimpan bukti pembayaran.'];
    }

    return [
        'success' => true,
        'payment_proof' => 'assets/uploads/payments/' . $filename,
        'upload_path' => $upload_path,
    ];
}

if (!isset($_SESSION['user'])) {
    respond(false, 'Silakan login terlebih dahulu.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method tidak diizinkan.');
}

include '../../includes/db.php';

$user_id = (int) $_SESSION['user']['ID_User'];
$order_id = (int) ($_POST['order_id'] ?? 0);
$payment_method = trim($_POST['payment_method'] ?? '');
$allowed_methods = ['Transfer Bank', 'E-Wallet', 'QRIS'];

if ($order_id <= 0) {
    respond(false, 'Order tidak valid.');
}

if (!in_array($payment_method, $allowed_methods, true)) {
    respond(false, 'Pilih metode pembayaran yang valid.');
}

$upload = uploadPaymentProof($user_id, $order_id);
if (!$upload['success']) {
    respond(false, $upload['message']);
}

mysqli_begin_transaction($conn);

$stmt = mysqli_prepare(
    $conn,
    "SELECT o.order_id, o.order_status, o.total_price,
            p.payment_id, p.payment_status, p.payment_proof
     FROM orders o
     LEFT JOIN payment p ON o.order_id = p.order_id
     WHERE o.order_id = ? AND o.user_id = ?
     LIMIT 1
     FOR UPDATE"
);

if (!$stmt) {
    mysqli_rollback($conn);
    if (is_file($upload['upload_path'])) unlink($upload['upload_path']);
    respond(false, 'Gagal memproses pembayaran.');
}

mysqli_stmt_bind_param($stmt, 'ii', $order_id, $user_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$order) {
    mysqli_rollback($conn);
    if (is_file($upload['upload_path'])) unlink($upload['upload_path']);
    respond(false, 'Order tidak ditemukan.');
}

$order_status = $order['order_status'];
$payment_status = $order['payment_status'] ?? 'pending';

if ($order_status === 'confirmed' || $payment_status === 'confirmed') {
    mysqli_rollback($conn);
    if (is_file($upload['upload_path'])) unlink($upload['upload_path']);
    respond(false, 'Pembayaran order ini sudah dikonfirmasi.');
}

if ($order_status === 'cancelled' || $payment_status === 'cancelled') {
    mysqli_rollback($conn);
    if (is_file($upload['upload_path'])) unlink($upload['upload_path']);
    respond(false, 'Order ini sudah dibatalkan.');
}

if (!empty($order['payment_id'])) {
    $payment_id = (int) $order['payment_id'];
    $payment_proof = $upload['payment_proof'];
    $payStmt = mysqli_prepare(
        $conn,
        "UPDATE payment
         SET payment_method = ?, payment_status = 'paid', payment_proof = ?, paid_at = NOW()
         WHERE payment_id = ?"
    );
    if (!$payStmt) {
        mysqli_rollback($conn);
        if (is_file($upload['upload_path'])) unlink($upload['upload_path']);
        respond(false, 'Gagal menyimpan pembayaran.');
    }
    mysqli_stmt_bind_param($payStmt, 'ssi', $payment_method, $payment_proof, $payment_id);
    $paymentOk = mysqli_stmt_execute($payStmt);
    mysqli_stmt_close($payStmt);
} else {
    $amount = (float) $order['total_price'];
    $payment_proof = $upload['payment_proof'];
    $payStmt = mysqli_prepare(
        $conn,
        "INSERT INTO payment (order_id, amount, payment_method, payment_status, payment_proof, paid_at, updated_at)
         VALUES (?, ?, ?, 'paid', ?, NOW(), NOW())"
    );
    if (!$payStmt) {
        mysqli_rollback($conn);
        if (is_file($upload['upload_path'])) unlink($upload['upload_path']);
        respond(false, 'Gagal menyimpan pembayaran.');
    }
    mysqli_stmt_bind_param($payStmt, 'idss', $order_id, $amount, $payment_method, $payment_proof);
    $paymentOk = mysqli_stmt_execute($payStmt);
    mysqli_stmt_close($payStmt);
}

if (!$paymentOk) {
    mysqli_rollback($conn);
    if (is_file($upload['upload_path'])) unlink($upload['upload_path']);
    respond(false, 'Gagal menyimpan pembayaran.');
}

$orderStmt = mysqli_prepare($conn, "UPDATE orders SET order_status = 'paid' WHERE order_id = ? AND user_id = ?");
if (!$orderStmt) {
    mysqli_rollback($conn);
    if (is_file($upload['upload_path'])) unlink($upload['upload_path']);
    respond(false, 'Gagal memperbarui order.');
}
mysqli_stmt_bind_param($orderStmt, 'ii', $order_id, $user_id);
$orderOk = mysqli_stmt_execute($orderStmt);
mysqli_stmt_close($orderStmt);

if (!$orderOk) {
    mysqli_rollback($conn);
    if (is_file($upload['upload_path'])) unlink($upload['upload_path']);
    respond(false, 'Gagal memperbarui order.');
}

mysqli_commit($conn);

deletePaymentProofIfLocal($order['payment_proof'] ?? null);

respond(true, 'Bukti pembayaran berhasil dikirim. Menunggu konfirmasi admin.', [
    'payment_proof' => $upload['payment_proof'],
]);
