<?php
namespace App\Controllers;

use App\Models\Task;
use PDO;
use App\Database;

class TaskController
{
    private $taskModel;
    /**
     * @var PDO
     */
    private $db;

    public function __construct()
    {
        // Lấy kết nối database duy nhất từ lớp Database
        $this->db = Database::getInstance();
        $this->taskModel = new Task($this->db);
    }

    public function index()
    {
        $tasks = $this->taskModel->getAllTasks();
        echo json_encode($tasks);
    }

    public function store()
    {
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'assigned_to' => $_POST['assigned_to'],
            'due_date' => $_POST['due_date']
        ];

        // Lấy userId từ token
        $headers = apache_request_headers();
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $authController = new AuthController();
        $userId = $authController->validateToken($token);

        if ($userId) {
            $this->taskModel->createTask($data);
            echo json_encode(['success' => 'Task created successfully']);
        } else {
            echo json_encode(['error' => 'Unauthorized']);
        }
    }

    public function update()
    {
        $data = [
            'id' => $_POST['id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'assigned_to' => $_POST['assigned_to'],
            'due_date' => $_POST['due_date']
        ];
        $this->taskModel->updateTask($data);
        echo json_encode(['success' => 'Task updated successfully']);
    }

    public function delete()
    {
        $id = $_POST['id'];
        $this->taskModel->deleteTask($id);
        echo json_encode(['success' => 'Task deleted successfully']);
    }
}
