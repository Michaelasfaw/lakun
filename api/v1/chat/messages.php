<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/models/Chat.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

$user      = requireAuth();
$taskId    = (int)($_GET['task_id'] ?? 0);
$sinceId   = (int)($_GET['since_id'] ?? 0);

if (!$taskId) apiError('task_id required', 422);

$chatModel = new Chat();
if (!$chatModel->canChat($taskId, (int)$user['sub'])) {
    apiError('Chat not available for this task', 403);
}

$chatModel->markRead($taskId, (int)$user['sub']);
$messages = $chatModel->getMessages($taskId, $sinceId);

apiSuccess(['messages' => $messages], 'Messages retrieved');
