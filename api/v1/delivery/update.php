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

$auth = requireAuth([ROLE_DELIVERY_AGENT]);
$body = getJsonBody();

$err = validateRequired($body, ['order_id', 'status']);
if ($err) apiError($err, 422);

$orderId = (int)$body['order_id'];
$status  = sanitizeString($body['status'], 20);

$orderModel = new Order();
$ok = $orderModel->updateDeliveryStatus($orderId, $auth['sub'], $status);
if (!$ok) apiError('Cannot update status — check order ID, assignment, or status value', 422);

// Notify the customer
$pdo = getDB();
$order = $pdo->prepare('SELECT customer_id FROM orders WHERE id = ?');
$order->execute([$orderId]);
$row = $order->fetch();
if ($row) {
    $messages = [
        'picked_up'        => ['Picked Up!',      "Your order has been picked up from the shop."],
        'on_the_way'       => ['On the Way!',      "Your delivery is on its way to you."],
        'out_for_delivery' => ['Out for Delivery!', "Your delivery is on its way to you."],
        'delivered'        => ['Order Delivered!', "Your order has been delivered. Please pay the rider."],
    ];
    $msg = $messages[$status] ?? ['Status Update', "Your order status: $status"];
    $pdo->prepare(
        'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
    )->execute([$row['customer_id'], $orderId, $status, $msg[0], $msg[1]]);
}

apiSuccess(['order_id' => $orderId, 'status' => $status], 'Status updated.');
