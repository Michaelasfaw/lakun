<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

$auth = requireAuth([ROLE_ADMIN]);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$body   = getJsonBody();
$action = $body['action'] ?? '';
$db     = getDB();

// ── 1. Open for bid ─────────────────────────────────────────────────────────
// Admin reviews a `requested` order and opens it for vendor bidding.
if ($action === 'open_for_bid') {
    $err = validateRequired($body, ['order_id']);
    if ($err) apiError($err, 422);
    $orderId = (int)$body['order_id'];

    $order = $db->prepare('SELECT id, status, customer_id, item_name FROM orders WHERE id = ?')->execute([$orderId]);
    $order = $db->query("SELECT id, status, customer_id, item_name FROM orders WHERE id = $orderId")->fetch();
    if (!$order) apiError('Order not found', 404);
    if ($order['status'] !== 'requested') apiError('Order must be in "requested" status to open for bid', 422);

    $window = (int)($body['bid_window_seconds'] ?? 120);
    $db->prepare(
        "UPDATE orders SET status = 'sent_to_vendors', bid_window_ends_at = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE id = ?"
    )->execute([$window, $orderId]);

    // Notify the customer
    $db->prepare(
        'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
    )->execute([
        $order['customer_id'], $orderId, 'order_reviewed',
        'Order Reviewed',
        "Your order for \"{$order['item_name']}\" has been reviewed and is now open for vendor bids.",
    ]);

    apiSuccess(['order_id' => $orderId], 'Order opened for bidding');
}

// ── 2. Edit order (while requested) ─────────────────────────────────────────
if ($action === 'edit_order') {
    $err = validateRequired($body, ['order_id']);
    if ($err) apiError($err, 422);
    $orderId = (int)$body['order_id'];

    $order = $db->query("SELECT status FROM orders WHERE id = $orderId")->fetch();
    if (!$order) apiError('Order not found', 404);
    if ($order['status'] !== 'requested') apiError('Can only edit orders in "requested" status', 422);

    $fields = []; $params = [];
    foreach (['item_name', 'quantity', 'notes', 'category'] as $f) {
        if (isset($body[$f])) { $fields[] = "$f = ?"; $params[] = $body[$f]; }
    }
    if (!$fields) apiError('Nothing to update', 422);
    $params[] = $orderId;
    $db->prepare("UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);

    apiSuccess(['order_id' => $orderId], 'Order updated');
}

// ── 3. Mark vendor paid ──────────────────────────────────────────────────────
// Admin has transferred the item amount to the vendor.
if ($action === 'mark_vendor_paid') {
    $err = validateRequired($body, ['order_id']);
    if ($err) apiError($err, 422);
    $orderId = (int)$body['order_id'];

    $order = $db->query(
        "SELECT id, status, selected_vendor_id, item_name, item_price FROM orders WHERE id = $orderId"
    )->fetch();
    if (!$order) apiError('Order not found', 404);
    if ($order['status'] !== 'confirmed') apiError('Order must be confirmed before marking vendor paid', 422);

    $ref    = sanitizeString($body['payment_ref'] ?? '', 100);
    $amount = isset($body['amount']) ? (float)$body['amount'] : null;

    $db->prepare(
        "UPDATE orders SET status = 'vendor_paid', vendor_paid_at = NOW(),
         vendor_payment_ref = ?, vendor_payment_amount = ? WHERE id = ?"
    )->execute([$ref ?: null, $amount, $orderId]);

    // Notify vendor
    if ($order['selected_vendor_id']) {
        $db->prepare(
            'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $order['selected_vendor_id'], $orderId, 'vendor_paid',
            'Payment Sent to You',
            "Admin has transferred payment for order \"{$order['item_name']}\". Ref: " . ($ref ?: 'N/A') . ". Please prepare the items for pickup.",
        ]);
    }

    apiSuccess(['order_id' => $orderId], 'Vendor marked as paid. Delivery agent can now pick up.');
}

apiError('Unknown action', 422);
