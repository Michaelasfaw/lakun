<?php
/**
 * POST — Customer uploads payment proof
 * Body (multipart/form-data):
 *   order_id, payment_method (bank_transfer|telebirr), txn_id, sender_name
 *   proof (file — image/jpeg|png|gif|webp, max 5MB)
 *
 * Note: cors.php calls ob_start() so PHP warnings never corrupt JSON.
 *       apiResponse() calls ob_end_clean() before emitting JSON.
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);
$auth    = requireAuth([ROLE_CUSTOMER]);

$db      = getDB();
$userId  = $auth['sub'];
$orderId = (int)($_POST['order_id'] ?? 0);

if (!$orderId) apiError('order_id required', 422);

// Verify order belongs to this customer and is in correct state
$stmt = $db->prepare("SELECT id, status, total_price FROM orders WHERE id=? AND customer_id=?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) apiError('Order not found', 404);
if (!in_array($order['status'], ['awaiting_payment', 'sent_to_customer', 'awaiting_confirmation'])) {
    apiError('Order is not awaiting payment (status: ' . $order['status'] . ')', 409);
}

// Optional fields
$method     = trim($_POST['payment_method'] ?? '');
$txnId      = trim($_POST['txn_id']        ?? '');
$senderName = trim($_POST['sender_name']   ?? '');

if ($method && !in_array($method, ['bank_transfer', 'telebirr'])) {
    apiError('Invalid payment_method', 422);
}

// ── File validation ───────────────────────────────────────────────────────────
if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
    $errMsg = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form size limit',
        UPLOAD_ERR_PARTIAL    => 'File only partially uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'No temp directory on server',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
    ][$_FILES['proof']['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'Upload error';
    apiError($errMsg, 422);
}

if ($_FILES['proof']['size'] > 5 * 1024 * 1024) {
    apiError('File too large (max 5MB)', 422);
}

// Detect MIME type using magic bytes (reliable on all PHP configs)
$tmp   = $_FILES['proof']['tmp_name'];
$fh    = fopen($tmp, 'rb');
$magic = bin2hex(fread($fh, 4));
fclose($fh);

if      (str_starts_with($magic, 'ffd8'))     $mime = 'image/jpeg';
elseif  (str_starts_with($magic, '89504e47')) $mime = 'image/png';
elseif  (str_starts_with($magic, '52494646')) $mime = 'image/webp';
elseif  (str_starts_with($magic, '47494638')) $mime = 'image/gif';
else    $mime = function_exists('mime_content_type') ? mime_content_type($tmp) : 'unknown';

if (!in_array($mime, ['image/jpeg','image/png','image/gif','image/webp'])) {
    apiError('Invalid file type — use JPEG or PNG (detected: ' . $mime . ')', 422);
}

// ── Save file ─────────────────────────────────────────────────────────────────
$uploadDir = dirname(__DIR__, 3) . '/uploads/payment_proofs/';

// Create directory if needed (try two possible paths)
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}
if (!is_dir($uploadDir)) {
    // Fallback: derive from DOCUMENT_ROOT
    $root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
    $alt  = $root . '/lakun/uploads/payment_proofs/';
    if (!is_dir($alt)) @mkdir($alt, 0755, true);
    if (is_dir($alt)) $uploadDir = $alt;
    else apiError('Upload directory could not be created — run setup-uploads.php on the server', 500);
}

$ext      = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION) ?: 'jpg');
if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) $ext = 'jpg';
$filename = 'proof_' . $orderId . '_' . time() . '.' . $ext;

if (!move_uploaded_file($tmp, $uploadDir . $filename)) {
    apiError('File could not be saved — check write permissions on uploads/payment_proofs/', 500);
}

$proofPath = 'uploads/payment_proofs/' . $filename;

// ── Update order ──────────────────────────────────────────────────────────────
$db->prepare(
    "UPDATE orders SET
        status              = 'payment_under_review',
        payment_method      = ?,
        payment_txn_id      = ?,
        payment_sender_name = ?,
        payment_proof_path  = ?,
        payment_uploaded_at = NOW()
     WHERE id = ?"
)->execute([$method ?: null, $txnId ?: null, $senderName ?: null, $proofPath, $orderId]);

// ── Notify admins ─────────────────────────────────────────────────────────────
$admins = $db->query("SELECT id FROM users WHERE role='admin'")->fetchAll();
foreach ($admins as $admin) {
    $db->prepare(
        "INSERT INTO notifications (user_id, order_id, type, title, body)
         VALUES (?, ?, 'payment_uploaded', 'Payment Proof Uploaded', ?)"
    )->execute([
        $admin['id'], $orderId,
        'Order #' . $orderId
            . ($method  ? ' — ' . strtoupper(str_replace('_', ' ', $method)) : '')
            . ($txnId   ? ' · TXN: ' . $txnId : ''),
    ]);
}

apiSuccess(['order_id' => $orderId], 'Payment proof submitted. Awaiting admin verification.');
