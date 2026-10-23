<?php
require_once __DIR__ . '/../config/db.php';

class Task {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getOpenTasksForBuilding(int $buildingId): array {
        $stmt = $this->db->prepare(
            "SELECT t.*, u.name as resident_name
             FROM tasks t
             JOIN users u ON u.id = t.resident_id
             WHERE t.building_id = ? AND t.status = 'open'
             ORDER BY t.urgency DESC, t.created_at DESC"
        );
        $stmt->execute([$buildingId]);
        return $stmt->fetchAll();
    }

    public function getTasksForResident(int $residentId): array {
        $stmt = $this->db->prepare(
            "SELECT t.*, u.name as runner_name, u.phone as runner_phone,
                    COALESCE(r.score, 0) as rating_given
             FROM tasks t
             LEFT JOIN users u ON u.id = t.runner_id
             LEFT JOIN ratings r ON r.task_id = t.id
             WHERE t.resident_id = ?
             ORDER BY t.created_at DESC"
        );
        $stmt->execute([$residentId]);
        return $stmt->fetchAll();
    }

    public function getTasksForRunner(int $runnerId): array {
        $stmt = $this->db->prepare(
            "SELECT t.*, u.name as resident_name, u.phone as resident_phone
             FROM tasks t
             JOIN users u ON u.id = t.resident_id
             WHERE t.runner_id = ?
             ORDER BY t.created_at DESC"
        );
        $stmt->execute([$runnerId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT t.*,
                    res.name as resident_name, res.phone as resident_phone,
                    run.name as runner_name, run.phone as runner_phone
             FROM tasks t
             JOIN users res ON res.id = t.resident_id
             LEFT JOIN users run ON run.id = t.runner_id
             WHERE t.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO tasks (title, description, category, urgency, tip, resident_id, building_id, unit_number)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['category'] ?? 'other',
            $data['urgency'] ?? 'normal',
            $data['tip'] ?? null,
            $data['resident_id'],
            $data['building_id'],
            $data['unit_number'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Race-safe acceptance using MySQL row-level lock.
     * Returns ['success' => bool, 'message' => string]
     */
    public function accept(int $taskId, int $runnerId): array {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id, status, resident_id FROM tasks WHERE id = ? AND status = 'open' FOR UPDATE"
            );
            $stmt->execute([$taskId]);
            $task = $stmt->fetch();

            if (!$task) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Job already taken or not found'];
            }

            $this->db->prepare(
                "UPDATE tasks SET runner_id = ?, status = 'accepted', accepted_at = NOW() WHERE id = ?"
            )->execute([$runnerId, $taskId]);

            $this->db->commit();
            return ['success' => true, 'resident_id' => $task['resident_id']];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
        }
    }

    public function markRunnerDone(int $taskId, int $runnerId): bool {
        $stmt = $this->db->prepare(
            "UPDATE tasks SET status = 'runner_done', runner_confirmed = 1 WHERE id = ? AND runner_id = ? AND status = 'accepted'"
        );
        $stmt->execute([$taskId, $runnerId]);
        return $stmt->rowCount() > 0;
    }

    public function confirmByResident(int $taskId, int $residentId): bool {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT id, status, runner_id FROM tasks WHERE id = ? AND resident_id = ? FOR UPDATE"
            );
            $stmt->execute([$taskId, $residentId]);
            $task = $stmt->fetch();

            if (!$task || !in_array($task['status'], ['accepted', 'runner_done'])) {
                $this->db->rollBack();
                return false;
            }

            $this->db->prepare(
                "UPDATE tasks SET resident_confirmed = 1, status = 'completed', completed_at = NOW() WHERE id = ?"
            )->execute([$taskId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function cancel(int $taskId, int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE tasks SET status = 'cancelled'
             WHERE id = ? AND (resident_id = ? OR runner_id = ?) AND status IN ('open', 'accepted')"
        );
        $stmt->execute([$taskId, $userId, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 20): array {
        $where = [];
        $params = [];
        if (!empty($filters['status']))   { $where[] = 't.status = ?';      $params[] = $filters['status']; }
        if (!empty($filters['building'])) { $where[] = 't.building_id = ?'; $params[] = $filters['building']; }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT t.*, res.name as resident_name, run.name as runner_name, b.name as building_name
             FROM tasks t
             JOIN users res ON res.id = t.resident_id
             LEFT JOIN users run ON run.id = t.runner_id
             JOIN buildings b ON b.id = t.building_id
             $whereClause
             ORDER BY t.created_at DESC LIMIT ? OFFSET ?"
        );
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getActiveForRunner(int $runnerId): ?array {
        $stmt = $this->db->prepare(
            "SELECT t.*, res.name as resident_name, res.phone as resident_phone, res.unit_number
             FROM tasks t
             JOIN users res ON res.id = t.resident_id
             WHERE t.runner_id = ? AND t.status IN ('accepted', 'runner_done')"
        );
        $stmt->execute([$runnerId]);
        return $stmt->fetch() ?: null;
    }

    public function getStats(): array {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) as total,
                SUM(status='open') as open_count,
                SUM(status='accepted') as accepted_count,
                SUM(status='completed') as completed_count,
                SUM(status='cancelled') as cancelled_count
             FROM tasks"
        );
        return $stmt->fetch();
    }
}
