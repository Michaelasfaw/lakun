<?php
/**
 * POST — Vendor approves item pickup for an assigned order
 * Body: { order_id }
 * Sets vendor_approved_at = NOW() and notifies delivery agent
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);
$auth = requireAuth([ROLE_VENDOR]);

$db   = getDB();
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$orderId = (int)($body['order_id'] ?? 0);
if (!$orderId) apiError('order_id required', 422);

// Verify this vendor is the selected vendor for this order
$stmt = $db->prepare(
    "SELECT o.id, o.status, o.delivery_agent_id, o.item_name, o.vendor_approved_at,
            IFNULL(o.order_code, CONCAT('LK', o.id)) AS order_code
     FROM orders o
     WHERE o.id = ? AND o.selected_vendor_id = ?"
);
$stmt->execute([$orderId, $auth['sub']]);
$order = $stmt->fetch();

if (!$order) apiError('Order not found or you are not the assigned vendor', 404);
if (!in_array($order['status'], ['assigned', 'confirmed'])) {
    apiError('Order is not in an approvable state (must be confirmed or assigned)', 409);
}
if ($order['vendor_approved_at']) {
    apiError('You have already approved pickup for this order', 409);
}

// Mark vendor approval
$db->prepare(
    'UPDATE orders SET vendor_approved_at = NOW() WHERE id = ?'
)->execute([$orderId]);

// Notify delivery agent (if assigned)
if ($order['delivery_agent_id']) {
    $db->prepare(
        'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
    )->execute([
        $order['delivery_agent_id'],
        $orderId,
        NOTIF_ORDER_ASSIGNED,
        'Vendor Ready — Pick Up Order ' . $order['order_code'],
        'The vendor has approved pickup for order ' . $order['order_code'] . ' (' . $order['item_name'] . '). You can now proceed to collect.',
    ]);
}

apiSuccess(['order_id' => $orderId, 'order_code' => $order['order_code']], 'Pickup approved. Delivery agent has been notified.');
