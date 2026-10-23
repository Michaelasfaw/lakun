<?php
require_once __DIR__ . '/../config/db.php';

class Bid {
    private PDO $db;

    public function __construct() { $this->db = getDB(); }

    public function submit(int $orderId, int $vendorId, float $price, bool $available, ?string $note = null, ?string $itemsBreakdown = null): int|false {
        // Verify order is still in bidding window
        $stmt = $this->db->prepare(
            'SELECT id, bid_window_ends_at, TIMESTAMPDIFF(SECOND, NOW(), bid_window_ends_at) AS secs_left
             FROM orders
             WHERE id = ? AND status = "bidding" AND bid_window_ends_at > NOW()'
        );
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) return false;

        // Insert or update (vendor can revise their bid within the window)
        // Try with new columns first; fall back to base columns if migration hasn't run
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO bids (order_id, vendor_id, price, available, note, items_breakdown)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE price = VALUES(price), available = VALUES(available),
                   note = VALUES(note), items_breakdown = VALUES(items_breakdown)'
            );
            $stmt->execute([$orderId, $vendorId, $price, $available ? 1 : 0, $note, $itemsBreakdown]);
        } catch (\PDOException $e) {
            // Columns not yet migrated — insert without them
            $stmt = $this->db->prepare(
                'INSERT INTO bids (order_id, vendor_id, price, available)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE price = VALUES(price), available = VALUES(available)'
            );
            $stmt->execute([$orderId, $vendorId, $price, $available ? 1 : 0]);
        }
        $bidId = (int)$this->db->lastInsertId() ?: true;

        // Shorten bid window once a bid arrives — no need to wait the full 120s
        $graceRow = $this->db->query(
            'SELECT value FROM settings WHERE `key` = "bid_grace_after_first_secs"'
        )->fetch();
        $graceSecs = $graceRow ? (int)$graceRow['value'] : 30;

        $secsLeft = (int)$order['secs_left'];
        if ($secsLeft > $graceSecs) {
            $this->db->prepare(
                'UPDATE orders SET bid_window_ends_at = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE id = ?'
            )->execute([$graceSecs, $orderId]);
        }

        return $bidId;
    }

    public function getForOrder(int $orderId): array {
        $stmt = $this->db->prepare(
            'SELECT b.*, u.name AS vendor_name, u.shop_name
             FROM bids b
             JOIN users u ON b.vendor_id = u.id
             WHERE b.order_id = ?
             ORDER BY b.price ASC'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getVendorBid(int $orderId, int $vendorId): ?array {
        $stmt = $this->db->prepare(
            'SELECT * FROM bids WHERE order_id = ? AND vendor_id = ?'
        );
        $stmt->execute([$orderId, $vendorId]);
        return $stmt->fetch() ?: null;
    }
}
