<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

requireAuth([ROLE_ADMIN]);
$db = getDB();

// ── User stats ─────────────────────────────────────────────────────────────
$userStats = $db->query(
    "SELECT COUNT(*) as total,
            SUM(role='customer')        as customers,
            SUM(role='vendor')          as vendors,
            SUM(role='delivery_agent')  as agents,
            SUM(role='vendor' AND is_active=1)         as vendors_active,
            SUM(role='delivery_agent' AND is_active=1) as agents_active,
            SUM(is_active=0)            as inactive
     FROM users WHERE role != 'admin'"
)->fetch();

// ── Order stats — full PDF lifecycle ──────────────────────────────────────
$orderStats = $db->query(
    "SELECT
        COUNT(*)                                           as total,
        SUM(status IN ('requested','sent_to_vendors','bids_received','sent_to_customer')) as pending,
        SUM(status='payment_under_review')                 as payment_pending,
        SUM(status IN ('assigned','out_for_delivery','picked_up','on_the_way')) as active_deliveries,
        SUM(status IN ('delivered','completed'))           as completed,
        SUM(status='cancelled')                            as cancelled,
        SUM(status='expired')                              as expired,
        SUM(status='failed_payment')                       as failed_payment,
        -- Legacy compat
        SUM(status IN ('bidding','sent_to_vendors'))       as bidding,
        SUM(status IN ('confirmed','awaiting_confirmation','awaiting_payment')) as confirmed,
        SUM(status IN ('assigned','out_for_delivery','picked_up','on_the_way')) as delivering,
        SUM(status IN ('delivered','completed') AND DATE(COALESCE(delivered_at, completed_at, created_at))=CURDATE()) as delivered,
        SUM(status IN ('delivered','completed'))           as delivered_total,
        -- Revenue: sum of confirmed/assigned/delivered/completed orders
        COALESCE(SUM(CASE WHEN status IN ('confirmed','assigned','out_for_delivery','delivered','completed','payment_under_review')
            AND total_price IS NOT NULL THEN total_price ELSE 0 END), 0) as revenue_total
     FROM orders"
)->fetch();

// ── Recent orders ──────────────────────────────────────────────────────────
$recentOrders = $db->query(
    "SELECT o.id, o.item_name, o.quantity, o.status, o.created_at,
            o.total_price, o.category, o.is_fast_order,
            o.payment_uploaded_at, o.payment_verified_at,
            b.name as building_name,
            u.name as customer_name
     FROM orders o
     JOIN buildings b ON b.id = o.building_id
     JOIN users u ON u.id = o.customer_id
     ORDER BY o.created_at DESC LIMIT 20"
)->fetchAll();

// ── Credit stats for agents ───────────────────────────────────────────────
$creditStats = null;
try {
    $creditStats = $db->query(
        "SELECT
            SUM(credits) as total_credits,
            COUNT(CASE WHEN credits = 0 THEN 1 END)  as agents_no_credits,
            COUNT(CASE WHEN credits > 0 THEN 1 END)  as agents_with_credits
         FROM users WHERE role = 'delivery_agent'"
    )->fetch();
} catch (Exception $e) { $creditStats = null; }

// ── Low credit agents (< 3) ───────────────────────────────────────────────
$lowCreditAgents = [];
try {
    $lowCreditAgents = $db->query(
        "SELECT id, name, phone, credits FROM users
         WHERE role = 'delivery_agent' AND credits < 3
         ORDER BY credits ASC LIMIT 5"
    )->fetchAll();
} catch (Exception $e) { $lowCreditAgents = []; }

// ── Orders awaiting payment upload (customer not uploaded yet) ─────────────
$awaitingPayment = $db->query(
    "SELECT o.id, o.item_name, o.total_price, o.created_at,
            u.name as customer_name, b.name as building_name
     FROM orders o
     JOIN users u ON u.id = o.customer_id
     JOIN buildings b ON b.id = o.building_id
     WHERE o.status = 'awaiting_payment'
     ORDER BY o.created_at ASC LIMIT 5"
)->fetchAll();

// ── Orders with payment proof uploaded (awaiting admin verification) ───────
$pendingVerification = $db->query(
    "SELECT o.id, o.item_name, o.total_price, o.payment_uploaded_at,
            o.payment_txn_id, o.payment_sender_name, o.payment_method,
            u.name as customer_name, b.name as building_name
     FROM orders o
     JOIN users u ON u.id = o.customer_id
     JOIN buildings b ON b.id = o.building_id
     WHERE o.status = 'payment_under_review'
     ORDER BY o.payment_uploaded_at ASC LIMIT 10"
)->fetchAll();

apiSuccess([
    'users'                => $userStats,
    'orders'               => $orderStats,
    'recent_orders'        => $recentOrders,
    'credit_stats'         => $creditStats,
    'low_credit_agents'    => $lowCreditAgents,
    'awaiting_payment'     => $awaitingPayment,
    'pending_verification' => $pendingVerification,
], 'Stats retrieved');
