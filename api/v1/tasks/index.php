<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/models/Task.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

$user = requireAuth();

$taskModel = new Task();

if ($user['role'] === 'runner') {
    if (!empty($_GET['building_id'])) {
        // Open task feed for a specific building
        $buildingId = (int)$_GET['building_id'];
        $tasks = $taskModel->getOpenTasksForBuilding($buildingId);
        // Strip unit number until accepted
        foreach ($tasks as &$t) {
            unset($t['unit_number'], $t['resident_name']);
        }
        apiSuccess($tasks, 'Open tasks retrieved');
    } else {
        // Runner's own history + active task
        $tasks = $taskModel->getTasksForRunner((int)$user['sub']);
        apiSuccess($tasks, 'Your tasks retrieved');
    }
} elseif ($user['role'] === 'resident') {
    $tasks = $taskModel->getTasksForResident((int)$user['sub']);
    apiSuccess($tasks, 'Your tasks retrieved');
} else {
    apiError('Unexpected role', 403);
}
