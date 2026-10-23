<?php
/**
 * GET /api/v1/location/nearby-vendors.php?building_id=1
 *       (or ?lat=6.42&lng=3.42&radius=2 for raw coordinate lookup)
 *
 * Returns active vendors reachable from the given building:
 *   • Vendors whose shop GPS is within the building's service_radius_km
 *   • Vendors manually assigned to that building (vendor_buildings)
 *
 * Sorted by distance ascending.  No auth required (public discovery).
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/services/LocationService.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);

$db = getDB();

// ── Option A: by building ────────────────────────────────────────────────────
if (!empty($_GET['building_id'])) {
    $buildingId = (int)$_GET['building_id'];
    $vendors    = LocationService::getVendorsForBuilding($db, $buildingId);
    apiSuccess($vendors, 'Vendors retrieved');
}

// ── Option B: raw lat/lng radius search ─────────────────────────────────────
$lat    = isset($_GET['lat'])    ? (float)$_GET['lat']    : null;
$lon    = isset($_GET['lng'])    ? (float)$_GET['lng']    : null;
$radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 2.0;

if ($lat === null || $lon === null) apiError('Provide building_id or lat+lng', 422);

// Find nearest building, then get its vendors
$building = LocationService::getNearestBuilding($db, $lat, $lon);
if (!$building) apiError('No buildings found nearby', 404);

$vendors = LocationService::getVendorsForBuilding($db, (int)$building['id']);

apiSuccess([
    'building' => $building,
    'vendors'  => $vendors,
], 'Vendors retrieved');
