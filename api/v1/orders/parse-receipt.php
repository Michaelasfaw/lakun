<?php
/**
 * POST — Parse a payment receipt screenshot and extract fields.
 * Does NOT create or modify any order — purely read/analyse.
 *
 * Body (multipart/form-data):
 *   proof  — image file (jpeg/png/webp, max 5 MB)
 *
 * Response:
 *   { transaction_id, sender_name, payment_method, source, detected }
 *   source: "qr" | "ocr" | null
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once dirname(__DIR__, 3) . '/app/services/ReceiptParserService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('Method not allowed', 405);
}

requireAuth([ROLE_CUSTOMER]);

// ── Validate uploaded file ────────────────────────────────────────────────────
if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
    apiError('No image file provided', 422);
}

$file        = $_FILES['proof'];
$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$mime        = mime_content_type($file['tmp_name']);

if (!in_array($mime, $allowedMime)) {
    apiError('Invalid file type — use JPEG, PNG, or WebP', 422);
}
if ($file['size'] > 5 * 1024 * 1024) {
    apiError('File too large (max 5 MB)', 422);
}

// ── Parse ─────────────────────────────────────────────────────────────────────
$result = ReceiptParserService::parse($file['tmp_name']);

apiSuccess([
    'transaction_id' => $result['transaction_id'],
    'sender_name'    => $result['sender_name'],
    'payment_method' => $result['payment_method'],
    'source'         => $result['source'],
    'detected'       => (bool)($result['transaction_id'] || $result['sender_name']),
]);
