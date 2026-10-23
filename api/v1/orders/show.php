<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Order.php';
require_once dirname(__DIR__, 3) . '/app/models/Bid.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);

$auth = requireAuth();
$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) apiError('Order ID required', 422);

$orderModel = new Order();

// Auto-process expired bidding windows
$orderModel->processExpiredWindows();

$order = $orderModel->findById($orderId);
if (!$order) apiError('Order not found', 404);

// Access control
$role = $auth['role'];
if ($role === ROLE_CUSTOMER && $order['customer_id'] != $auth['sub']) apiError('Forbidden', 403);
if ($role === ROLE_DELIVERY_AGENT && $order['delivery_agent_id'] != $auth['sub']) apiError('Forbidden', 403);
if ($role === ROLE_VENDOR && $order['selected_vendor_id'] != $auth['sub']) {
    // Vendor can view order during bidding phase
    if ($order['status'] !== 'bidding') apiError('Forbidden', 403);
}

// Attach bids — customer now sees ALL bids so they can choose their preferred vendor
$order['bids'] = [];
$bidPhases = ['bidding','sent_to_vendors','bids_received','sent_to_customer','awaiting_confirmation'];
$bidModel = new Bid();
if ($role === ROLE_ADMIN && in_array($order['status'], $bidPhases)) {
    $order['bids'] = $bidModel->getForOrder($orderId);
} elseif ($role === ROLE_CUSTOMER && in_array($order['status'], ['sent_to_customer','awaiting_confirmation','awaiting_payment','payment_under_review'])) {
    // Customer sees ALL bids with shop tags so they can compare and select
    $order['bids'] = $bidModel->getForOrder($orderId);
} elseif ($role === ROLE_VENDOR) {
    // Vendor sees only their own bid
    $myBid = $bidModel->getVendorBid($orderId, $auth['sub']);
    if ($myBid) $order['bids'] = [$myBid];
}

apiSuccess($order);
