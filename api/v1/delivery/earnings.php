<?php
/**
 * GET — Delivery agent earnings dashboard
 * Returns: weekly deliveries, total ETB, bonus progress, all-time stats
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);
$auth = requireAuth([ROLE_DELIVERY_AGENT]);
$agentId = (int)$auth['sub'];
$db = getDB();

// Week boundaries (Monday–Sunday)
$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));

// Weekly deliveries (orders delivered this week by this agent)
$weeklyRow = $db->prepare(
    "SELECT COUNT(*) as deliveries,
            COALESCE(SUM(delivery_fee), 0) as delivery_earnings
     FROM orders
     WHERE delivery_agent_id = ?
       AND status IN ('delivered','completed','out_for_delivery','picked_up','on_the_way')
       AND DATE(COALESCE(assigned_at, created_at)) BETWEEN ? AND ?"
);
$weeklyRow->execute([$agentId, $monday, $sunday]);
$weekly = $weeklyRow->fetch();

// All-time stats
$allTime = $db->prepare(
    "SELECT COUNT(*) as total_deliveries,
            COALESCE(SUM(delivery_fee), 0) as total_delivery_earnings
     FROM orders
     WHERE delivery_agent_id = ? AND status IN ('delivered','completed')"
);
$allTime->execute([$agentId]);
$allTime = $allTime->fetch();

// Agent earnings log (from agent_earnings table if it exists)
$earningsLog = [];
try {
    $logStmt = $db->prepare(
        "SELECT ae.amount, ae.type, ae.note, ae.created_at, o.item_name
         FROM agent_earnings ae
         LEFT JOIN orders o ON o.id = ae.order_id
         WHERE ae.agent_id = ?
         ORDER BY ae.created_at DESC LIMIT 20"
    );
    $logStmt->execute([$agentId]);
    $earningsLog = $logStmt->fetchAll();
} catch (Exception $e) { $earningsLog = []; }

// Recent delivered orders this week
$recentOrders = $db->prepare(
    "SELECT o.id, o.item_name, o.quantity, o.delivery_fee, o.status,
            o.assigned_at, o.delivered_at,
            b.name as building_name
     FROM orders o
     JOIN buildings b ON b.id = o.building_id
     WHERE o.delivery_agent_id = ?
       AND DATE(COALESCE(o.assigned_at, o.created_at)) BETWEEN ? AND ?
     ORDER BY COALESCE(o.delivered_at, o.assigned_at) DESC"
);
$recentOrders->execute([$agentId, $monday, $sunday]);
$recentOrders = $recentOrders->fetchAll();

// PDF Bonus tiers: 100=1000ETB, 150=1500ETB, 200=2500ETB
$bonusTiers = [
    ['deliveries' => 100, 'bonus' => 1000, 'label' => 'Bronze'],
    ['deliveries' => 150, 'bonus' => 1500, 'label' => 'Silver'],
    ['deliveries' => 200, 'bonus' => 2500, 'label' => 'Gold'],
];
$weekDeliveries = (int)$weekly['deliveries'];
$nextBonus = null;
foreach ($bonusTiers as $tier) {
    if ($weekDeliveries < $tier['deliveries']) {
        $nextBonus = $tier;
        $nextBonus['progress'] = $weekDeliveries;
        $nextBonus['remaining'] = $tier['deliveries'] - $weekDeliveries;
        $nextBonus['pct'] = min(100, round(($weekDeliveries / $tier['deliveries']) * 100));
        break;
    }
}

// Already earned bonus this week?
$earnedBonuses = [];
foreach ($bonusTiers as $tier) {
    if ($weekDeliveries >= $tier['deliveries']) $earnedBonuses[] = $tier;
}

apiSuccess([
    'week' => [
        'start'             => $monday,
        'end'               => $sunday,
        'deliveries'        => $weekDeliveries,
        'delivery_earnings' => (float)$weekly['delivery_earnings'],
        'orders'            => $recentOrders,
    ],
    'all_time' => [
        'total_deliveries'       => (int)$allTime['total_deliveries'],
        'total_delivery_earnings'=> (float)$allTime['total_delivery_earnings'],
    ],
    'bonus' => [
        'tiers'          => $bonusTiers,
        'next'           => $nextBonus,
        'earned_this_week'=> $earnedBonuses,
    ],
    'earnings_log' => $earningsLog,
]);
