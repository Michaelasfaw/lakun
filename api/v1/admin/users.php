<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/User.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

$admin = requireAuth([ROLE_ADMIN]);
$db    = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Single user detail
    if (isset($_GET['id'])) {
        $stmt = $db->prepare(
            "SELECT u.id, u.name, u.phone, u.role, u.building_id, u.block, u.floor, u.house_no,
                    u.shop_name, u.shop_location, u.is_active, u.credits, u.created_at,
                    b.name as building_name
             FROM users u
             LEFT JOIN buildings b ON b.id = u.building_id
             WHERE u.id = ?"
        );
        $stmt->execute([(int)$_GET['id']]);
        $user = $stmt->fetch();
        if (!$user) apiError('User not found', 404);

        // Recent credit transactions for agents
        $tx = [];
        if ($user['role'] === ROLE_DELIVERY_AGENT) {
            $txStmt = $db->prepare(
                "SELECT ct.*, a.name as admin_name
                 FROM credit_transactions ct
                 LEFT JOIN users a ON a.id = ct.admin_id
                 WHERE ct.user_id = ?
                 ORDER BY ct.created_at DESC LIMIT 10"
            );
            $txStmt->execute([(int)$_GET['id']]);
            $tx = $txStmt->fetchAll();
        }
        apiSuccess(['user' => $user, 'transactions' => $tx]);
    }

    // List users
    $role   = $_GET['role'] ?? null;
    $search = $_GET['search'] ?? null;
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = 25;
    $offset = ($page - 1) * $limit;

    $where  = ["u.role != 'admin'"];
    $params = [];
    if ($role && $role !== 'all') { $where[] = 'u.role = ?'; $params[] = $role; }
    if ($search) {
        $where[] = "(u.name LIKE ? OR u.phone LIKE ?)";
        $like = "%$search%";
        $params[] = $like;
        $params[] = $like;
    }

    $whereStr = implode(' AND ', $where);
    $countStmt = $db->prepare("SELECT COUNT(*) FROM users u WHERE $whereStr");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $params[] = $limit;
    $params[] = $offset;
    $stmt = $db->prepare(
        "SELECT u.id, u.name, u.phone, u.role, u.building_id, u.block, u.floor, u.house_no,
                u.shop_name, u.is_active, u.credits, u.created_at,
                b.name as building_name
         FROM users u
         LEFT JOIN buildings b ON b.id = u.building_id
         WHERE $whereStr
         ORDER BY u.created_at DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    // Role counts
    $counts = $db->query(
        "SELECT role, COUNT(*) as cnt FROM users WHERE role != 'admin' GROUP BY role"
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    apiSuccess([
        'users'  => $users,
        'total'  => $total,
        'page'   => $page,
        'pages'  => max(1, ceil($total / $limit)),
        'counts' => $counts,
    ], 'Users retrieved');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = getJsonBody();
    $action = $body['action'] ?? '';

    if ($action === 'toggle_active') {
        $err = validateRequired($body, ['user_id']);
        if ($err) apiError($err, 422);
        $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([(int)$body['user_id']]);
        apiSuccess(null, 'User status updated');
    }

    if ($action === 'update_user') {
        $err = validateRequired($body, ['user_id']);
        if ($err) apiError($err, 422);
        $uid = (int)$body['user_id'];
        $fields = []; $params = [];
        foreach (['name', 'phone', 'role', 'block', 'floor', 'house_no', 'shop_name', 'shop_location', 'building_id'] as $f) {
            if (array_key_exists($f, $body)) {
                $fields[] = "$f = ?";
                $params[] = $body[$f] === '' ? null : $body[$f];
            }
        }
        if (!$fields) apiError('Nothing to update', 422);
        $params[] = $uid;
        $db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
        apiSuccess(null, 'User updated');
    }

    if ($action === 'reset_password') {
        $err = validateRequired($body, ['user_id', 'password']);
        if ($err) apiError($err, 422);
        if (strlen($body['password']) < 6) apiError('Password must be at least 6 characters', 422);
        $hash = password_hash($body['password'], PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, (int)$body['user_id']]);
        apiSuccess(null, 'Password reset successfully');
    }

    if ($action === 'create_user') {
        $err = validateRequired($body, ['name', 'phone', 'role', 'password']);
        if ($err) apiError($err, 422);
        // Check phone unique
        $exists = $db->prepare("SELECT id FROM users WHERE phone = ?");
        $exists->execute([trim($body['phone'])]);
        if ($exists->fetch()) apiError('Phone number already registered', 409);
        $hash = password_hash($body['password'], PASSWORD_BCRYPT);
        $db->prepare(
            "INSERT INTO users (name, phone, password_hash, role, building_id, block, floor, house_no, shop_name, shop_location)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            trim($body['name']),
            trim($body['phone']),
            $hash,
            $body['role'],
            $body['building_id'] ?: null,
            $body['block'] ?: null,
            $body['floor'] ?: null,
            $body['house_no'] ?: null,
            $body['shop_name'] ?: null,
            $body['shop_location'] ?: null,
        ]);
        apiSuccess(['id' => $db->lastInsertId()], 'User created', 201);
    }

    if ($action === 'delete_user') {
        $err = validateRequired($body, ['user_id']);
        if ($err) apiError($err, 422);
        $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([(int)$body['user_id']]);
        apiSuccess(null, 'User deleted');
    }

    apiError('Unknown action', 422);
}

apiError('Method not allowed', 405);
