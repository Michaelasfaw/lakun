<?php
/**
 * POST — Admin verifies or rejects payment proof
 * Body: { order_id, action: 'approve'|'reject', reason? }
 * On approve: notifies customer, vendor AND delivery agent
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);
$auth   = requireAuth([ROLE_ADMIN]);

$db     = getDB();
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$orderId= (int)($body['order_id'] ?? 0);
$action = $body['action'] ?? '';

if (!$orderId) apiError('order_id required', 422);
if (!in_array($action, ['approve', 'reject'])) apiError('action must be approve or reject', 422);

$stmt = $db->prepare(
    "SELECT o.*, IFNULL(o.order_code, CONCAT('LK', o.id)) AS order_code,
            o.customer_id, o.selected_vendor_id, o.delivery_agent_id,
            o.item_name, o.item_price, o.delivery_fee, o.tip_amount, o.total_price, o.status
     FROM orders o WHERE o.id = ?"
);
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) apiError('Order not found', 404);
if ($order['status'] !== 'payment_under_review') {
    apiError('Order is not in payment_under_review state', 409);
}

$adminId   = $auth['sub'];
$orderCode = $order['order_code'];
$itemName  = $order['item_name'];
$itemPrice = (float)($order['item_price'] ?? 0);
$deliveryFee = (float)($order['delivery_fee'] ?? 0);
$tipAmount = (float)($order['tip_amount'] ?? 0);
$totalPrice = (float)($order['total_price'] ?? ($itemPrice + $deliveryFee + $tipAmount));

// Vendor amount = item price only (platform keeps delivery fee + tip)
$vendorAmount = $itemPrice;

$notifStmt = $db->prepare(
    'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
);

if ($action === 'approve') {
    $db->prepare(
        "UPDATE orders SET
            status = 'confirmed',
            payment_verified_at = NOW(),
            payment_verified_by = ?,
            confirmed_at = NOW()
         WHERE id = ?"
    )->execute([$adminId, $orderId]);

    // Notify customer
    $notifStmt->execute([
        $order['customer_id'], $orderId, NOTIF_PAYMENT_VERIFIED,
        'Payment Confirmed! ✅',
        "Your payment for {$orderCode} ({$itemName}) has been verified. Your order is confirmed! Total paid: ETB " . number_format($totalPrice, 2),
    ]);

    // Notify vendor (transfer amount = item price only)
    if ($order['selected_vendor_id']) {
        $notifStmt->execute([
            $order['selected_vendor_id'], $orderId, NOTIF_PAYMENT_VERIFIED,
            "Payment Received — Prepare Order {$orderCode}",
            "Order {$orderCode} payment confirmed. Prepare items for pickup. You will receive ETB " . number_format($vendorAmount, 2) . " for the items.",
        ]);
    }

    // Notify delivery agent (payment confirmed — safe to pick up)
    if ($order['delivery_agent_id']) {
        $notifStmt->execute([
            $order['delivery_agent_id'], $orderId, NOTIF_ORDER_CONFIRMED,
            "Payment Confirmed — {$orderCode} Ready for Pickup",
            "Payment verified for order {$orderCode} ({$itemName}). Vendor has been notified to prepare items.",
        ]);
    }

    apiSuccess([
        'order_id'     => $orderId,
        'order_code'   => $orderCode,
        'new_status'   => 'confirmed',
        'vendor_amount'=> $vendorAmount,
        'delivery_fee' => $deliveryFee,
        'tip_amount'   => $tipAmount,
        'total'        => $totalPrice,
    ], 'Payment approved. Order confirmed.');

} else {
    $reason = trim($body['reason'] ?? 'Payment could not be verified. Please try again.');
    $db->prepare(
        "UPDATE orders SET
            status = 'failed_payment',
            payment_verified_at = NOW(),
            payment_verified_by = ?
         WHERE id = ?"
    )->execute([$adminId, $orderId]);

    // Notify customer
    $notifStmt->execute([
        $order['customer_id'], $orderId, NOTIF_PAYMENT_REJECTED,
        'Payment Rejected ❌',
        "Your payment proof for {$orderCode} was rejected. Reason: {$reason}. Please re-upload.",
    ]);

    apiSuccess(['order_id' => $orderId, 'order_code' => $orderCode, 'new_status' => 'failed_payment'], 'Payment rejected.');
}
