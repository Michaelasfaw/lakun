<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$admin = requireAuth([ROLE_ADMIN]);
$body  = getJsonBody();
$err   = validateRequired($body, ['user_id', 'amount', 'description']);
if ($err) apiError($err, 422);

$userId = (int)$body['user_id'];
$amount = (int)$body['amount'];
$desc   = trim($body['description']);
$type   = $amount >= 0 ? 'adjustment' : 'adjustment';

$db = getDB();
$db->beginTransaction();
try {
    $user = $db->prepare("SELECT id, name, role, credits FROM users WHERE id = ? FOR UPDATE");
    $user->execute([$userId]);
    $user = $user->fetch();
    if (!$user) { $db->rollBack(); apiError('User not found', 404); }

    $newBalance = $user['credits'] + $amount;
    if ($newBalance < 0) $newBalance = 0;

    $db->prepare("UPDATE users SET credits = ? WHERE id = ?")->execute([$newBalance, $userId]);

    $db->prepare(
        "INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, admin_id)
         VALUES (?, 'adjustment', ?, ?, ?, ?)"
    )->execute([$userId, $amount, $newBalance, $desc, (int)$admin['sub']]);

    $db->commit();
    apiSuccess(['new_balance' => $newBalance], 'Credits adjusted');
} catch (Exception $e) {
    $db->rollBack();
    apiError('Failed to adjust credits', 500);
}
