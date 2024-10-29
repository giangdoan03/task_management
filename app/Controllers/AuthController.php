<?php
namespace App\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use App\Database;

class AuthController
{
    private $db;
    private $secretKey = "your_secret_key"; // Thay bằng khóa bí mật của bạn

    public function __construct()
    {
        // Lấy kết nối database duy nhất từ lớp Database
        $this->db = Database::getInstance();
    }

    public function login()
    {
        // Lấy thông tin từ JSON request body
        $input = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra xem input có hợp lệ không
        if (is_null($input) || !isset($input['email']) || !isset($input['password'])) {
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $email = $input['email'];
        $password = $input['password'];

        // Kiểm tra xem người dùng có tồn tại trong database không
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Thiết lập JWT_ISSUER tùy theo môi trường
            $jwt_issuer = ($_SERVER['SERVER_NAME'] === 'localhost')
                ? 'http://localhost'
                : (getenv('JWT_ISSUER') ?: "https://api.develop.io.vn");

            // Đặt secretKey từ file .env nếu có, hoặc dùng giá trị mặc định
            $secretKey = getenv('SECRET_KEY') ?: 'your_static_secret_key_here';

            // Tạo JWT token
            $payload = [
                'iss' => $jwt_issuer,
                'iat' => time(),            // Thời gian tạo token
                'exp' => time() + 3600,     // Thời gian hết hạn (1 giờ)
                'sub' => $user['id'],       // ID người dùng
            ];

            // Tạo JWT
            $jwt = JWT::encode($payload, $secretKey, 'HS256');

            // Trả về token và thông tin người dùng
            echo json_encode([
                'token' => $jwt,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }



    public function register()
    {

        // Lấy thông tin từ JSON request body
        $input = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra xem các thông tin cần thiết có tồn tại không
        if (!isset($input['email']) || !isset($input['password']) || !isset($input['name'])) {
            echo json_encode(['error' => 'Name, email, and password are required']);
            return;
        }

        $email = $input['email'];
        $password = password_hash($input['password'], PASSWORD_BCRYPT);
        $name = $input['name'];

        // Kiểm tra email đã tồn tại chưa
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['error' => 'Email already exists']);
            return;
        }

        // Thêm người dùng mới vào database
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $stmt->execute(['name' => $name, 'email' => $email, 'password' => $password]);

        echo json_encode(['success' => 'User registered successfully']);
    }



    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return $decoded->sub;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function logout()
    {
        // Để thực hiện logout, chỉ cần xóa JWT ở phía client (trình duyệt hoặc ứng dụng)
        echo json_encode(['success' => 'Logged out']);
    }
}
