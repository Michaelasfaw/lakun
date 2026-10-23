<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/User.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$user = requireAuth();
$body = getJsonBody();

$update = [];
if (isset($body['name']))        $update['name']        = sanitizeString($body['name'], 100);
if (isset($body['unit_number'])) $update['unit_number'] = sanitizeString($body['unit_number'], 20);
if (isset($body['building_id'])) $update['building_id'] = (int)$body['building_id'];

if (empty($update)) apiError('No fields to update', 422);

$userModel = new User();
$userModel->updateProfile((int)$user['sub'], $update);

apiSuccess(null, 'Profile updated');
