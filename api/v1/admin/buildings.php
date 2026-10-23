<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/models/Building.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

requireAuth([ROLE_ADMIN]);
$buildingModel = new Building();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    apiSuccess($buildingModel->getAll(), 'Buildings retrieved');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = getJsonBody();
    $err  = validateRequired($body, ['name']);
    if ($err) apiError($err, 422);
    $id = $buildingModel->create([
        'name'              => sanitizeString($body['name'], 100),
        'address'           => sanitizeString($body['address'] ?? '', 255),
        'latitude'          => isset($body['latitude'])          ? (float)$body['latitude']          : null,
        'longitude'         => isset($body['longitude'])         ? (float)$body['longitude']         : null,
        'service_radius_km' => isset($body['service_radius_km']) ? (float)$body['service_radius_km'] : 2.00,
        'total_floors'      => (int)($body['total_floors'] ?? 1),
    ]);
    apiSuccess(['id' => $id], 'Building created', 201);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body = getJsonBody();
    $err  = validateRequired($body, ['id', 'name']);
    if ($err) apiError($err, 422);
    $buildingModel->update((int)$body['id'], [
        'name'              => sanitizeString($body['name'], 100),
        'address'           => sanitizeString($body['address'] ?? '', 255),
        'latitude'          => isset($body['latitude'])          ? (float)$body['latitude']          : null,
        'longitude'         => isset($body['longitude'])         ? (float)$body['longitude']         : null,
        'service_radius_km' => isset($body['service_radius_km']) ? (float)$body['service_radius_km'] : 2.00,
        'total_floors'      => (int)($body['total_floors'] ?? 1),
    ]);
    apiSuccess(null, 'Building updated');
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $body = getJsonBody();
    if (!isset($body['id'])) apiError('id required', 422);
    $buildingModel->delete((int)$body['id']);
    apiSuccess(null, 'Building deleted');
}

apiError('Method not allowed', 405);
