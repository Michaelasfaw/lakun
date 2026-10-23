<?php
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
$_SESSION = [];
session_destroy();

apiSuccess(null, 'Logged out successfully');
