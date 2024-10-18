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

    // Hàm lấy chi tiết một task theo id
    public function getTaskById($id)
    {
        $task = $this->taskModel->getTaskById($id);

        if ($task) {
            echo json_encode($task);
        } else {
            echo json_encode(['error' => 'Task not found']);
        }
    }

    public function store()
    {
        // Lấy dữ liệu JSON từ request body
        $input = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra xem dữ liệu JSON có được giải mã đúng không
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        // Kiểm tra từng giá trị từ JSON input
        $title = isset($input['title']) ? $input['title'] : null;
        $description = isset($input['description']) ? $input['description'] : null;
        $assigned_to = isset($input['assigned_to']) ? $input['assigned_to'] : null;
        $due_date = isset($input['due_date']) ? $input['due_date'] : null;

        // Kiểm tra xem tất cả các trường có tồn tại và không rỗng hay không
        if (empty($title)) {
            echo json_encode(['error' => 'Title is required']);
            return;
        }

        if (empty($description)) {
            echo json_encode(['error' => 'Description is required']);
            return;
        }

        if (empty($assigned_to)) {
            echo json_encode(['error' => 'Assigned to is required']);
            return;
        }

        if (empty($due_date)) {
            echo json_encode(['error' => 'Due date is required']);
            return;
        }

        // Tạo mảng $data để lưu vào database
        $data = [
            'title' => $title,
            'description' => $description,
            'assigned_to' => $assigned_to,
            'due_date' => $due_date
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
        // Lấy dữ liệu JSON từ request body
        $input = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra xem dữ liệu JSON có được giải mã đúng không
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        // Kiểm tra từng giá trị từ JSON input
        $id = isset($input['id']) ? $input['id'] : null;
        $title = isset($input['title']) ? $input['title'] : null;
        $description = isset($input['description']) ? $input['description'] : null;
        $assigned_to = isset($input['assigned_to']) ? $input['assigned_to'] : null;
        $due_date = isset($input['due_date']) ? $input['due_date'] : null;

        // Kiểm tra xem tất cả các trường có tồn tại và không rỗng hay không
        if (empty($id)) {
            echo json_encode(['error' => 'Task ID is required']);
            return;
        }

        if (empty($title)) {
            echo json_encode(['error' => 'Title is required']);
            return;
        }

        if (empty($description)) {
            echo json_encode(['error' => 'Description is required']);
            return;
        }

        if (empty($assigned_to)) {
            echo json_encode(['error' => 'Assigned to is required']);
            return;
        }

        if (empty($due_date)) {
            echo json_encode(['error' => 'Due date is required']);
            return;
        }

        // Tạo mảng $data để cập nhật vào database
        $data = [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'assigned_to' => $assigned_to,
            'due_date' => $due_date
        ];

        // Thực hiện cập nhật task
        $this->taskModel->updateTask($data);
        echo json_encode(['success' => 'Task updated successfully']);
    }


    public function delete()
    {
        $id = $_POST['id'];
        $this->taskModel->deleteTask($id);
        echo json_encode(['success' => 'Task deleted successfully']);
    }

    public function storeSubTask()
    {
        // Lấy dữ liệu JSON từ request body
        $input = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra xem dữ liệu JSON có được giải mã đúng không
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        // Kiểm tra từng giá trị từ JSON input
        $taskId = isset($input['task_id']) ? $input['task_id'] : null;
        $title = isset($input['title']) ? $input['title'] : null;
        $description = isset($input['description']) ? $input['description'] : null;

        // Kiểm tra các trường bắt buộc
        if (empty($taskId)) {
            echo json_encode(['error' => 'Task ID is required']);
            return;
        }

        if (empty($title)) {
            echo json_encode(['error' => 'Title is required']);
            return;
        }

        if (empty($description)) {
            echo json_encode(['error' => 'Description is required']);
            return;
        }

        // Kiểm tra xem task_id có tồn tại trong bảng tasks không
        $taskExists = $this->taskModel->checkTaskExists($taskId);
        if (!$taskExists) {
            echo json_encode(['error' => 'Task ID does not exist']);
            return;
        }

        // Tạo mảng dữ liệu để lưu vào database
        $data = [
            'task_id' => $taskId,
            'title' => $title,
            'description' => $description
        ];

        // Lưu sub-task
        $this->taskModel->createSubTask($data);
        echo json_encode(['success' => 'Sub-task created successfully']);
    }


    public function updateSubTaskCompletion()
    {
        // Lấy dữ liệu JSON từ request body
        $input = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra xem dữ liệu JSON có được giải mã đúng không
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        // Kiểm tra từng giá trị từ JSON input
        $subTaskId = isset($input['id']) ? $input['id'] : null;
        $isCompleted = isset($input['is_completed']) ? $input['is_completed'] : null;
        $taskId = isset($input['task_id']) ? $input['task_id'] : null;

        // Kiểm tra các trường bắt buộc
        if (empty($subTaskId)) {
            echo json_encode(['error' => 'Sub-task ID is required']);
            return;
        }

        if (!isset($isCompleted)) {
            echo json_encode(['error' => 'Completion status is required']);
            return;
        }

        if (empty($taskId)) {
            echo json_encode(['error' => 'Task ID is required']);
            return;
        }

        // Cập nhật hoàn thành của sub-task
        $this->taskModel->updateSubTaskCompletion($subTaskId, $isCompleted);

        // Tính toán % hoàn thành của task lớn
        $subTasks = $this->taskModel->getSubTasks($taskId);
        $totalSubTasks = count($subTasks);
        $completedSubTasks = count(array_filter($subTasks, function($subTask) {
            return $subTask['is_completed'] == 1;
        }));

        // Cập nhật % hoàn thành của task lớn
        $completionPercentage = ($completedSubTasks / $totalSubTasks) * 100;
        $this->taskModel->updateTaskCompletion($taskId, $completionPercentage);

        echo json_encode(['success' => 'Sub-task updated and completion recalculated']);
    }

    // API để trả về danh sách người dùng
    public function getListUser()
    {
        $users = $this->userModel->getAllUsers();
        echo json_encode($users);
    }


}
