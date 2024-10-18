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
}
