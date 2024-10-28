<?php

namespace App\Controllers;

use App\Models\User;
use PDO;
use App\Database;

class UserController
{
    private $userModel;
    /**
     * @var PDO
     */
    private $db;

    public function __construct()
    {
        // Lấy kết nối database từ lớp Database
        $this->db = Database::getInstance();
        $this->userModel = new User($this->db);
    }

    // API để trả về danh sách người dùng
    public function index()
    {
        $users = $this->userModel->getAllUsers();
        echo json_encode($users);
    }


    // API để tạo người dùng mới
    public function store()
    {
        $input = $this->getJsonInput(); // Lấy dữ liệu từ JSON input

        // Kiểm tra các trường cần thiết
        $requiredFields = ['name', 'email', 'password', 'role'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                $this->jsonResponse(['error' => ucfirst($field) . ' is required'], 400);
                return;
            }
        }

        // Hash password trước khi lưu vào cơ sở dữ liệu
        $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);

        try {
            // Tạo người dùng
            $this->userModel->createUser($input);
            $this->jsonResponse(['success' => 'User created successfully'], 201);
        } catch (\Exception $e) {
            // Handle duplicate email or other exceptions
            $this->jsonResponse(['error' => $e->getMessage()], 400); // Bad request or custom status code
        }
    }


    // API để cập nhật thông tin người dùng
    public function update($id)
    {
        $input = $this->getJsonInput(); // Lấy dữ liệu từ JSON input

        // Kiểm tra các trường cần thiết
        $requiredFields = ['name', 'email', 'role'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                $this->jsonResponse(['error' => ucfirst($field) . ' is required'], 400);
                return;
            }
        }

        // Cập nhật thông tin người dùng
        $this->userModel->updateUser($id, $input);
        $this->jsonResponse(['success' => 'User updated successfully'], 200);
    }

    // API để xóa người dùng
    public function delete($id)
    {
        // Kiểm tra xem người dùng có tồn tại không
        $userExists = $this->userModel->getUserById($id);
        if (!$userExists) {
            $this->jsonResponse(['error' => 'User not found'], 404);
            return;
        }

        // Xóa người dùng
        $this->userModel->deleteUser($id);
        $this->jsonResponse(['success' => 'User deleted successfully'], 200);
    }

    // Hàm để lấy dữ liệu từ JSON input
    private function getJsonInput()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    // Hàm phản hồi JSON
    private function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    // API để trả về chi tiết một người dùng theo ID
    public function show($id)
    {
        $user = $this->userModel->getUserById($id);
        if ($user) {
            $this->jsonResponse($user, 200); // Thành công
        } else {
            $this->jsonResponse(['error' => 'User not found'], 404); // Không tìm thấy người dùng
        }
    }
}
