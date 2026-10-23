<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Order.php';
require_once dirname(__DIR__, 3) . '/app/models/User.php';
require_once dirname(__DIR__, 3) . '/app/services/LocationService.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$auth = requireAuth([ROLE_CUSTOMER]);
$body = getJsonBody();

$err = validateRequired($body, ['building_id', 'block', 'floor', 'house_no', 'customer_phone']);
if ($err) apiError($err, 422);

// Support multi-item OR single-item
$items = [];
if (!empty($body['items']) && is_array($body['items'])) {
    foreach ($body['items'] as $it) {
        if (empty($it['name'])) continue;
        $items[] = [
            'name'  => sanitizeString($it['name'], 100),
            'qty'   => max(1, (int)($it['qty'] ?? 1)),
            'brand' => isset($it['brand']) ? sanitizeString($it['brand'], 50) : '',
        ];
    }
}
if (empty($items)) {
    // Fall back to single-item fields
    if (empty($body['item_name'])) apiError('item_name or items array required', 422);
    $items[] = [
        'name'  => sanitizeString($body['item_name'], 100),
        'qty'   => max(1, (int)($body['quantity'] ?? 1)),
        'brand' => '',
    ];
}

// Primary item for legacy compatibility
$primaryName = implode(', ', array_map(fn($i) => $i['name'] . ' ×' . $i['qty'], $items));
$primaryQty  = array_sum(array_column($items, 'qty'));
$category    = isset($body['category']) ? sanitizeString($body['category'], 50) : 'general';
$isFastOrder = ($category === 'food') ? 1 : 0;

$orderModel = new Order();

// Use category-specific bid window
$pdo = getDB();
$windowKey  = $isFastOrder ? 'bid_window_food_secs' : 'bid_window_other_secs';
$windowRow  = $pdo->query("SELECT value FROM settings WHERE `key`='$windowKey'")->fetch();
$windowSecs = $windowRow ? (int)$windowRow['value'] : 30;
if (!$windowSecs) $windowSecs = $orderModel->getBidWindowSeconds();

// Get delivery fee from settings
$row = $pdo->query('SELECT value FROM settings WHERE `key` = "delivery_fee"')->fetch();
$deliveryFee = $row ? (float)$row['value'] : 50.0;

// Pre-order scheduled_at (optional)
$scheduledAt = null;
if (!empty($body['scheduled_at'])) {
    $ts = strtotime($body['scheduled_at']);
    if ($ts && $ts > time()) {
        $scheduledAt = date('Y-m-d H:i:s', $ts);
    }
}

$orderId = $orderModel->create([
    'customer_id'        => $auth['sub'],
    'building_id'        => (int)$body['building_id'],
    'block'              => sanitizeString($body['block'], 20),
    'floor'              => sanitizeString($body['floor'], 20),
    'house_no'           => sanitizeString($body['house_no'], 20),
    'customer_phone'     => sanitizeString($body['customer_phone'], 20),
    'item_name'          => $primaryName,
    'quantity'           => $primaryQty,
    'notes'              => isset($body['notes']) ? sanitizeString($body['notes'], 500) : null,
    'delivery_fee'       => $deliveryFee,
    'bid_window_seconds' => $windowSecs,
    'scheduled_at'       => $scheduledAt,
]);

// Store multi-item JSON and category
try {
    $pdo->prepare(
        "UPDATE orders SET items_json = ?, category = ?, is_fast_order = ? WHERE id = ?"
    )->execute([json_encode($items), $category, $isFastOrder, $orderId]);
} catch (Exception $e) { /* items_json column may not exist yet */ }

// Notify vendors
$vendorIds = LocationService::getVendorIdsForBuilding($pdo, (int)$body['building_id']);
$notifStmt = $pdo->prepare(
    'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
);
$fastTag = $isFastOrder ? '⚡ FAST ORDER — ' : '';
foreach ($vendorIds as $vid) {
    $notifStmt->execute([$vid, $orderId, NOTIF_NEW_ORDER,
        $fastTag . 'New Order — Bid Now!', $primaryName]);
}

// Notify admins too
$admins = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll();
foreach ($admins as $admin) {
    $notifStmt->execute([$admin['id'], $orderId, NOTIF_NEW_ORDER,
        $fastTag . 'New Order Received', $primaryName]);
}

apiSuccess([
    'order_id'           => $orderId,
    'order_code'         => 'LK' . $orderId,
    'bid_window_seconds' => $windowSecs,
    'delivery_fee'       => $deliveryFee,
    'is_fast_order'      => (bool)$isFastOrder,
    'scheduled_at'       => $scheduledAt,
], 'Order placed. Vendors are bidding!', 201);
