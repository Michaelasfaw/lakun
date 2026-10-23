<?php
require_once __DIR__ . '/../config/db.php';

class Chat {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getMessages(int $taskId, int $sinceId = 0): array {
        $stmt = $this->db->prepare(
            'SELECT cm.*, u.name as sender_name, u.role as sender_role
             FROM chat_messages cm
             JOIN users u ON u.id = cm.sender_id
             WHERE cm.task_id = ? AND cm.id > ?
             ORDER BY cm.created_at ASC'
        );
        $stmt->execute([$taskId, $sinceId]);
        return $stmt->fetchAll();
    }

    public function send(int $taskId, int $senderId, string $message): int {
        $stmt = $this->db->prepare(
            'INSERT INTO chat_messages (task_id, sender_id, message) VALUES (?, ?, ?)'
        );
        $stmt->execute([$taskId, $senderId, $message]);
        return (int)$this->db->lastInsertId();
    }

    public function markRead(int $taskId, int $userId): void {
        $stmt = $this->db->prepare(
            'UPDATE chat_messages SET is_read = 1 WHERE task_id = ? AND sender_id != ?'
        );
        $stmt->execute([$taskId, $userId]);
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM chat_messages cm
             JOIN tasks t ON t.id = cm.task_id
             WHERE cm.sender_id != ? AND cm.is_read = 0
               AND (t.resident_id = ? OR t.runner_id = ?)
               AND t.status IN ('accepted', 'runner_done')"
        );
        $stmt->execute([$userId, $userId, $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function canChat(int $taskId, int $userId): bool {
        $stmt = $this->db->prepare(
            "SELECT id FROM tasks WHERE id = ? AND (resident_id = ? OR runner_id = ?) AND status IN ('accepted', 'runner_done', 'completed')"
        );
        $stmt->execute([$taskId, $userId, $userId]);
        return (bool)$stmt->fetch();
    }
}
