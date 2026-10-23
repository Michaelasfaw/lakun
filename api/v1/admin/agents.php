<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);

$auth = requireAuth([ROLE_ADMIN]);
$buildingId = (int)($_GET['building_id'] ?? 0);

$pdo = getDB();
if ($buildingId) {
    $stmt = $pdo->prepare(
        'SELECT u.id, u.name, u.phone, u.credits FROM users u
         JOIN agent_buildings ab ON u.id = ab.agent_id
         WHERE ab.building_id = ? AND u.is_active = 1 AND u.role = "delivery_agent"
         ORDER BY u.credits DESC, u.name'
    );
    $stmt->execute([$buildingId]);
} else {
    $stmt = $pdo->prepare(
        'SELECT id, name, phone, credits FROM users
         WHERE role = "delivery_agent" AND is_active = 1
         ORDER BY credits DESC, name'
    );
    $stmt->execute();
}

apiSuccess($stmt->fetchAll());
