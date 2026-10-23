<?php
/**
 * POST — Admin selects a bid and forwards it to the customer
 * Body: { order_id, bid_id }
 * Changes order status: bids_received/sent_to_vendors → sent_to_customer
 * Sets selected_vendor_id, item_price, total_price on the order
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);
requireAuth([ROLE_ADMIN]);

$db   = getDB();
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$orderId = (int)($body['order_id'] ?? 0);
$bidId   = (int)($body['bid_id']   ?? 0);

if (!$orderId || !$bidId) apiError('order_id and bid_id required', 422);

// Load order
$order = $db->prepare(
    "SELECT o.*, u.name as customer_name FROM orders o
     JOIN users u ON u.id = o.customer_id WHERE o.id = ?"
);
$order->execute([$orderId]);
$order = $order->fetch();
if (!$order) apiError('Order not found', 404);

$validStatuses = ['bidding', 'sent_to_vendors', 'bids_received'];
if (!in_array($order['status'], $validStatuses)) {
    apiError('Order must be in bidding/sent_to_vendors/bids_received state (current: ' . $order['status'] . ')', 409);
}

// Load bid
$bid = $db->prepare(
    "SELECT b.*, u.name as vendor_name, u.shop_name FROM bids b
     JOIN users u ON u.id = b.vendor_id WHERE b.id = ? AND b.order_id = ?"
);
$bid->execute([$bidId, $orderId]);
$bid = $bid->fetch();
if (!$bid) apiError('Bid not found for this order', 404);
if (!$bid['available']) apiError('Vendor marked item as unavailable', 409);

// Get delivery fee from settings
$feeRow = $db->query("SELECT value FROM settings WHERE `key`='delivery_fee'")->fetch();
$deliveryFee = $feeRow ? (float)$feeRow['value'] : 50.0;
$totalPrice  = (float)$bid['price'] + $deliveryFee;

$db->beginTransaction();
try {
    // Mark selected bid
    $db->prepare("UPDATE bids SET is_selected = 0 WHERE order_id = ?")->execute([$orderId]);
    $db->prepare("UPDATE bids SET is_selected = 1 WHERE id = ?")->execute([$bidId]);

    // Update order
    $db->prepare(
        "UPDATE orders SET
            status             = 'sent_to_customer',
            selected_bid_id    = ?,
            selected_vendor_id = ?,
            item_price         = ?,
            delivery_fee       = ?,
            total_price        = ?
         WHERE id = ?"
    )->execute([$bidId, $bid['vendor_id'], $bid['price'], $deliveryFee, $totalPrice, $orderId]);

    // Notify customer
    $db->prepare(
        "INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, 'bid_received', ?, ?)"
    )->execute([
        $order['customer_id'], $orderId,
        'Your Order is Ready!',
        "Best price found: ETB " . number_format($totalPrice, 2) . " (item ETB " . number_format($bid['price'], 2) . " + delivery ETB " . number_format($deliveryFee, 2) . "). Tap to confirm and pay.",
    ]);

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    apiError('Database error: ' . $e->getMessage(), 500);
}

apiSuccess([
    'order_id'     => $orderId,
    'bid_id'       => $bidId,
    'vendor'       => $bid['vendor_name'],
    'item_price'   => $bid['price'],
    'delivery_fee' => $deliveryFee,
    'total_price'  => $totalPrice,
    'new_status'   => 'sent_to_customer',
], 'Bid forwarded to customer. Customer notified.');
