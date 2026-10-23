<?php
/**
 * GET — Admin financial report
 * Query params: period=daily|monthly|all, year=YYYY, month=MM
 */
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') apiError('Method not allowed', 405);
requireAuth([ROLE_ADMIN]);

$db     = getDB();
$period = $_GET['period'] ?? 'monthly';
$year   = (int)($_GET['year']  ?? date('Y'));
$month  = (int)($_GET['month'] ?? date('m'));

// Tax rate from settings
$taxRow  = $db->query("SELECT value FROM settings WHERE `key` = 'tax_rate_percent'")->fetch();
$taxRate = $taxRow ? (float)$taxRow['value'] : 15.0;

// ── Daily breakdown (current month) ──────────────────────────────────────
$dailyStmt = $db->prepare(
    "SELECT
        DATE(completed_at) AS day,
        COUNT(*) AS order_count,
        COALESCE(SUM(total_price),   0) AS gross_income,
        COALESCE(SUM(delivery_fee),  0) AS delivery_fees,
        COALESCE(SUM(tip_amount),    0) AS tips,
        COALESCE(SUM(item_price),    0) AS vendor_payouts
     FROM orders
     WHERE status IN ('completed','delivered')
       AND YEAR(completed_at) = ? AND MONTH(completed_at) = ?
     GROUP BY DATE(completed_at)
     ORDER BY day DESC"
);
$dailyStmt->execute([$year, $month]);
$dailyRows = $dailyStmt->fetchAll();

// Compute tax & net for each daily row
foreach ($dailyRows as &$row) {
    $platformIncome = (float)$row['delivery_fees'] + (float)$row['tips'];
    $row['platform_income'] = $platformIncome;
    $row['tax_amount']      = round($platformIncome * ($taxRate / 100), 2);
    $row['net_earnings']    = round($platformIncome - $row['tax_amount'], 2);
}
unset($row);

// ── Monthly summary (current year) ───────────────────────────────────────
$monthlyStmt = $db->prepare(
    "SELECT
        YEAR(completed_at) AS yr,
        MONTH(completed_at) AS mo,
        DATE_FORMAT(completed_at, '%b %Y') AS month_label,
        COUNT(*) AS order_count,
        COALESCE(SUM(total_price),  0) AS gross_income,
        COALESCE(SUM(delivery_fee), 0) AS delivery_fees,
        COALESCE(SUM(tip_amount),   0) AS tips,
        COALESCE(SUM(item_price),   0) AS vendor_payouts
     FROM orders
     WHERE status IN ('completed','delivered')
       AND YEAR(completed_at) = ?
     GROUP BY YEAR(completed_at), MONTH(completed_at)
     ORDER BY yr DESC, mo DESC"
);
$monthlyStmt->execute([$year]);
$monthlyRows = $monthlyStmt->fetchAll();

foreach ($monthlyRows as &$row) {
    $platformIncome = (float)$row['delivery_fees'] + (float)$row['tips'];
    $row['platform_income'] = $platformIncome;
    $row['tax_amount']      = round($platformIncome * ($taxRate / 100), 2);
    $row['net_earnings']    = round($platformIncome - $row['tax_amount'], 2);
}
unset($row);

// ── All-time totals ───────────────────────────────────────────────────────
$totalsStmt = $db->query(
    "SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(total_price),  0) AS gross_income,
        COALESCE(SUM(delivery_fee), 0) AS delivery_fees,
        COALESCE(SUM(tip_amount),   0) AS tips,
        COALESCE(SUM(item_price),   0) AS vendor_payouts
     FROM orders WHERE status IN ('completed','delivered')"
);
$totals = $totalsStmt->fetch();
$platformTotal = (float)$totals['delivery_fees'] + (float)$totals['tips'];
$taxTotal      = round($platformTotal * ($taxRate / 100), 2);
$netTotal      = round($platformTotal - $taxTotal, 2);

$totals['platform_income'] = $platformTotal;
$totals['tax_amount']      = $taxTotal;
$totals['net_earnings']    = $netTotal;

// ── This month summary ────────────────────────────────────────────────────
$thisMonthStmt = $db->prepare(
    "SELECT
        COUNT(*) AS order_count,
        COALESCE(SUM(total_price),  0) AS gross_income,
        COALESCE(SUM(delivery_fee), 0) AS delivery_fees,
        COALESCE(SUM(tip_amount),   0) AS tips,
        COALESCE(SUM(item_price),   0) AS vendor_payouts
     FROM orders
     WHERE status IN ('completed','delivered')
       AND YEAR(completed_at) = ? AND MONTH(completed_at) = ?"
);
$thisMonthStmt->execute([$year, $month]);
$thisMonth = $thisMonthStmt->fetch();
$platformMonth = (float)$thisMonth['delivery_fees'] + (float)$thisMonth['tips'];
$thisMonth['platform_income'] = $platformMonth;
$thisMonth['tax_amount']      = round($platformMonth * ($taxRate / 100), 2);
$thisMonth['net_earnings']    = round($platformMonth - $thisMonth['tax_amount'], 2);

apiSuccess([
    'tax_rate_percent' => $taxRate,
    'year'             => $year,
    'month'            => $month,
    'all_time'         => $totals,
    'this_month'       => $thisMonth,
    'monthly'          => $monthlyRows,
    'daily'            => $dailyRows,
]);
