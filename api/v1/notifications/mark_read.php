<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Notification.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$user       = requireAuth();
$body       = getJsonBody();
$notifModel = new Notification();
$userId     = (int)$user['sub'];

// Mark specific or all
$notifId = isset($body['id']) ? (int)$body['id'] : null;
$notifModel->markRead($userId, $notifId);

apiSuccess(null, 'Marked as read');
