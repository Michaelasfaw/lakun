<?php
/**
 * POST — Customer accepts the bid sent to them
 * Body: { order_id }
 * Changes status: sent_to_customer → awaiting_payment
 * Shows customer the payment instructions
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);
$auth = requireAuth([ROLE_CUSTOMER]);

$db   = getDB();
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$orderId = (int)($body['order_id'] ?? 0);
$bidId   = (int)($body['bid_id']   ?? 0);
if (!$orderId) apiError('order_id required', 422);

$order = $db->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$order->execute([$orderId, $auth['sub']]);
$order = $order->fetch();
if (!$order) apiError('Order not found', 404);

// Allow from sent_to_customer or legacy awaiting_confirmation
if (!in_array($order['status'], ['sent_to_customer', 'awaiting_confirmation'])) {
    apiError('Order is not awaiting your bid acceptance (status: ' . $order['status'] . ')', 409);
}

// If customer chose a specific bid, update the selected vendor + price
if ($bidId) {
    $bidStmt = $db->prepare("SELECT b.*, u.id AS vendor_id FROM bids b JOIN users u ON u.id = b.vendor_id WHERE b.id = ? AND b.order_id = ?");
    $bidStmt->execute([$bidId, $orderId]);
    $chosenBid = $bidStmt->fetch();
    if (!$chosenBid) apiError('Selected bid not found for this order', 404);

    $newTotal = (float)$chosenBid['price'] + (float)$order['delivery_fee'] + (float)($order['tip_amount'] ?? 0);
    $db->prepare(
        "UPDATE orders SET selected_vendor_id = ?, item_price = ?, total_price = ? WHERE id = ?"
    )->execute([$chosenBid['vendor_id'], $chosenBid['price'], $newTotal, $orderId]);
    $order['total_price'] = $newTotal;
    $order['item_price']  = $chosenBid['price'];
    $order['selected_vendor_id'] = $chosenBid['vendor_id'];
}

// Update to awaiting_payment
$db->prepare(
    "UPDATE orders SET status = 'awaiting_payment' WHERE id = ?"
)->execute([$orderId]);

// Get payment settings to return to customer
$settings = $db->query("SELECT `key`, value FROM settings WHERE `key` IN ('payment_bank_name','payment_bank_account','payment_account_name','payment_telebirr')")->fetchAll();
$pay = [];
foreach ($settings as $s) $pay[$s['key']] = $s['value'];

// Notify admin
$admins = $db->query("SELECT id FROM users WHERE role='admin'")->fetchAll();
foreach ($admins as $admin) {
    $db->prepare(
        "INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, 'awaiting_payment', 'Customer Accepted Bid', ?)"
    )->execute([
        $admin['id'], $orderId,
        "Order " . ($order['order_code'] ?? ('LK'.$orderId)) . " — Customer accepted. Awaiting payment of ETB " . number_format((float)$order['total_price'], 2),
    ]);
}

apiSuccess([
    'order_id'    => $orderId,
    'total_price' => $order['total_price'],
    'new_status'  => 'awaiting_payment',
    'payment'     => $pay,
], 'Bid accepted! Please complete payment to proceed.');
