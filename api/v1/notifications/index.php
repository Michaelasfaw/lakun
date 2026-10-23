<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/models/Notification.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

$user        = requireAuth();
$notifModel  = new Notification();
$userId      = (int)$user['sub'];

$notifications = $notifModel->getForUser($userId, 30);
$unread        = $notifModel->getUnreadCount($userId);

apiSuccess([
    'notifications' => $notifications,
    'unread_count'  => $unread,
], 'Notifications retrieved');
