<?php
/**
 * POST — Admin reinitiates a cancelled/expired order back into bidding
 * Body: { order_id }
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/services/LocationService.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);
requireAuth([ROLE_ADMIN]);

$db      = getDB();
$body    = json_decode(file_get_contents('php://input'), true) ?? [];
$orderId = (int)($body['order_id'] ?? 0);

if (!$orderId) apiError('order_id required', 422);

$order = $db->prepare("SELECT id, status, building_id, item_name, is_fast_order FROM orders WHERE id=?");
$order->execute([$orderId]);
$order = $order->fetch();

if (!$order) apiError('Order not found', 404);
if (!in_array($order['status'], ['cancelled', 'expired'])) {
    apiError('Only cancelled or expired orders can be reinitiated', 409);
}

// Get bid window from settings
$windowKey = $order['is_fast_order'] ? 'bid_window_food_secs' : 'bid_window_other_secs';
$windowRow = $db->query("SELECT value FROM settings WHERE `key`='$windowKey'")->fetch();
$windowSecs = $windowRow ? (int)$windowRow['value'] : 30;
if ($windowSecs < 10) $windowSecs = 30;

// Reset order: clear previous bid selection, set status back to bidding with a fresh window
$db->prepare(
    "UPDATE orders SET
        status             = 'bidding',
        selected_bid_id    = NULL,
        selected_vendor_id = NULL,
        item_price         = NULL,
        total_price        = NULL,
        bid_window_ends_at = DATE_ADD(NOW(), INTERVAL ? SECOND),
        payment_method     = NULL,
        payment_txn_id     = NULL,
        payment_sender_name= NULL,
        payment_proof_path = NULL,
        payment_uploaded_at= NULL
     WHERE id = ?"
)->execute([$windowSecs, $orderId]);

// Clear old bids so vendors can bid fresh
$db->prepare("DELETE FROM bids WHERE order_id = ?")->execute([$orderId]);

// Notify vendors
$vendorIds = LocationService::getVendorIdsForBuilding($db, (int)$order['building_id']);
$notifStmt = $db->prepare(
    'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
);
foreach ($vendorIds as $vid) {
    $notifStmt->execute([
        $vid, $orderId, NOTIF_NEW_ORDER,
        'Order Reinitiated — Bid Now!',
        $order['item_name']
    ]);
}

apiSuccess(['order_id' => $orderId, 'bid_window_seconds' => $windowSecs], 'Order reinitiated for bidding.');
