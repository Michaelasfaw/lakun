<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$authUser = requireAuth([ROLE_DELIVERY_AGENT]);
$body = getJsonBody();
$err  = validateRequired($body, ['package_id']);
if ($err) apiError($err, 422);

$db = getDB();

// Get package
$pkg = $db->prepare("SELECT * FROM credit_packages WHERE id = ? AND is_active = 1");
$pkg->execute([(int)$body['package_id']]);
$pkg = $pkg->fetch();
if (!$pkg) apiError('Package not found or inactive', 404);

$userId = (int)$authUser['sub'];

$db->beginTransaction();
try {
    $userRow = $db->prepare("SELECT credits FROM users WHERE id = ? FOR UPDATE");
    $userRow->execute([$userId]);
    $userRow = $userRow->fetch();

    $newBalance = (int)$userRow['credits'] + (int)$pkg['task_count'];
    $db->prepare("UPDATE users SET credits = ? WHERE id = ?")->execute([$newBalance, $userId]);

    $db->prepare(
        "INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, package_id)
         VALUES (?, 'purchase', ?, ?, ?, ?)"
    )->execute([
        $userId,
        (int)$pkg['task_count'],
        $newBalance,
        "Purchased: {$pkg['name']} ({$pkg['task_count']} credits) — {$pkg['price_birr']} Birr",
        (int)$pkg['id'],
    ]);

    $db->commit();
    apiSuccess(['credits' => $newBalance, 'added' => $pkg['task_count']], "Purchase successful! {$pkg['task_count']} credits added.");
} catch (Exception $e) {
    $db->rollBack();
    apiError('Purchase failed', 500);
}
