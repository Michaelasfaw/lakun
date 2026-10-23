<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/LocationService.php';

class Notification {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function create(int $userId, ?int $orderId, string $type, string $title, string $body = ''): void {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, order_id, type, title, body) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $orderId, $type, $title, $body]);
    }

    public function getForUser(int $userId, int $limit = 30): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markRead(int $userId, ?int $notifId = null): void {
        if ($notifId) {
            $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
            $stmt->execute([$notifId, $userId]);
        } else {
            $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
            $stmt->execute([$userId]);
        }
    }

    /**
     * Notify all vendors reachable from $buildingId — GPS radius + manual assignments.
     */
    public function notifyVendorsInBuilding(int $buildingId, int $orderId, string $title, string $body = ''): void {
        $vendorIds = LocationService::getVendorIdsForBuilding($this->db, $buildingId);
        foreach ($vendorIds as $vid) {
            $this->create($vid, $orderId, NOTIF_NEW_ORDER, $title, $body);
        }
    }
}
