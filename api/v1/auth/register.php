<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/User.php';
require_once dirname(__DIR__, 3) . '/app/models/Building.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/rate_limit.php';

rateLimit(10, 60);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$body = getJsonBody();
$error = validateRequired($body, ['name', 'phone', 'password', 'role']);
if ($error) apiError($error, 422);

$name  = sanitizeString($body['name'], 100);
$phone = sanitizeString($body['phone'], 20);
$pass  = $body['password'];
$role  = sanitizeString($body['role'], 20);

if (!validatePhone($phone)) apiError('Invalid phone number format', 422);
if (!validateEnum($role, ['customer', 'vendor', 'delivery_agent'])) apiError('Invalid role', 422);
if (strlen($pass) < 6) apiError('Password must be at least 6 characters', 422);

$userModel = new User();
if ($userModel->findByPhone($phone)) apiError('Phone number already registered', 409);

$data = ['name' => $name, 'phone' => $phone, 'password' => $pass, 'role' => $role];

if ($role === 'customer') {
    $err = validateRequired($body, ['building_id', 'block', 'floor', 'house_no']);
    if ($err) apiError($err, 422);
    $buildingModel = new Building();
    if (!$buildingModel->findById((int)$body['building_id'])) apiError('Building not found', 404);
    $data['building_id'] = (int)$body['building_id'];
    $data['block']    = sanitizeString($body['block'], 20);
    $data['floor']    = sanitizeString($body['floor'], 20);
    $data['house_no'] = sanitizeString($body['house_no'], 20);
}

if ($role === 'vendor') {
    $err = validateRequired($body, ['shop_name', 'shop_location']);
    if ($err) apiError($err, 422);
    $data['shop_name']     = sanitizeString($body['shop_name'], 100);
    $data['shop_location'] = sanitizeString($body['shop_location'], 255);
}

$userId = $userModel->create($data);

// Assign buildings served (vendor and delivery_agent)
if (in_array($role, ['vendor', 'delivery_agent']) && !empty($body['building_ids']) && is_array($body['building_ids'])) {
    foreach ($body['building_ids'] as $bid) {
        if ($role === 'vendor')          $userModel->addVendorBuilding($userId, (int)$bid);
        if ($role === 'delivery_agent')  $userModel->addAgentBuilding($userId, (int)$bid);
    }
}

$token = jwtEncode(['sub' => $userId, 'role' => $role, 'name' => $name], JWT_SECRET);

apiSuccess([
    'user'  => ['id' => $userId, 'name' => $name, 'role' => $role],
    'token' => $token,
], 'Registration successful', 201);
