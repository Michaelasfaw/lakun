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

$user = requireAuth([ROLE_RESIDENT]);
$body = getJsonBody();
$err  = validateRequired($body, ['task_id']);
if ($err) apiError($err, 422);

$taskModel  = new Task();
$residentId = (int)$user['sub'];
$taskId     = (int)$body['task_id'];

$task = $taskModel->findById($taskId);
if (!$task || (int)$task['resident_id'] !== $residentId) {
    apiError('Task not found', 404);
}

$ok = $taskModel->confirmByResident($taskId, $residentId);
if (!$ok) apiError('Cannot confirm at this stage', 409);

// Notify runner
if ($task['runner_id']) {
    $notifModel = new Notification();
    $notifModel->create(
        (int)$task['runner_id'],
        $taskId,
        NOTIF_COMPLETED,
        "Task completed and confirmed! Great work 🎉"
    );
}

apiSuccess(null, 'Task confirmed and closed!');
