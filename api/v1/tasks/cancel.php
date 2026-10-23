<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Task.php';
require_once dirname(__DIR__, 3) . '/app/models/Notification.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$user = requireAuth([ROLE_RESIDENT, ROLE_RUNNER]);
$body = getJsonBody();
$err  = validateRequired($body, ['task_id']);
if ($err) apiError($err, 422);

$taskModel = new Task();
$userId    = (int)$user['sub'];
$taskId    = (int)$body['task_id'];

$task = $taskModel->findById($taskId);
if (!$task) apiError('Task not found', 404);

$ok = $taskModel->cancel($taskId, $userId);
if (!$ok) apiError('Cannot cancel this task', 409);

// Notify the other party
$notifModel = new Notification();
if ($user['role'] === 'resident' && $task['runner_id']) {
    $notifModel->create((int)$task['runner_id'], $taskId, NOTIF_CANCELLED, 'The resident cancelled the task.');
} elseif ($user['role'] === 'runner') {
    $notifModel->create((int)$task['resident_id'], $taskId, NOTIF_CANCELLED, 'The runner cancelled the task. You can re-post it.');
}

apiSuccess(null, 'Task cancelled');
