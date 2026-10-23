<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

requireAuth([ROLE_ADMIN]);
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$limit  = min(100, (int)($_GET['limit'] ?? 50));
$params = [];
$where  = '';
if ($userId) { $where = 'WHERE ct.user_id = ?'; $params[] = $userId; }

try {
    $stmt = $db->prepare(
        "SELECT ct.*, u.name as user_name, u.role as user_role, a.name as admin_name
         FROM credit_transactions ct
         JOIN users u ON u.id = ct.user_id
         LEFT JOIN users a ON a.id = ct.admin_id
         $where
         ORDER BY ct.created_at DESC
         LIMIT $limit"
    );
    $stmt->execute($params);
    $txs = $stmt->fetchAll();
    apiSuccess(['transactions' => $txs]);
} catch (Exception $e) {
    apiSuccess(['transactions' => []]);
}
