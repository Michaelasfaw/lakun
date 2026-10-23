<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

requireAuth([ROLE_ADMIN]);
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $db->query("SELECT * FROM credit_packages ORDER BY sort_order, id")->fetchAll();
    apiSuccess(['packages' => $rows]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = getJsonBody();
    $action = $body['action'] ?? 'create';

    if ($action === 'create') {
        $err = validateRequired($body, ['name', 'price_birr', 'task_count']);
        if ($err) apiError($err, 422);

        $stmt = $db->prepare(
            "INSERT INTO credit_packages (name, description, price_birr, task_count, sort_order)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            trim($body['name']),
            trim($body['description'] ?? ''),
            (float)$body['price_birr'],
            (int)$body['task_count'],
            (int)($body['sort_order'] ?? 0),
        ]);
        $pkg = $db->query("SELECT * FROM credit_packages WHERE id = " . $db->lastInsertId())->fetch();
        apiSuccess(['package' => $pkg], 'Package created', 201);
    }

    if ($action === 'update') {
        $err = validateRequired($body, ['id']);
        if ($err) apiError($err, 422);

        $fields = [];
        $params = [];
        foreach (['name','description','price_birr','task_count','sort_order','is_active'] as $f) {
            if (isset($body[$f])) { $fields[] = "$f = ?"; $params[] = $body[$f]; }
        }
        if (!$fields) apiError('Nothing to update', 422);
        $params[] = (int)$body['id'];
        $db->prepare("UPDATE credit_packages SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
        apiSuccess(null, 'Package updated');
    }

    if ($action === 'delete') {
        $err = validateRequired($body, ['id']);
        if ($err) apiError($err, 422);
        $db->prepare("DELETE FROM credit_packages WHERE id = ?")->execute([(int)$body['id']]);
        apiSuccess(null, 'Package deleted');
    }

    apiError('Unknown action', 422);
}

apiError('Method not allowed', 405);
