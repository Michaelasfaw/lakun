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

$user = requireAuth([ROLE_RUNNER]);
$body = getJsonBody();
$err  = validateRequired($body, ['task_id']);
if ($err) apiError($err, 422);

$taskModel = new Task();
$runnerId  = (int)$user['sub'];
$taskId    = (int)$body['task_id'];

$task = $taskModel->findById($taskId);
if (!$task || (int)$task['runner_id'] !== $runnerId) {
    apiError('Task not found or not assigned to you', 404);
}

$ok = $taskModel->markRunnerDone($taskId, $runnerId);
if (!$ok) apiError('Cannot mark task as done at this stage', 409);

// Notify resident
$notifModel = new Notification();
$notifModel->create(
    (int)$task['resident_id'],
    $taskId,
    NOTIF_RUNNER_DONE,
    "Your runner has completed the task! Please confirm to close it. ✅"
);

apiSuccess(null, 'Marked as done. Waiting for resident confirmation.');
