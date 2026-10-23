<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/LocationService.php';

class Order {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
        // Drop the broken AFTER INSERT trigger that illegally UPDATEs the same
        // table (MySQL/MariaDB error 1442). PHP handles order_code via UPDATE after INSERT.
        try { $this->db->exec('DROP TRIGGER IF EXISTS trg_order_code'); } catch (\PDOException $e) {}
    }

    public function create(array $data): int {
        $scheduledAt = !empty($data['scheduled_at']) ? $data['scheduled_at'] : null;

        // Try INSERT with new columns first; fall back to base schema if migration not run
        // order_code is NOT NULL in schema — insert '' placeholder; UPDATE below sets real value
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO orders
                 (order_code, customer_id, building_id, block, floor, house_no, customer_phone,
                  item_name, quantity, notes, delivery_fee, scheduled_at, bid_window_ends_at, status)
                 VALUES ("", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), "bidding")'
            );
            $stmt->execute([
                $data['customer_id'], $data['building_id'], $data['block'],
                $data['floor'], $data['house_no'], $data['customer_phone'],
                $data['item_name'], $data['quantity'] ?? 1, $data['notes'] ?? null,
                $data['delivery_fee'], $scheduledAt, $data['bid_window_seconds'],
            ]);
        } catch (\PDOException $e) {
            // scheduled_at column not yet added — insert without it
            $stmt = $this->db->prepare(
                'INSERT INTO orders
                 (order_code, customer_id, building_id, block, floor, house_no, customer_phone,
                  item_name, quantity, notes, delivery_fee, bid_window_ends_at, status)
                 VALUES ("", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), "bidding")'
            );
            $stmt->execute([
                $data['customer_id'], $data['building_id'], $data['block'],
                $data['floor'], $data['house_no'], $data['customer_phone'],
                $data['item_name'], $data['quantity'] ?? 1, $data['notes'] ?? null,
                $data['delivery_fee'], $data['bid_window_seconds'],
            ]);
        }

        $newId = (int)$this->db->lastInsertId();
        // Auto-set LK order code (silently ignore if column not yet migrated)
        try {
            $this->db->prepare(
                'UPDATE orders SET order_code = ? WHERE id = ? AND (order_code IS NULL OR order_code = "")'
            )->execute(['LK' . $newId, $newId]);
        } catch (\PDOException $e) {}

        return $newId;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT o.*,
                    b.name AS building_name,
                    u.name AS customer_name, u.phone AS customer_phone_no,
                    v.name AS vendor_name, v.shop_name, v.phone AS vendor_phone,
                    da.name AS agent_name, da.phone AS agent_phone,
                    TIMESTAMPDIFF(SECOND, NOW(), o.bid_window_ends_at) AS seconds_remaining
             FROM orders o
             JOIN buildings b ON o.building_id = b.id
             JOIN users u ON o.customer_id = u.id
             LEFT JOIN users v  ON o.selected_vendor_id = v.id
             LEFT JOIN users da ON o.delivery_agent_id  = da.id
             WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch() ?: null;
        // Synthesise order_code if column not yet migrated
        if ($row && empty($row['order_code'])) {
            $row['order_code'] = 'LK' . $row['id'];
        }
        return $row;
    }

    public function getForCustomer(int $customerId): array {
        $stmt = $this->db->prepare(
            'SELECT o.*, b.name AS building_name,
                    TIMESTAMPDIFF(SECOND, NOW(), o.bid_window_ends_at) AS seconds_remaining
             FROM orders o
             JOIN buildings b ON o.building_id = b.id
             WHERE o.customer_id = ?
             ORDER BY o.created_at DESC'
        );
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    /**
     * Open orders this vendor can bid on.
     * Uses LocationService to include orders from any building within the vendor's reach,
     * not just buildings they are manually assigned to.
     */
    public function getOpenForVendor(int $vendorId): array {
        // Fetch vendor's current GPS (may be null if not set)
        $vStmt = $this->db->prepare('SELECT latitude, longitude FROM users WHERE id = ?');
        $vStmt->execute([$vendorId]);
        $vendor = $vStmt->fetch();

        $lat = ($vendor && $vendor['latitude']  !== null) ? (float)$vendor['latitude']  : null;
        $lon = ($vendor && $vendor['longitude'] !== null) ? (float)$vendor['longitude'] : null;

        return LocationService::getOpenOrdersForVendor($this->db, $vendorId, $lat, $lon);
    }

    /** Orders where this vendor won the bid */
    public function getWonByVendor(int $vendorId): array {
        $stmt = $this->db->prepare(
            'SELECT o.*, b.name AS building_name
             FROM orders o
             JOIN buildings b ON o.building_id = b.id
             WHERE o.selected_vendor_id = ?
             ORDER BY o.created_at DESC'
        );
        $stmt->execute([$vendorId]);
        return $stmt->fetchAll();
    }

    /** Tasks assigned to delivery agent */
    public function getForAgent(int $agentId): array {
        $stmt = $this->db->prepare(
            'SELECT o.*,
                    b.name AS building_name,
                    u.name AS customer_name, u.phone AS customer_phone,
                    v.name AS vendor_name, v.shop_name, v.shop_location
             FROM orders o
             JOIN buildings b ON o.building_id = b.id
             JOIN users u ON o.customer_id = u.id
             LEFT JOIN users v ON o.selected_vendor_id = v.id
             WHERE o.delivery_agent_id = ?
             ORDER BY o.assigned_at DESC'
        );
        $stmt->execute([$agentId]);
        return $stmt->fetchAll();
    }

    /** Admin: all orders, latest first */
    public function getAll(?string $status = null, int $limit = 50): array {
        $select = 'SELECT o.*,
                          b.name AS building_name,
                          u.name AS customer_name,
                          v.name AS vendor_name, v.shop_name,
                          da.name AS agent_name
                   FROM orders o
                   JOIN buildings b ON o.building_id = b.id
                   JOIN users u ON o.customer_id = u.id
                   LEFT JOIN users v  ON o.selected_vendor_id = v.id
                   LEFT JOIN users da ON o.delivery_agent_id  = da.id';
        if ($status) {
            $stmt = $this->db->prepare($select . ' WHERE o.status = ? ORDER BY o.created_at DESC LIMIT ?');
            $stmt->execute([$status, $limit]);
        } else {
            $stmt = $this->db->prepare($select . ' ORDER BY o.created_at DESC LIMIT ?');
            $stmt->execute([$limit]);
        }
        $rows = $stmt->fetchAll();
        // Synthesise order_code if column not yet migrated
        foreach ($rows as &$row) {
            if (empty($row['order_code'])) $row['order_code'] = 'LK' . $row['id'];
        }
        return $rows;
    }

    /**
     * Close the bid window: select the lowest available bid.
     * Returns the winning bid array or null if no valid bids.
     */
    public function selectWinner(int $orderId): ?array {
        $this->db->beginTransaction();
        try {
            // Lock the order row
            $stmt = $this->db->prepare(
                'SELECT id, status FROM orders WHERE id = ? FOR UPDATE'
            );
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order || $order['status'] !== 'bidding') {
                $this->db->rollBack();
                return null;
            }

            // Find lowest available bid
            $stmt = $this->db->prepare(
                'SELECT b.*, u.shop_name FROM bids b
                 JOIN users u ON b.vendor_id = u.id
                 WHERE b.order_id = ? AND b.available = 1
                 ORDER BY b.price ASC LIMIT 1'
            );
            $stmt->execute([$orderId]);
            $winner = $stmt->fetch();

            if (!$winner) {
                // No bids — extend window by another cycle or cancel
                $this->db->prepare(
                    'UPDATE orders SET status = "cancelled" WHERE id = ?'
                )->execute([$orderId]);
                $this->db->commit();
                return null;
            }

            // Get delivery fee
            $deliveryFee = (float)($this->getDeliveryFee());
            $totalPrice  = (float)$winner['price'] + $deliveryFee;

            // Mark winner bid + update order
            $this->db->prepare(
                'UPDATE bids SET is_selected = 1 WHERE id = ?'
            )->execute([$winner['id']]);

            $this->db->prepare(
                'UPDATE orders SET
                    status = "awaiting_confirmation",
                    selected_bid_id    = ?,
                    selected_vendor_id = ?,
                    item_price         = ?,
                    delivery_fee       = ?,
                    total_price        = ?
                 WHERE id = ?'
            )->execute([
                $winner['id'],
                $winner['vendor_id'],
                $winner['price'],
                $deliveryFee,
                $totalPrice,
                $orderId,
            ]);

            $this->db->commit();
            return $winner;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Customer confirms the price — moves to confirmed */
    public function confirm(int $orderId, int $customerId): bool {
        $stmt = $this->db->prepare(
            'UPDATE orders
             SET status = "confirmed", confirmed_at = NOW()
             WHERE id = ? AND customer_id = ? AND status = "awaiting_confirmation"'
        );
        $stmt->execute([$orderId, $customerId]);
        return $stmt->rowCount() > 0;
    }

    /** Admin assigns a delivery agent */
    public function assignAgent(int $orderId, int $agentId): bool {
        $stmt = $this->db->prepare(
            'UPDATE orders
             SET delivery_agent_id = ?, status = "assigned", assigned_at = NOW()
             WHERE id = ? AND status IN ("confirmed","vendor_paid")'
        );
        $stmt->execute([$agentId, $orderId]);
        return $stmt->rowCount() > 0;
    }

    /** Delivery agent updates delivery status */
    public function updateDeliveryStatus(int $orderId, int $agentId, string $newStatus): bool {
        $allowed = ['picked_up', 'on_the_way', 'out_for_delivery', 'delivered'];
        if (!in_array($newStatus, $allowed, true)) return false;

        $extra = $newStatus === 'delivered' ? ', delivered_at = NOW()' : '';
        $stmt = $this->db->prepare(
            "UPDATE orders SET status = ? $extra
             WHERE id = ? AND delivery_agent_id = ? AND status != 'delivered'"
        );
        $stmt->execute([$newStatus, $orderId, $agentId]);
        return $stmt->rowCount() > 0;
    }

    /** Customer confirms they received the delivery — moves from delivered → completed */
    public function confirmDelivery(int $orderId, int $customerId): bool {
        $stmt = $this->db->prepare(
            'UPDATE orders
             SET status = "completed", completed_at = NOW()
             WHERE id = ? AND customer_id = ? AND status = "delivered"'
        );
        $stmt->execute([$orderId, $customerId]);
        return $stmt->rowCount() > 0;
    }

    public function cancel(int $orderId, int $customerId): bool {
        $stmt = $this->db->prepare(
            'UPDATE orders SET status = "cancelled"
             WHERE id = ? AND customer_id = ? AND status IN ("bidding","awaiting_confirmation")'
        );
        $stmt->execute([$orderId, $customerId]);
        return $stmt->rowCount() > 0;
    }

    private function getDeliveryFee(): float {
        $stmt = $this->db->prepare('SELECT value FROM settings WHERE `key` = "delivery_fee"');
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (float)$row['value'] : 50.0;
    }

    public function getBidWindowSeconds(): int {
        $stmt = $this->db->prepare('SELECT value FROM settings WHERE `key` = "bid_window_seconds"');
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (int)$row['value'] : 60;
    }

    /** Check all bidding orders whose windows have expired and auto-select winners */
    public function processExpiredWindows(): array {
        $stmt = $this->db->prepare(
            'SELECT id FROM orders
             WHERE status = "bidding" AND bid_window_ends_at <= NOW()'
        );
        $stmt->execute();
        $expired = $stmt->fetchAll();
        $results = [];
        foreach ($expired as $row) {
            $results[$row['id']] = $this->selectWinner($row['id']);
        }
        return $results;
    }
}
