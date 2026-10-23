<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Task.php';
require_once dirname(__DIR__, 3) . '/app/models/User.php';
require_once dirname(__DIR__, 3) . '/app/models/Notification.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$user = requireAuth([ROLE_RESIDENT]);
$body = getJsonBody();

$err = validateRequired($body, ['title', 'category']);
if ($err) apiError($err, 422);

if (!validateEnum($body['category'], ['grocery', 'package', 'document', 'food', 'other'])) {
    apiError('Invalid category', 422);
}

$userModel = new User();
$resident  = $userModel->findById((int)$user['sub']);
if (!$resident) apiError('User not found', 404);

$taskModel = new Task();
$taskId = $taskModel->create([
    'title'       => sanitizeString($body['title'], 150),
    'description' => sanitizeString($body['description'] ?? '', 1000),
    'category'    => $body['category'],
    'urgency'     => in_array($body['urgency'] ?? '', ['normal', 'urgent']) ? $body['urgency'] : 'normal',
    'tip'         => isset($body['tip']) ? sanitizeString($body['tip'], 100) : null,
    'resident_id' => $resident['id'],
    'building_id' => $resident['building_id'],
    'unit_number' => $resident['unit_number'],
]);

// Notify all runners in this building
$notifModel = new Notification();
$urgencyLabel = ($body['urgency'] ?? 'normal') === 'urgent' ? '🔴 URGENT' : '📋';
$notifModel->notifyRunnersInBuilding(
    $resident['building_id'],
    $taskId,
    "$urgencyLabel New task in your building: " . sanitizeString($body['title'], 60)
);

apiSuccess(['task_id' => $taskId], 'Task created successfully', 201);
