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
}
