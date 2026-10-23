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

$auth = requireAuth([ROLE_ADMIN]);
$body = getJsonBody();

$err = validateRequired($body, ['order_id', 'agent_id']);
if ($err) apiError($err, 422);

$orderId = (int)$body['order_id'];
$agentId = (int)$body['agent_id'];

$pdo = getDB();

// Check agent credits
$agentRow = $pdo->prepare("SELECT name, credits FROM users WHERE id = ? AND role = 'delivery_agent'");
$agentRow->execute([$agentId]);
$agent = $agentRow->fetch();
if (!$agent) apiError('Agent not found', 404);
if ($agent['credits'] < 1) {
    apiError("Agent \"{$agent['name']}\" has no credits. Ask them to purchase a credit package.", 402);
}

$orderModel = new Order();
$ok = $orderModel->assignAgent($orderId, $agentId);
if (!$ok) apiError('Cannot assign — order must be confirmed or vendor_paid', 422);

// Deduct 1 credit from agent
$pdo->beginTransaction();
try {
    $newBalance = max(0, $agent['credits'] - 1);
    $pdo->prepare("UPDATE users SET credits = ? WHERE id = ?")->execute([$newBalance, $agentId]);
    $pdo->prepare(
        "INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, order_id, admin_id)
         VALUES (?, 'deduction', -1, ?, ?, ?, ?)"
    )->execute([$agentId, $newBalance, "Delivery assignment — Order #$orderId", $orderId, (int)$auth['sub']]);
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    // Assignment succeeded but credit deduction failed — log and continue
}

// Notify agent
$order = $pdo->prepare('SELECT customer_phone, item_name, building_id, block, floor, house_no, total_price FROM orders WHERE id = ?');
$order->execute([$orderId]);
$ord = $order->fetch();

if ($ord) {
    $pdo->prepare(
        'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
    )->execute([
        $agentId, $orderId, NOTIF_ORDER_ASSIGNED,
        'New Delivery Task',
        "Deliver: {$ord['item_name']} — Block {$ord['block']}, Floor {$ord['floor']}, House {$ord['house_no']}. COD: ₦{$ord['total_price']}",
    ]);
    $custRow = $pdo->prepare('SELECT customer_id FROM orders WHERE id = ?');
    $custRow->execute([$orderId]);
    $cust = $custRow->fetch();
    if ($cust) {
        $pdo->prepare(
            'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $cust['customer_id'], $orderId, NOTIF_ORDER_ASSIGNED,
            'Delivery Agent Assigned!',
            'A delivery agent has been assigned to your order and will pick it up soon.',
        ]);
    }
}

apiSuccess([
    'order_id'         => $orderId,
    'agent_id'         => $agentId,
    'agent_credits_left' => $newBalance ?? $agent['credits'],
], 'Delivery agent assigned. 1 credit deducted.');
