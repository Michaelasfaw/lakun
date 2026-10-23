<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Bid.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$auth = requireAuth([ROLE_VENDOR]);
$body = getJsonBody();

$err = validateRequired($body, ['order_id', 'price']);
if ($err) apiError($err, 422);

$orderId   = (int)$body['order_id'];
$price     = (float)$body['price'];
$available = isset($body['available']) ? (bool)$body['available'] : true;
$note      = isset($body['note']) ? trim((string)$body['note']) : null;
$breakdown = isset($body['items_breakdown']) && is_array($body['items_breakdown'])
             ? json_encode($body['items_breakdown'])
             : null;

if ($price <= 0) apiError('Price must be greater than 0', 422);

$bidModel = new Bid();
$result = $bidModel->submit($orderId, $auth['sub'], $price, $available, $note, $breakdown);

if ($result === false) {
    apiError('Bid window has closed or order is no longer accepting bids', 422);
}

apiSuccess(['order_id' => $orderId, 'price' => $price], 'Bid submitted!');
