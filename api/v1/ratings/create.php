<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Rating.php';
require_once dirname(__DIR__, 3) . '/app/models/Task.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$user = requireAuth([ROLE_RESIDENT]);
$body = getJsonBody();
$err  = validateRequired($body, ['task_id', 'score']);
if ($err) apiError($err, 422);

$score = (int)$body['score'];
if ($score < 1 || $score > 5) apiError('Score must be between 1 and 5', 422);

$taskId     = (int)$body['task_id'];
$residentId = (int)$user['sub'];

$taskModel = new Task();
$task = $taskModel->findById($taskId);
if (!$task || $task['status'] !== 'completed') apiError('Task not found or not completed', 404);
if ((int)$task['resident_id'] !== $residentId) apiError('Not your task', 403);
if (!$task['runner_id']) apiError('No runner assigned', 400);

$ratingModel = new Rating();
$ok = $ratingModel->create(
    $taskId,
    $residentId,
    (int)$task['runner_id'],
    $score,
    sanitizeString($body['comment'] ?? '', 300)
);

if (!$ok) apiError('Rating already submitted for this task', 409);

apiSuccess(null, 'Rating submitted. Thank you!', 201);
