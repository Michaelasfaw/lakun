<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/User.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/rate_limit.php';

rateLimit(20, 60);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$body = getJsonBody();
$error = validateRequired($body, ['phone', 'password']);
if ($error) apiError($error, 422);

$phone    = sanitizeString($body['phone'], 20);
$password = $body['password'];

$userModel = new User();
$user = $userModel->findByPhone($phone);

if (!$user || !password_verify($password, $user['password_hash'])) {
    apiError('Invalid phone number or password', 401);
}
if (!$user['is_active']) {
    apiError('Account has been deactivated. Contact support.', 403);
}

$payload = [
    'sub'  => $user['id'],
    'role' => $user['role'],
    'name' => $user['name'],
    'building_id' => $user['building_id'],
];
$token = jwtEncode($payload, JWT_SECRET);

// Set PHP session for web views
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
$_SESSION['user'] = [
    'id'          => $user['id'],
    'name'        => $user['name'],
    'role'        => $user['role'],
    'building_id' => $user['building_id'],
    'block'       => $user['block'],
    'floor'       => $user['floor'],
    'house_no'    => $user['house_no'],
    'shop_name'   => $user['shop_name'],
];

apiSuccess([
    'user'  => [
        'id'          => $user['id'],
        'name'        => $user['name'],
        'role'        => $user['role'],
        'building_id' => $user['building_id'],
        'shop_name'   => $user['shop_name'],
    ],
    'token' => $token,
], 'Login successful');
