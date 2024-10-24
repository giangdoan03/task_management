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

    public function getAllTasks()
    {
        // Xác thực người dùng
        $userId = $this->authenticate();

        // Nếu không xác thực được, trả về lỗi Unauthorized
        if (!$userId) {
            return $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        // Nếu xác thực thành công, tiếp tục xử lý
        $tasks = $this->taskModel->getAllTasks();
        return $this->jsonResponse($tasks);
    }

// Hàm lấy chi tiết một task theo id, bao gồm cả subtasks
    public function getTaskById($id)
    {
        // Xác thực người dùng
        $userId = $this->authenticate();

        // Nếu không xác thực được, trả về lỗi Unauthorized
        if (!$userId) {
            return $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }
        // Lấy task theo id
        $task = $this->taskModel->getTaskById($id);

        if ($task) {
            // Lấy subtasks liên quan đến task này
            $subTasks = $this->taskModel->getSubTasks($id);
            // Thêm subtasks vào response
            $task['subtasks'] = $subTasks;

            return $this->jsonResponse($task); // Trả về JSON bao gồm task và subtasks
        } else {
            return $this->jsonResponse(['error' => 'Task not found'], 404);
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

        // Kiểm tra các trường cần thiết
        $requiredFields = ['task_id', 'title', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                $this->jsonResponse(['error' => ucfirst($field) . ' is required'], 400);
                return;
            }
        }

        // Kiểm tra xem task có tồn tại không
        $taskExists = $this->taskModel->checkTaskExists($input['task_id']);
        if (!$taskExists) {
            $this->jsonResponse(['error' => 'Task ID does not exist'], 404);
            return;
        }

        // Tạo subtask mới
        $this->taskModel->createSubTask($input);

        // Lấy lại subtask vừa tạo để trả về
        $subTask = $this->taskModel->getLastInsertedSubTask();

        // Trả về phản hồi với subtask vừa tạo
        $this->jsonResponse($subTask, 201);
    }

    public function updateSubTaskCompletion()
    {
        $userId = $this->authenticate();  // Xác thực người dùng
        $input = $this->getJsonInput();  // Lấy dữ liệu từ JSON input

        // Kiểm tra xem các trường có tồn tại hay không
        $requiredFields = ['id', 'is_completed', 'task_id', 'title', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                $this->jsonResponse(['error' => ucfirst($field) . ' is required'], 400);
                return;
            }
        }

        // Gọi đúng phương thức updateSubTaskCompletion
        $this->taskModel->updateSubTaskCompletion($input['id'], $input['is_completed'], $input['title'], $input['description']);

        // Tính toán % hoàn thành của task lớn
        $subTasks = $this->taskModel->getSubTasks($input['task_id']);
        $totalSubTasks = count($subTasks);
        $completedSubTasks = count(array_filter($subTasks, function ($subTask) {
            return $subTask['is_completed'] == 1;
        }));

        // Kiểm tra xem có sub-task nào không để tránh chia cho 0
        $completionPercentage = 0;
        if ($totalSubTasks > 0) {
            $completionPercentage = ($completedSubTasks / $totalSubTasks) * 100;
            $this->taskModel->updateTaskCompletion($input['task_id'], $completionPercentage);
        } else {
            $this->taskModel->updateTaskCompletion($input['task_id'], 0);  // Không có sub-task, đặt % hoàn thành là 0
        }

        // Trả về phần trăm hoàn thành mới
        $this->jsonResponse([
            'success' => 'Sub-task updated and completion recalculated',
            'completion_percentage' => $completionPercentage
        ]);
    }



    public function deleteSubTasks()
    {
        $userId = $this->authenticate();  // Xác thực người dùng
        $input = $this->getJsonInput();  // Lấy dữ liệu từ JSON input

        // Kiểm tra xem có nhận được danh sách ID hoặc một ID cần xóa không
        if (empty($input['subtask_ids'])) {
            $this->jsonResponse(['error' => 'Sub-task IDs are required'], 400);
            return;
        }

        // Gọi model để xóa subtasks
        $this->taskModel->deleteSubTasks($input['subtask_ids']);
        $this->jsonResponse(['success' => 'Sub-tasks deleted successfully'], 200);
    }

}

