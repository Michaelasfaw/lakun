<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);
requireAuth([ROLE_ADMIN]);

$buildingId = (int)($_GET['building_id'] ?? 0);
if (!$buildingId) apiError('building_id required', 422);

$db = getDB();

// Get building info (total_floors, name)
$bldg = $db->prepare("SELECT id, name, total_floors FROM buildings WHERE id = ?");
$bldg->execute([$buildingId]);
$bldg = $bldg->fetch();
if (!$bldg) apiError('Building not found', 404);

// Distinct blocks already used in this building
$blockRows = $db->prepare(
    "SELECT DISTINCT block FROM users
     WHERE building_id = ? AND block IS NOT NULL AND block != ''
     ORDER BY block ASC"
);
$blockRows->execute([$buildingId]);
$blocks = $blockRows->fetchAll(PDO::FETCH_COLUMN);

// Distinct floors already used in this building
$floorRows = $db->prepare(
    "SELECT DISTINCT floor FROM users
     WHERE building_id = ? AND floor IS NOT NULL AND floor != ''
     ORDER BY CAST(floor AS UNSIGNED) ASC, floor ASC"
);
$floorRows->execute([$buildingId]);
$usedFloors = $floorRows->fetchAll(PDO::FETCH_COLUMN);

// Distinct house numbers already used (to show taken units)
$houseRows = $db->prepare(
    "SELECT DISTINCT house_no FROM users
     WHERE building_id = ? AND house_no IS NOT NULL AND house_no != ''
     ORDER BY house_no ASC"
);
$houseRows->execute([$buildingId]);
$takenUnits = $houseRows->fetchAll(PDO::FETCH_COLUMN);

// Generate floor list from total_floors (1..n) + any existing floors above that
$totalFloors = max((int)$bldg['total_floors'], count($usedFloors) > 0 ? (int)max($usedFloors) : 0);
$floors = [];
for ($i = 1; $i <= max($totalFloors, 1); $i++) {
    $floors[] = (string)$i;
}
// Merge any outlier floors not in the range
foreach ($usedFloors as $f) {
    if (!in_array($f, $floors)) $floors[] = $f;
}

// Default blocks if none exist yet
if (empty($blocks)) {
    $blocks = ['A', 'B', 'C', 'D'];
}

apiSuccess([
    'building'    => $bldg,
    'blocks'      => $blocks,
    'floors'      => $floors,
    'taken_units' => $takenUnits,
    'total_floors'=> $totalFloors,
]);
