<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Order.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);

$auth = requireAuth();
$orderModel = new Order();

// Auto-close expired bid windows on every poll
$orderModel->processExpiredWindows();

$role = $auth['role'];

if ($role === ROLE_CUSTOMER) {
    $orders = $orderModel->getForCustomer($auth['sub']);
} elseif ($role === ROLE_VENDOR) {
    $type = $_GET['type'] ?? 'open';
    $orders = $type === 'won'
        ? $orderModel->getWonByVendor($auth['sub'])
        : $orderModel->getOpenForVendor($auth['sub']);
} elseif ($role === ROLE_DELIVERY_AGENT) {
    $orders = $orderModel->getForAgent($auth['sub']);
} elseif ($role === ROLE_ADMIN) {
    $status = $_GET['status'] ?? null;
    $orders = $orderModel->getAll($status);
} else {
    apiError('Forbidden', 403);
}

apiSuccess($orders);
