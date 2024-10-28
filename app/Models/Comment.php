<?php

namespace App\Models;

use App\Database;
use PDO;

class Comment
{
    private $db;

    public function __construct($db)
    {
        $this->db = Database::getInstance();
        $this->db = $db;
    }

    // Kiểm tra xem user_id có tồn tại không
    public function userExists($userId)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // Kiểm tra xem task_id có tồn tại không
    public function taskExists($taskId)
    {
        $stmt = $this->db->prepare("SELECT id FROM tasks WHERE id = :task_id");
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->rowCount() > 0;
    }

    // Lấy tất cả bình luận của một công việc
    public function getCommentsByTaskId($taskId)
    {
        $stmt = $this->db->prepare("SELECT comments.*, users.name FROM comments JOIN users ON comments.user_id = users.id WHERE task_id = :task_id ORDER BY created_at DESC");
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo một bình luận mới
    public function createComment($data)
    {
        $stmt = $this->db->prepare("INSERT INTO comments (task_id, user_id, content) VALUES (:task_id, :user_id, :content)");
        return $stmt->execute($data);
    }
}
