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

// Check runner has no active job
$activeJob = $taskModel->getActiveForRunner($runnerId);
if ($activeJob) {
    apiError('You already have an active job. Complete it first.', 409);
}

$result = $taskModel->accept((int)$body['task_id'], $runnerId);

if (!$result['success']) {
    apiError($result['message'] ?? 'Could not accept task', 409);
}

// Notify resident
$notifModel = new Notification();
$notifModel->create(
    $result['resident_id'],
    (int)$body['task_id'],
    NOTIF_ACCEPTED,
    "A runner has accepted your task and is on the way! 🏃"
);

$task = $taskModel->findById((int)$body['task_id']);
apiSuccess(['task' => $task], 'Job accepted! Head to the resident.');
