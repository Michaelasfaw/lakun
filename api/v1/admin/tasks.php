<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/models/Task.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

requireAuth([ROLE_ADMIN]);
$taskModel = new Task();

$filters = [];
if (!empty($_GET['status']))   $filters['status']   = $_GET['status'];
if (!empty($_GET['building'])) $filters['building']  = (int)$_GET['building'];
$page  = (int)($_GET['page'] ?? 1);

$tasks = $taskModel->getAll($filters, $page, 25);
apiSuccess(['tasks' => $tasks], 'Tasks retrieved');
