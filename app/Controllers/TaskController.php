<?php
namespace App\Controllers;

use App\Models\Task;
use PDO;
use App\Database;

class TaskController
{
    private $taskModel;
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->taskModel = new Task($this->db);
    }

    // Phương thức chung để trả về JSON
    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    // Phương thức xử lý đầu vào JSON chung
    private function getJsonInput()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonResponse(['error' => 'Invalid JSON'], 400);
        }
        return $input;
    }

    // Phương thức xác thực token chung
    private function authenticate()
    {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            $authController = new AuthController();
            $userId = $authController->validateToken($token);
            if ($userId) {
                return $userId;
            }
        }
        $this->jsonResponse(['error' => 'Unauthorized'], 401);
    }

    public function index()
    {
        $tasks = $this->taskModel->getAllTasks();
        $this->jsonResponse($tasks);
    }

    // Hàm lấy chi tiết một task theo id
    public function getTaskById($id)
    {
        $task = $this->taskModel->getTaskById($id);
        if ($task) {
            $this->jsonResponse($task);
        } else {
            $this->jsonResponse(['error' => 'Task not found'], 404);
        }
    }

    public function store()
    {
        $userId = $this->authenticate();  // Xác thực người dùng
        $input = $this->getJsonInput();  // Lấy dữ liệu từ JSON input

        $requiredFields = ['title', 'description', 'assigned_to', 'due_date'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                $this->jsonResponse(['error' => ucfirst($field) . ' is required'], 400);
            }
        }

        $this->taskModel->createTask($input);
        $this->jsonResponse(['success' => 'Task created successfully'], 201);
    }

    public function update()
    {
        $userId = $this->authenticate();  // Xác thực người dùng
        $input = $this->getJsonInput();  // Lấy dữ liệu từ JSON input

        $requiredFields = ['id', 'title', 'description', 'assigned_to', 'due_date'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                $this->jsonResponse(['error' => ucfirst($field) . ' is required'], 400);
            }
        }

        $this->taskModel->updateTask($input);
        $this->jsonResponse(['success' => 'Task updated successfully']);
    }

    public function delete()
    {
        $userId = $this->authenticate();  // Xác thực người dùng
        $input = $this->getJsonInput();  // Lấy dữ liệu từ JSON input

        if (empty($input['id'])) {
            $this->jsonResponse(['error' => 'Task ID is required'], 400);
        }

        $this->taskModel->deleteTask($input['id']);
        $this->jsonResponse(['success' => 'Task deleted successfully']);
    }

    public function storeSubTask()
    {
        $userId = $this->authenticate();  // Xác thực người dùng
        $input = $this->getJsonInput();  // Lấy dữ liệu từ JSON input

        $requiredFields = ['task_id', 'title', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                $this->jsonResponse(['error' => ucfirst($field) . ' is required'], 400);
            }
        }

        $taskExists = $this->taskModel->checkTaskExists($input['task_id']);
        if (!$taskExists) {
            $this->jsonResponse(['error' => 'Task ID does not exist'], 404);
        }

        $this->taskModel->createSubTask($input);
        $this->jsonResponse(['success' => 'Sub-task created successfully'], 201);
    }

    public function updateSubTaskCompletion()
    {
        $userId = $this->authenticate();  // Xác thực người dùng
        $input = $this->getJsonInput();  // Lấy dữ liệu từ JSON input

        $requiredFields = ['id', 'is_completed', 'task_id'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                $this->jsonResponse(['error' => ucfirst($field) . ' is required'], 400);
            }
        }

        $this->taskModel->updateSubTaskCompletion($input['id'], $input['is_completed']);

        // Tính toán % hoàn thành của task lớn
        $subTasks = $this->taskModel->getSubTasks($input['task_id']);
        $totalSubTasks = count($subTasks);
        $completedSubTasks = count(array_filter($subTasks, fn($subTask) => $subTask['is_completed'] == 1));

        $completionPercentage = ($completedSubTasks / $totalSubTasks) * 100;
        $this->taskModel->updateTaskCompletion($input['task_id'], $completionPercentage);

        $this->jsonResponse(['success' => 'Sub-task updated and completion recalculated']);
    }

    // API để trả về danh sách người dùng
    public function getListUser()
    {
        $users = $this->userModel->getAllUsers();
        $this->jsonResponse($users);
    }
}

