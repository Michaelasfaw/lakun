<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);

$authUser = requireAuth([ROLE_DELIVERY_AGENT]);
$db = getDB();

$user = $db->prepare("SELECT credits FROM users WHERE id = ?")->execute([(int)$authUser['sub']]);
$user = $db->prepare("SELECT credits FROM users WHERE id = ?");
$user->execute([(int)$authUser['sub']]);
$row = $user->fetch();

$txStmt = $db->prepare(
    "SELECT ct.*, u.name as admin_name
     FROM credit_transactions ct
     LEFT JOIN users u ON u.id = ct.admin_id
     WHERE ct.user_id = ?
     ORDER BY ct.created_at DESC LIMIT 20"
);
$txStmt->execute([(int)$authUser['sub']]);
$transactions = $txStmt->fetchAll();

apiSuccess([
    'credits'      => (int)($row['credits'] ?? 0),
    'transactions' => $transactions,
]);
