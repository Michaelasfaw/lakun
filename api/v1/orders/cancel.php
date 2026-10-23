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
$orderId = (int)($body['order_id'] ?? 0);
if (!$orderId) apiError('order_id required', 422);

$orderModel = new Order();
$ok = $orderModel->cancel($orderId, $auth['sub']);
if (!$ok) apiError('Cannot cancel at this stage', 422);

apiSuccess(['order_id' => $orderId], 'Order cancelled.');
