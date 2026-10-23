<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);

requireAuth([ROLE_DELIVERY_AGENT]);
$db = getDB();

try {
    $pkgs = $db->query(
        "SELECT id, name, description, price_birr, task_count, sort_order
         FROM credit_packages WHERE is_active = 1
         ORDER BY sort_order, price_birr"
    )->fetchAll();
    apiSuccess(['packages' => $pkgs]);
} catch (Exception $e) {
    apiSuccess(['packages' => []]);
}
