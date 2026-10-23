<?php
/**
 * GET /api/v1/location/nearby-buildings.php?lat=6.42&lng=3.42&radius=10
 *
 * Returns all buildings sorted by distance from the supplied coordinates.
 * `radius` (km) caps results; defaults to 10 km.  No auth required.
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/services/LocationService.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);

$lat    = isset($_GET['lat'])    ? (float)$_GET['lat']    : null;
$lon    = isset($_GET['lng'])    ? (float)$_GET['lng']    : null;
$radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 10.0;

if ($lat === null || $lon === null) apiError('lat and lng are required', 422);
if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) apiError('Invalid coordinates', 422);

$db       = getDB();
$buildings = LocationService::getNearbyBuildings($db, $lat, $lon, $radius);

apiSuccess($buildings, 'Nearby buildings retrieved');
