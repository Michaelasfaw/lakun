<?php
/**
 * LocationService — Haversine-based geolocation helpers.
 *
 * All distances are in kilometres. Coordinates are WGS-84 decimal degrees.
 * No external dependencies — pure PHP math.
 */
class LocationService {

    private const EARTH_RADIUS_KM = 6371.0;

    // ─── Core Math ──────────────────────────────────────────────────────────

    /**
     * Haversine distance between two GPS points, in km.
     */
    public static function haversine(
        float $lat1, float $lon1,
        float $lat2, float $lon2
    ): float {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * asin(min(1, sqrt($a)));
    }

    /**
     * Human-readable distance string.
     *   < 1 km  → "350 m"
     *   >= 1 km → "1.4 km"
     */
    public static function formatDistance(float $km): string {
        if ($km < 1.0) {
            return round($km * 1000) . ' m';
        }
        return number_format($km, 1) . ' km';
    }

    // ─── Buildings ──────────────────────────────────────────────────────────

    /**
     * Return all buildings sorted by distance from (lat, lon).
     * Buildings without coordinates are appended at the end.
     *
     * @return array  Each row is the buildings DB row + 'distance_km' + 'distance_label'
     */
    public static function getNearbyBuildings(
        PDO $db,
        float $lat,
        float $lon,
        float $maxRadiusKm = 10.0
    ): array {
        $stmt = $db->query('SELECT * FROM buildings');
        $buildings = $stmt->fetchAll();

        $withDist = [];
        $noDist   = [];

        foreach ($buildings as $b) {
            if ($b['latitude'] !== null && $b['longitude'] !== null) {
                $d = self::haversine($lat, $lon, (float)$b['latitude'], (float)$b['longitude']);
                if ($d <= $maxRadiusKm) {
                    $b['distance_km']    = round($d, 3);
                    $b['distance_label'] = self::formatDistance($d);
                    $withDist[] = $b;
                }
            } else {
                $b['distance_km']    = null;
                $b['distance_label'] = null;
                $noDist[] = $b;
            }
        }

        usort($withDist, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);

        return array_merge($withDist, $noDist);
    }

    /**
     * Return the single nearest building to (lat, lon), or null if none found.
     */
    public static function getNearestBuilding(PDO $db, float $lat, float $lon): ?array {
        $results = self::getNearbyBuildings($db, $lat, $lon, 50.0);
        return $results[0] ?? null;
    }

    // ─── Vendors ────────────────────────────────────────────────────────────

    /**
     * Return all active vendors reachable from a building, combining:
     *   1. Vendors whose shop GPS is within the building's service_radius_km
     *   2. Vendors manually assigned to that building via vendor_buildings
     *
     * Each row includes 'distance_km', 'distance_label', 'source' ('gps'|'assigned').
     *
     * @return array  vendor user rows, deduplicated, sorted by distance
     */
    public static function getVendorsForBuilding(PDO $db, int $buildingId): array {
        // Building coordinates
        $bldgStmt = $db->prepare('SELECT * FROM buildings WHERE id = ?');
        $bldgStmt->execute([$buildingId]);
        $bldg = $bldgStmt->fetch();
        if (!$bldg) return [];

        $radius = (float)($bldg['service_radius_km'] ?? 2.0);

        // All active vendors
        $vStmt = $db->query(
            'SELECT u.id, u.name, u.phone, u.shop_name, u.shop_location,
                    u.latitude, u.longitude
             FROM users u
             WHERE u.role = "vendor" AND u.is_active = 1'
        );
        $allVendors = $vStmt->fetchAll();

        // Manually assigned vendor IDs
        $assignedStmt = $db->prepare('SELECT vendor_id FROM vendor_buildings WHERE building_id = ?');
        $assignedStmt->execute([$buildingId]);
        $assignedIds = array_column($assignedStmt->fetchAll(), 'vendor_id');

        $result = [];
        $seen   = [];

        foreach ($allVendors as $v) {
            if (isset($seen[$v['id']])) continue;

            $isAssigned  = in_array($v['id'], $assignedIds);
            $hasGps      = $v['latitude'] !== null && $v['longitude'] !== null;
            $bldgHasGps  = $bldg['latitude'] !== null && $bldg['longitude'] !== null;

            $dist   = null;
            $source = null;

            if ($hasGps && $bldgHasGps) {
                $dist = self::haversine(
                    (float)$bldg['latitude'], (float)$bldg['longitude'],
                    (float)$v['latitude'],    (float)$v['longitude']
                );
            }

            $withinRadius = ($dist !== null && $dist <= $radius);

            if ($withinRadius) {
                $source = 'gps';
            } elseif ($isAssigned) {
                $source = 'assigned';
                $dist   = 0.0; // same complex, treat as 0
            } else {
                continue; // not reachable
            }

            $v['distance_km']    = $dist !== null ? round($dist, 3) : null;
            $v['distance_label'] = $dist !== null ? self::formatDistance($dist) : 'same complex';
            $v['source']         = $source;
            $result[] = $v;
            $seen[$v['id']] = true;
        }

        usort($result, fn($a, $b) => ($a['distance_km'] ?? 0) <=> ($b['distance_km'] ?? 0));

        return $result;
    }

    /**
     * Return all vendor IDs reachable from a building (for notification targeting).
     */
    public static function getVendorIdsForBuilding(PDO $db, int $buildingId): array {
        $vendors = self::getVendorsForBuilding($db, $buildingId);
        return array_column($vendors, 'id');
    }

    // ─── Open Orders for Vendor ─────────────────────────────────────────────

    /**
     * Return open (bidding) orders that this vendor can see, based on proximity.
     * Falls back to manually assigned buildings if vendor has no GPS set.
     *
     * @param int        $vendorId
     * @param float|null $vendorLat   Vendor shop latitude  (null = use assigned buildings only)
     * @param float|null $vendorLon   Vendor shop longitude
     * @return array  order rows with 'distance_km' and 'distance_label' appended
     */
    public static function getOpenOrdersForVendor(
        PDO $db,
        int $vendorId,
        ?float $vendorLat = null,
        ?float $vendorLon = null
    ): array {
        $stmt = $db->prepare(
            'SELECT o.*, b.name AS building_name,
                    b.latitude AS bldg_lat, b.longitude AS bldg_lng,
                    b.service_radius_km AS bldg_radius,
                    TIMESTAMPDIFF(SECOND, NOW(), o.bid_window_ends_at) AS seconds_remaining,
                    (SELECT id FROM bids WHERE order_id = o.id AND vendor_id = :vid1) AS my_bid_id,
                    (SELECT 1 FROM vendor_buildings
                     WHERE vendor_id = :vid2 AND building_id = o.building_id) AS is_assigned
             FROM orders o
             JOIN buildings b ON o.building_id = b.id
             WHERE o.status = "bidding" AND o.bid_window_ends_at > NOW()
             ORDER BY o.created_at DESC'
        );
        $stmt->execute([':vid1' => $vendorId, ':vid2' => $vendorId]);
        $orders = $stmt->fetchAll();

        $result = [];

        foreach ($orders as $o) {
            $bldgLat = $o['bldg_lat'] !== null ? (float)$o['bldg_lat'] : null;
            $bldgLon = $o['bldg_lng'] !== null ? (float)$o['bldg_lng'] : null;
            $radius  = (float)($o['bldg_radius'] ?? 2.0);

            $dist    = null;
            $include = (bool)$o['is_assigned']; // always include assigned

            if ($vendorLat !== null && $vendorLon !== null && $bldgLat !== null && $bldgLon !== null) {
                $dist    = self::haversine($vendorLat, $vendorLon, $bldgLat, $bldgLon);
                $include = $include || ($dist <= $radius);
            }

            if (!$include) continue;

            $o['distance_km']    = $dist !== null ? round($dist, 2) : null;
            $o['distance_label'] = $dist !== null ? self::formatDistance($dist) : null;
            $result[] = $o;
        }

        // Sort: assigned / nearest first
        usort($result, fn($a, $b) => ($a['distance_km'] ?? 0) <=> ($b['distance_km'] ?? 0));

        return $result;
    }
}
