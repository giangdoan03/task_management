<?php

namespace App\Models;

use PDO;

class Task
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllTasks()
    {
        $stmt = $this->db->query("SELECT * FROM tasks");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hàm lấy chi tiết một task theo id
    public function getTaskById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createTask($data)
    {
        $stmt = $this->db->prepare("INSERT INTO tasks (title, description, assigned_to, due_date) VALUES (:title, :description, :assigned_to, :due_date)");
        return $stmt->execute($data);
    }

    public function updateTask($data)
    {
        $stmt = $this->db->prepare("UPDATE tasks SET title = :title, description = :description, assigned_to = :assigned_to, due_date = :due_date WHERE id = :id");
        return $stmt->execute($data);
    }

    public function deleteTask($id)
    {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getSubTasks($taskId)
    {
        $stmt = $this->db->prepare("SELECT * FROM sub_tasks WHERE task_id = :task_id");
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSubTask($data)
    {
        // Xác định các khóa trong mảng $data
        $stmt = $this->db->prepare("INSERT INTO sub_tasks (task_id, title, description) VALUES (:task_id, :title, :description)");

        // Chạy câu lệnh với các giá trị tương ứng từ mảng $data
        return $stmt->execute([
            'task_id' => $data['task_id'],
            'title' => $data['title'],
            'description' => $data['description']
        ]);
    }

    public function getLastInsertedSubTask()
    {
        $stmt = $this->db->query("SELECT * FROM sub_tasks ORDER BY id DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function updateSubTaskCompletion($subTaskId, $isCompleted)
    {
        $stmt = $this->db->prepare("UPDATE sub_tasks SET is_completed = :is_completed WHERE id = :id");
        return $stmt->execute(['id' => $subTaskId, 'is_completed' => $isCompleted]);
    }

    public function updateTaskCompletion($taskId, $completionPercentage)
    {
        $stmt = $this->db->prepare("UPDATE tasks SET completion_percentage = :completion_percentage WHERE id = :id");
        return $stmt->execute(['id' => $taskId, 'completion_percentage' => $completionPercentage]);
    }

    public function checkTaskExists($taskId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tasks WHERE id = :task_id");
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchColumn() > 0;
    }

    // Phương thức để xóa một hoặc nhiều subtasks
    public function deleteSubTasks($subTaskIds)
    {
        // Kiểm tra nếu chỉ có một ID thì xử lý thành mảng
        if (!is_array($subTaskIds)) {
            $subTaskIds = [$subTaskIds];
        }

        // Tạo câu lệnh SQL xóa với danh sách ID
        $ids = implode(',', array_fill(0, count($subTaskIds), '?'));
        $stmt = $this->db->prepare("DELETE FROM sub_tasks WHERE id IN ($ids)");

        // Thực thi câu lệnh
        return $stmt->execute($subTaskIds);
    }




}
