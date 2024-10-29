<?php

namespace App\Models;

use PDO;

class User
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Lấy tất cả người dùng từ bảng users
    public function getAllUsers()
    {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm một người dùng mới
    public function createUser($data)
    {

        // Kiểm tra xem email đã tồn tại chưa
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $data['email']]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Email already exists, return an error message or handle it as you prefer
            throw new \Exception("Email already exists.");
        }

        // If the email doesn't exist, proceed to insert the new user
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        return $stmt->execute($data);
    }

    // Cập nhật thông tin người dùng
    public function updateUser($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id");
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    // Xóa người dùng dựa trên ID
    public function deleteUser($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Lấy thông tin người dùng theo ID
    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


}
