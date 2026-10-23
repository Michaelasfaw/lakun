<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Public: delivery_fee and bid_window_seconds readable by all
    $stmt = $pdo->query('SELECT `key`, value FROM settings');
    $rows = $stmt->fetchAll();
    $out = [];
    foreach ($rows as $r) $out[$r['key']] = $r['value'];
    apiSuccess($out);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = requireAuth([ROLE_ADMIN]);
    $body = getJsonBody();
    $allowed = [
        'delivery_fee', 'bid_window_seconds',
        'bid_window_food_secs', 'bid_window_other_secs',
        'bid_grace_after_first_secs',
        'payment_bank_name', 'payment_bank_account',
        'payment_account_name', 'payment_telebirr',
    ];
    foreach ($allowed as $k) {
        if (isset($body[$k])) {
            $pdo->prepare(
                'INSERT INTO settings (`key`, value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE value = VALUES(value)'
            )->execute([$k, (string)$body[$k]]);
        }
    }
    apiSuccess([], 'Settings updated.');
}

apiError('Method not allowed', 405);
