<?php
/**
 * POST /api/v1/location/update-position.php
 * Body: { "latitude": 6.4281, "longitude": 3.4219 }
 *
 * Vendors and delivery agents can store their current/shop GPS.
 * Customers can also call this to verify their nearest building.
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/helpers/validation.php';
require_once dirname(__DIR__, 3) . '/app/services/LocationService.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Method not allowed', 405);

$auth = requireAuth();
$body = getJsonBody();
$err  = validateRequired($body, ['latitude', 'longitude']);
if ($err) apiError($err, 422);

$lat = (float)$body['latitude'];
$lon = (float)$body['longitude'];

if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    apiError('Invalid coordinates', 422);
}

$db = getDB();

// Save to user record
$db->prepare('UPDATE users SET latitude = ?, longitude = ? WHERE id = ?')
   ->execute([$lat, $lon, (int)$auth['sub']]);

// If vendor, find the nearest building they're within radius of
$nearestBuilding = null;
if ($auth['role'] === ROLE_VENDOR) {
    $nearestBuilding = LocationService::getNearestBuilding($db, $lat, $lon);
}

// For any role, return nearest building for UX confirmation
if (!$nearestBuilding) {
    $nearestBuilding = LocationService::getNearestBuilding($db, $lat, $lon);
}

apiSuccess([
    'saved'            => true,
    'latitude'         => $lat,
    'longitude'        => $lon,
    'nearest_building' => $nearestBuilding ? [
        'id'             => $nearestBuilding['id'],
        'name'           => $nearestBuilding['name'],
        'distance_label' => $nearestBuilding['distance_label'],
    ] : null,
], 'Location updated');
