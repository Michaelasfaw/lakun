<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private PDO $db;

    public function __construct() { $this->db = getDB(); }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT id, name, phone, role, building_id, block, floor, house_no,
                    shop_name, shop_location, profile_photo, is_active, created_at
             FROM users WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByPhone(string $phone): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE phone = ?');
        $stmt->execute([$phone]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users
             (name, phone, password_hash, role, building_id, block, floor, house_no, shop_name, shop_location)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['phone'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'],
            $data['building_id']   ?? null,
            $data['block']         ?? null,
            $data['floor']         ?? null,
            $data['house_no']      ?? null,
            $data['shop_name']     ?? null,
            $data['shop_location'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function addVendorBuilding(int $vendorId, int $buildingId): void {
        $this->db->prepare(
            'INSERT IGNORE INTO vendor_buildings (vendor_id, building_id) VALUES (?, ?)'
        )->execute([$vendorId, $buildingId]);
    }

    public function addAgentBuilding(int $agentId, int $buildingId): void {
        $this->db->prepare(
            'INSERT IGNORE INTO agent_buildings (agent_id, building_id) VALUES (?, ?)'
        )->execute([$agentId, $buildingId]);
    }

    public function getVendorBuildings(int $vendorId): array {
        $stmt = $this->db->prepare(
            'SELECT b.* FROM buildings b
             JOIN vendor_buildings vb ON b.id = vb.building_id
             WHERE vb.vendor_id = ?'
        );
        $stmt->execute([$vendorId]);
        return $stmt->fetchAll();
    }

    public function getAgentBuildings(int $agentId): array {
        $stmt = $this->db->prepare(
            'SELECT b.* FROM buildings b
             JOIN agent_buildings ab ON b.id = ab.building_id
             WHERE ab.agent_id = ?'
        );
        $stmt->execute([$agentId]);
        return $stmt->fetchAll();
    }

    /** Get all active delivery agents for a building */
    public function getDeliveryAgentsForBuilding(int $buildingId): array {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.name, u.phone FROM users u
             JOIN agent_buildings ab ON u.id = ab.agent_id
             WHERE ab.building_id = ? AND u.is_active = 1 AND u.role = "delivery_agent"'
        );
        $stmt->execute([$buildingId]);
        return $stmt->fetchAll();
    }

    public function updateProfile(int $id, array $data): void {
        $sets = []; $params = [];
        $allowed = ['name', 'block', 'floor', 'house_no', 'building_id', 'shop_name', 'shop_location'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) { $sets[] = "$field = ?"; $params[] = $data[$field]; }
        }
        if (empty($sets)) return;
        $params[] = $id;
        $this->db->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
    }

    public function getAll(?string $role = null, int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        if ($role) {
            $stmt = $this->db->prepare(
                'SELECT id, name, phone, role, building_id, block, floor, house_no,
                        shop_name, is_active, created_at
                 FROM users WHERE role = ? ORDER BY created_at DESC LIMIT ? OFFSET ?'
            );
            $stmt->execute([$role, $perPage, $offset]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT id, name, phone, role, building_id, block, floor, house_no,
                        shop_name, is_active, created_at
                 FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?'
            );
            $stmt->execute([$perPage, $offset]);
        }
        return $stmt->fetchAll();
    }

    public function toggleActive(int $id): void {
        $this->db->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ?')->execute([$id]);
    }
}
