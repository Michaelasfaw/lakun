<?php
require_once __DIR__ . '/../config/db.php';

class Building {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll(): array {
        return $this->db->query('SELECT * FROM buildings ORDER BY name ASC')->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM buildings WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO buildings (name, address, latitude, longitude, service_radius_km, total_floors)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['address']           ?? '',
            $data['latitude']          ?? null,
            $data['longitude']         ?? null,
            $data['service_radius_km'] ?? 2.00,
            $data['total_floors']      ?? 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            'UPDATE buildings
             SET name = ?, address = ?, latitude = ?, longitude = ?,
                 service_radius_km = ?, total_floors = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['address']           ?? '',
            isset($data['latitude'])   ? (float)$data['latitude']          : null,
            isset($data['longitude'])  ? (float)$data['longitude']         : null,
            isset($data['service_radius_km']) ? (float)$data['service_radius_km'] : 2.00,
            $data['total_floors']      ?? 1,
            $id,
        ]);
    }

    public function delete(int $id): void {
        $this->db->prepare('DELETE FROM buildings WHERE id = ?')->execute([$id]);
    }
}
