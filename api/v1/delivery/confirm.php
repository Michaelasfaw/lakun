<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Order.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$auth = requireAuth([ROLE_CUSTOMER]);
$body = getJsonBody();

$err = validateRequired($body, ['order_id']);
if ($err) apiError($err, 422);

$orderId = (int)$body['order_id'];

$orderModel = new Order();
$ok = $orderModel->confirmDelivery($orderId, $auth['sub']);
if (!$ok) apiError('Cannot confirm delivery — order may not be in delivered status or does not belong to you', 422);

// Notify the delivery agent and vendor
$pdo = getDB();
$order = $pdo->prepare('SELECT delivery_agent_id, selected_vendor_id FROM orders WHERE id = ?');
$order->execute([$orderId]);
$row = $order->fetch();
if ($row) {
    $notifStmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
    );
    if ($row['delivery_agent_id']) {
        $notifStmt->execute([
            $row['delivery_agent_id'], $orderId, 'completed',
            'Delivery Confirmed', 'The customer has confirmed receipt of order #' . $orderId . '.'
        ]);
    }
    if ($row['selected_vendor_id']) {
        $notifStmt->execute([
            $row['selected_vendor_id'], $orderId, 'completed',
            'Order Completed', 'Order #' . $orderId . ' has been confirmed as delivered by the customer.'
        ]);
    }
}

apiSuccess(['order_id' => $orderId, 'status' => 'completed'], 'Delivery confirmed. Thank you!');
