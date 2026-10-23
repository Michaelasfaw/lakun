<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Chat.php';
require_once dirname(__DIR__, 3) . '/app/models/Notification.php';
require_once dirname(__DIR__, 3) . '/app/models/Task.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$user = requireAuth();
$body = getJsonBody();
$err  = validateRequired($body, ['task_id', 'message']);
if ($err) apiError($err, 422);

$taskId   = (int)$body['task_id'];
$senderId = (int)$user['sub'];
$message  = sanitizeString($body['message'], 500);

if (empty($message)) apiError('Message cannot be empty', 422);

$chatModel = new Chat();
if (!$chatModel->canChat($taskId, $senderId)) {
    apiError('Chat not available for this task', 403);
}

$msgId = $chatModel->send($taskId, $senderId, $message);

// Notify the other party
$taskModel = new Task();
$task = $taskModel->findById($taskId);
if ($task) {
    $otherId = $senderId === (int)$task['resident_id'] ? $task['runner_id'] : $task['resident_id'];
    if ($otherId) {
        $notifModel = new Notification();
        $notifModel->create(
            (int)$otherId, $taskId, NOTIF_NEW_MESSAGE,
            $user['name'] . ': ' . mb_substr($message, 0, 60)
        );
    }
}

apiSuccess(['id' => $msgId], 'Message sent', 201);
