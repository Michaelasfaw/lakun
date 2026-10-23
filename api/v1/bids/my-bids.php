<?php
/**
 * GET — Returns all bids submitted by the authenticated vendor,
 * with order details and computed bid outcome (won/lost/pending).
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);

$auth = requireAuth([ROLE_VENDOR]);
$pdo  = getDB();

$stmt = $pdo->prepare(
    'SELECT b.id, b.order_id, b.price, b.available, b.is_selected, b.submitted_at AS created_at,
            o.item_name, o.quantity, o.status AS order_status,
            o.category, o.is_fast_order,
            bld.name AS building_name
     FROM bids b
     JOIN orders o   ON b.order_id    = o.id
     JOIN buildings bld ON o.building_id = bld.id
     WHERE b.vendor_id = ?
     ORDER BY b.submitted_at DESC
     LIMIT 50'
);
$stmt->execute([$auth['sub']]);
$bids = $stmt->fetchAll();

// Statuses where the bid race is still open (not yet decided)
$pendingStatuses = ['bidding', 'sent_to_vendors', 'bids_received', 'requested'];
// Statuses that mean a winner has been chosen (active/post-selection)
$wonStatuses = ['sent_to_customer', 'awaiting_payment', 'payment_under_review',
                'confirmed', 'assigned', 'out_for_delivery', 'picked_up',
                'on_the_way', 'delivered', 'completed', 'awaiting_confirmation'];

foreach ($bids as &$bid) {
    if ($bid['is_selected'] && in_array($bid['order_status'], $wonStatuses)) {
        $bid['outcome'] = 'won';
    } elseif (!$bid['is_selected'] && in_array($bid['order_status'], $wonStatuses)) {
        $bid['outcome'] = 'lost';
    } elseif ($bid['order_status'] === 'cancelled' || $bid['order_status'] === 'expired') {
        $bid['outcome'] = 'cancelled';
    } else {
        $bid['outcome'] = 'pending';
    }
}
unset($bid);

apiSuccess($bids);
