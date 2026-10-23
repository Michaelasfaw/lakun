<?php
require_once __DIR__ . '/../config/db.php';

class Rating {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function create(int $taskId, int $residentId, int $runnerId, int $score, string $comment = ''): bool {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO ratings (task_id, resident_id, runner_id, score, comment) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$taskId, $residentId, $runnerId, $score, $comment]);
            return true;
        } catch (PDOException) {
            return false; // UNIQUE constraint on task_id
        }
    }

    public function getForTask(int $taskId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM ratings WHERE task_id = ?');
        $stmt->execute([$taskId]);
        return $stmt->fetch() ?: null;
    }

    public function getRunnerStats(int $runnerId): array {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total_ratings, ROUND(AVG(score), 1) as avg_score,
                    SUM(score = 5) as five_star, SUM(score = 4) as four_star,
                    SUM(score = 3) as three_star, SUM(score <= 2) as low_star
             FROM ratings WHERE runner_id = ?'
        );
        $stmt->execute([$runnerId]);
        return $stmt->fetch();
    }

    public function getRecentForRunner(int $runnerId, int $limit = 10): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.name as resident_name, t.title as task_title
             FROM ratings r
             JOIN users u ON u.id = r.resident_id
             JOIN tasks t ON t.id = r.task_id
             WHERE r.runner_id = ?
             ORDER BY r.created_at DESC LIMIT ?'
        );
        $stmt->execute([$runnerId, $limit]);
        return $stmt->fetchAll();
    }
}
